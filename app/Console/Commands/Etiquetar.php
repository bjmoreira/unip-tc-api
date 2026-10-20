<?php

namespace App\Console\Commands;

use App\Models\Questao;
use App\Models\Topico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Etiquetagem de questoes apoiada por modelo de linguagem.
 *
 * O recomendador exige que cada questao tenha topico e nivel de dificuldade. No
 * acervo proprio isso e conhecido por construcao, porque os geradores produzem
 * a questao ja sabendo o que estao produzindo. O problema aparece quando se
 * quer incorporar questoes de origem externa, que chegam sem etiqueta: a
 * etiquetagem manual nao escala e, pior, nao se mantem consistente.
 *
 * Este comando NAO etiqueta o acervo, que ja esta etiquetado. Ele MEDE se um
 * modelo de linguagem seria capaz de etiquetar: usa as etiquetas existentes
 * como gabarito, pede ao modelo que classifique a questao sem ve-las e compara.
 * O acervo proprio funciona, assim, como conjunto de validacao para decidir se
 * a etiquetagem automatica tem qualidade suficiente para ser adotada.
 *
 * A medida importa mais no topico do que no nivel: errar o topico manda a
 * questao para a area errada do estudante, enquanto errar o nivel apenas
 * desloca a dificuldade dentro da area certa.
 */
class Etiquetar extends Command
{
    protected $signature = 'etiquetar
        {--amostra=90 : Quantas questoes sortear do acervo}
        {--modelo=llama3.1:8b : Modelo servido pelo Ollama}
        {--url=http://watermarks-front-ollama-1:11434 : Endereco do Ollama}
        {--semente=20261019 : Semente do sorteio da amostra}
        {--saida= : Diretorio de saida; por omissao storage/app/etiquetagem}';

    protected $description = 'Mede a concordancia entre a etiquetagem de um modelo de linguagem e o gabarito do acervo';

    private const NIVEIS = ['facil', 'medio', 'dificil'];

    public function handle(): int
    {
        $amostra = max(1, (int) $this->option('amostra'));
        $modelo  = (string) $this->option('modelo');
        $url     = rtrim((string) $this->option('url'), '/');

        $topicos = Topico::pluck('nome', 'id');

        if ($topicos->isEmpty()) {
            $this->error('Acervo vazio. Rode as migrações e o seeder antes.');

            return self::FAILURE;
        }

        // Amostra estratificada: mesma quantidade por topico, para que a
        // acuracia media nao seja dominada pelos topicos mais numerosos.
        $porTopico = max(1, intdiv($amostra, $topicos->count()));
        $questoes = collect();

        foreach ($topicos->keys() as $topicoId) {
            $questoes = $questoes->concat(
                Questao::with('alternativas')
                    ->where('topico_id', $topicoId)
                    ->inRandomOrder((int) $this->option('semente'))
                    ->limit($porTopico)
                    ->get()
            );
        }

        $this->info(sprintf(
            'Etiquetagem: %d questões (%d por tópico), modelo %s',
            $questoes->count(),
            $porTopico,
            $modelo
        ));

        $linhas = [];
        $acertosTopico = 0;
        $acertosNivel  = 0;
        $falhas = 0;
        $barra = $this->output->createProgressBar($questoes->count());
        $barra->start();

        foreach ($questoes as $questao) {
            $resposta = $this->classificar($url, $modelo, $questao, $topicos->values()->all());
            $barra->advance();

            if ($resposta === null) {
                $falhas++;
                continue;
            }

            $topicoReal = $topicos[$questao->topico_id];
            $okTopico = $this->normalizar($resposta['topico']) === $this->normalizar($topicoReal);
            $okNivel  = $this->normalizar($resposta['nivel']) === $this->normalizar($questao->nivel);

            $acertosTopico += $okTopico ? 1 : 0;
            $acertosNivel  += $okNivel ? 1 : 0;

            $linhas[] = [
                $questao->id,
                $topicoReal,
                $resposta['topico'],
                $okTopico ? 1 : 0,
                $questao->nivel,
                $resposta['nivel'],
                $okNivel ? 1 : 0,
            ];
        }

        $barra->finish();
        $this->newLine(2);

        $validas = count($linhas);

        if ($validas === 0) {
            $this->error('Nenhuma resposta aproveitável do modelo.');

            return self::FAILURE;
        }

        $this->table(
            ['Medida', 'Valor'],
            [
                ['Questões avaliadas', $validas],
                ['Respostas descartadas', $falhas],
                ['Concordância no tópico', sprintf('%.1f%%', 100 * $acertosTopico / $validas)],
                ['Concordância no nível', sprintf('%.1f%%', 100 * $acertosNivel / $validas)],
                ['Acerto ao acaso no tópico', sprintf('%.1f%%', 100 / $topicos->count())],
                ['Acerto ao acaso no nível', sprintf('%.1f%%', 100 / count(self::NIVEIS))],
            ]
        );

        $this->matrizPorTopico($linhas, $topicos->values()->all());

        $diretorio = $this->option('saida') ?: storage_path('app/etiquetagem');
        File::ensureDirectoryExists($diretorio);
        $caminho = $diretorio . '/etiquetagem.csv';
        $arquivo = fopen($caminho, 'w');
        fputcsv($arquivo, ['questao_id', 'topico_real', 'topico_modelo', 'topico_ok', 'nivel_real', 'nivel_modelo', 'nivel_ok']);
        foreach ($linhas as $linha) {
            fputcsv($arquivo, $linha);
        }
        fclose($arquivo);

        $this->line('Dados brutos: ' . $caminho);

        return self::SUCCESS;
    }

    /**
     * Pede ao modelo a classificacao de uma questao.
     *
     * O enunciado e as alternativas vao sem qualquer pista do gabarito, e a
     * lista de topicos e fechada: a tarefa e de classificacao, nao de invencao
     * de categoria. Temperatura zero porque o que se mede e a capacidade do
     * modelo, nao a sua variedade.
     *
     * @param array<int, string> $topicos
     * @return array{topico: string, nivel: string}|null
     */
    private function classificar(string $url, string $modelo, Questao $questao, array $topicos): ?array
    {
        $alternativas = $questao->alternativas->pluck('texto')->implode("\n- ");

        $prompt = "Classifique a questão de múltipla escolha abaixo.\n\n"
            . "Tópicos possíveis (escolha exatamente um): " . implode('; ', $topicos) . "\n"
            . "Níveis possíveis (escolha exatamente um): facil; medio; dificil\n\n"
            . "Questão:\n{$questao->enunciado}\n\nAlternativas:\n- {$alternativas}\n\n"
            . "Responda somente com JSON no formato {\"topico\": \"...\", \"nivel\": \"...\"}.";

        try {
            $resposta = Http::timeout(120)->post($url . '/api/generate', [
                'model'  => $modelo,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
                'options' => ['temperature' => 0.0],
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $resposta->successful()) {
            return null;
        }

        $conteudo = json_decode((string) ($resposta->json('response') ?? ''), true);

        if (! is_array($conteudo) || ! isset($conteudo['topico'], $conteudo['nivel'])) {
            return null;
        }

        return ['topico' => (string) $conteudo['topico'], 'nivel' => (string) $conteudo['nivel']];
    }

    /** Compara ignorando acento, caixa e espaco em excesso. */
    private function normalizar(string $texto): string
    {
        $semAcento = iconv('UTF-8', 'ASCII//TRANSLIT', $texto) ?: $texto;

        return strtolower(trim(preg_replace('/\s+/', ' ', $semAcento)));
    }

    /**
     * Acuracia por topico: mostra onde a etiquetagem automatica falha, que e a
     * informacao util para decidir se ela entra ou nao no fluxo.
     *
     * @param array<int, array<int, mixed>> $linhas
     * @param array<int, string> $topicos
     */
    private function matrizPorTopico(array $linhas, array $topicos): void
    {
        $tabela = [];

        foreach ($topicos as $nome) {
            $doTopico = array_filter($linhas, fn ($l) => $l[1] === $nome);
            $total = count($doTopico);

            if ($total === 0) {
                continue;
            }

            $certos = count(array_filter($doTopico, fn ($l) => $l[3] === 1));
            $confusoes = [];

            foreach ($doTopico as $l) {
                if ($l[3] === 0) {
                    $confusoes[$l[2]] = ($confusoes[$l[2]] ?? 0) + 1;
                }
            }

            arsort($confusoes);
            $principal = array_key_first($confusoes);

            $tabela[] = [
                $nome,
                $total,
                sprintf('%.0f%%', 100 * $certos / $total),
                $principal === null ? '-' : $principal . ' (' . $confusoes[$principal] . ')',
            ];
        }

        $this->table(['Tópico', 'n', 'Acerto', 'Confusão mais frequente'], $tabela);
    }
}
