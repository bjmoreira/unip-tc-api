<?php

namespace App\Console\Commands;

use App\Models\Questao;
use App\Models\Topico;
use App\Recomendacao\Elo;
use App\Simulacao\Agente;
use App\Simulacao\Aleatorio;
use App\Simulacao\Experimento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Ambiente de simulacao em lote.
 *
 * Roda o experimento inteiro sem passar por HTTP e sem gravar provas no banco:
 * o banco e consultado uma unica vez, no inicio, para carregar a composicao real
 * do acervo de questoes. O que se avalia depois disso e o codigo de decisao da
 * aplicacao, executado diretamente.
 *
 * Dois bracos, a mesma populacao de agentes e a mesma semente. A unica diferenca
 * entre eles e qual metodo do Recomendador monta as provas a partir da segunda.
 */
class Simular extends Command
{
    protected $signature = 'simular
        {--agentes=1000 : Quantos estudantes virtuais por braco}
        {--provas=18 : Quantas provas cada agente faz}
        {--tamanho=20 : Quantas questoes por prova}
        {--semente=20260928 : Semente do experimento, para reproducao}
        {--ganho=6.0 : Ganho de proficiencia real na primeira pratica de um topico}
        {--piso= : Piso de cobertura por topico; por omissao o valor da aplicacao}
        {--retencao=0 : Constante de retencao em dias; 0 desliga o esquecimento}
        {--estimador=padrao : Estimador de proficiencia: padrao, k_adaptativo ou tendencia}
        {--teto= : Teto do fator de espacamento; 1 desliga o espacamento}
        {--intervalo-min=1 : Menor intervalo em dias entre provas consecutivas}
        {--intervalo-max=4 : Maior intervalo em dias entre provas consecutivas}
        {--saida= : Diretorio de saida; por omissao storage/app/simulacao}';

    protected $description = 'Executa o experimento comparativo entre a distribuicao adaptativa e a linha de base uniforme';

    public function handle(): int
    {
        $quantidade = max(1, (int) $this->option('agentes'));
        $provas     = max(1, (int) $this->option('provas'));
        $tamanho    = max(1, (int) $this->option('tamanho'));
        $semente    = (int) $this->option('semente');
        $ganho      = max(0.0, (float) $this->option('ganho'));
        $piso       = $this->option('piso') === null ? null : max(0, (int) $this->option('piso'));
        $retencao   = max(0.0, (float) $this->option('retencao'));
        $estimador  = (string) $this->option("estimador");
        $iMin       = max(0, (int) $this->option("intervalo-min"));
        $iMax       = max($iMin, (int) $this->option("intervalo-max"));
        $teto       = $this->option("teto") === null ? null : max(1.0, (float) $this->option("teto"));

        $modos = [Elo::MODO_PADRAO, Elo::MODO_K_ADAPTATIVO, Elo::MODO_TENDENCIA];
        if (! in_array($estimador, $modos, true)) {
            $this->error('Estimador inválido. Use: ' . implode(', ', $modos));

            return self::FAILURE;
        }

        $banco = $this->carregarBanco();

        if ($banco === []) {
            $this->error('Nenhuma questão encontrada. Rode as migrações e o seeder antes da simulação.');

            return self::FAILURE;
        }

        $topicos = array_keys($banco);

        $this->info('Experimento: Questões Adaptativas');
        $this->line(sprintf(
            '  %d agentes por braço, %d provas de %d questões, %d tópicos, semente %d',
            $quantidade,
            $provas,
            $tamanho,
            count($topicos),
            $semente
        ));
        $this->newLine();

        // Uma unica sequencia de sorteio constroi a populacao. Os dois bracos
        // recebem os mesmos objetos, reiniciados, e nao amostras diferentes.
        $construcao = new Aleatorio($semente);
        $agentes = [];
        for ($i = 0; $i < $quantidade; $i++) {
            $agentes[] = new Agente($i, $topicos, $provas, $construcao, $ganho, $retencao, $estimador, $iMin, $iMax);
        }

        $this->line(sprintf(
            '  Perfis: %.1f tópicos fracos por agente em média, limiar %.0f, patamar %.0f',
            array_sum(array_map(fn (Agente $a) => count($a->fracosIniciais), $agentes)) / $quantidade,
            Agente::LIMIAR_FRACO,
            Agente::PATAMAR
        ));
        $this->line(sprintf('  Premissa de aprendizagem: ganho de %.1f pontos na primeira prática do tópico', $ganho));
        $this->newLine();

        $experimento = new Experimento($banco, $tamanho, $provas, $semente, $piso, $teto);

        $this->line('  Rodando braço adaptativo...');
        $adaptativo = $experimento->rodar($agentes, true);

        $this->line('  Rodando linha de base uniforme...');
        $base = $experimento->rodar($agentes, false);

        $this->newLine();

        $diretorio = $this->option('saida') ?: storage_path('app/simulacao');
        $arquivos = $this->gravar($diretorio, $adaptativo, $base);

        $this->resumo($adaptativo, $base);

        $this->newLine();
        $this->info('Dados brutos:');
        foreach ($arquivos as $arquivo) {
            $this->line('  ' . $arquivo);
        }

        return self::SUCCESS;
    }

    /**
     * Composicao real do acervo: quantas questoes de cada nivel cada topico tem
     * e qual a dificuldade desse nivel.
     *
     * A simulacao usa o acervo que a aplicacao usa, e nao um acervo idealizado.
     * Se um topico nao tiver questoes faceis suficientes, o experimento sente o
     * mesmo limite que o estudante sentiria.
     *
     * @return array<int, array<string, array{dificuldade: float, total: int}>>
     */
    private function carregarBanco(): array
    {
        $linhas = Questao::query()
            ->select('topico_id', 'nivel')
            ->selectRaw('count(*) as total')
            ->selectRaw('avg(dificuldade) as dificuldade')
            ->groupBy('topico_id', 'nivel')
            ->get();

        $banco = [];

        foreach (Topico::orderBy('id')->pluck('id') as $topicoId) {
            $banco[$topicoId] = [];
        }

        foreach ($linhas as $linha) {
            $banco[$linha->topico_id][$linha->nivel] = [
                'dificuldade' => (float) $linha->dificuldade,
                'total'       => (int) $linha->total,
            ];
        }

        // Um topico sem questao nenhuma nao pode entrar na distribuicao: o
        // recomendador reservaria vagas que ninguem preencheria.
        return array_filter($banco, fn (array $niveis) => $niveis !== []);
    }

    /**
     * Grava os dados brutos que alimentam os graficos do relatorio.
     *
     * Sao dois arquivos porque servem a duas leituras diferentes: a evolucao
     * prova a prova, que e uma serie temporal, e o resultado final de cada
     * agente, que e uma distribuicao.
     *
     * @return array<int, string>
     */
    private function gravar(string $diretorio, array $adaptativo, array $base): array
    {
        File::ensureDirectoryExists($diretorio);

        $porProva = $diretorio . DIRECTORY_SEPARATOR . 'experimento.csv';
        $handle = fopen($porProva, 'w');
        fputcsv($handle, [
            'braco',
            'prova',
            'aproveitamento_medio',
            'prof_real_fracos',
            'prof_real_geral',
            'erro_estimativa',
            'vies_estimativa',
            'pct_fracos_no_patamar',
            'questoes_nos_fracos',
        ]);

        foreach (['adaptativo' => $adaptativo, 'linha_de_base' => $base] as $nome => $resultado) {
            foreach ($resultado['provas'] as $linha) {
                fputcsv($handle, [
                    $nome,
                    $linha['prova'],
                    round($linha['aproveitamento'], 4),
                    round($linha['real_fracos'], 4),
                    round($linha['real_geral'], 4),
                    round($linha['erro'], 4),
                    round($linha['vies'], 4),
                    round($linha['pct_fracos_patamar'], 4),
                    round($linha['questoes_fracos'], 4),
                ]);
            }
        }
        fclose($handle);

        $porAgente = $diretorio . DIRECTORY_SEPARATOR . 'agentes.csv';
        $handle = fopen($porAgente, 'w');
        fputcsv($handle, [
            'braco',
            'agente',
            'topicos_fracos',
            'prof_real_fracos_inicio',
            'prof_real_fracos_fim',
            'prof_real_geral_fim',
            'erro_estimativa_fim',
            'vies_estimativa_fim',
            'prova_do_patamar',
        ]);

        foreach (['adaptativo' => $adaptativo, 'linha_de_base' => $base] as $nome => $resultado) {
            foreach ($resultado['agentes'] as $linha) {
                fputcsv($handle, [
                    $nome,
                    $linha['agente'],
                    $linha['topicos_fracos'],
                    round($linha['real_fracos_inicio'], 4),
                    round($linha['real_fracos_fim'], 4),
                    round($linha['real_geral_fim'], 4),
                    round($linha['erro_fim'], 4),
                    round($linha['vies_fim'], 4),
                    $linha['prova_do_patamar'] ?? '',
                ]);
            }
        }
        fclose($handle);

        return [$porProva, $porAgente];
    }

    /** Resumo comparativo no terminal. */
    private function resumo(array $adaptativo, array $base): void
    {
        $fimA = end($adaptativo['provas']);
        $fimB = end($base['provas']);

        $mediaA = $this->media(array_column($adaptativo['provas'], 'aproveitamento'));
        $mediaB = $this->media(array_column($base['provas'], 'aproveitamento'));

        $erroMedioA = $this->media(array_column($adaptativo['provas'], 'erro'));
        $erroMedioB = $this->media(array_column($base['provas'], 'erro'));

        $inicio = $this->media(array_column($adaptativo['agentes'], 'real_fracos_inicio'));

        $patamar = $this->comparacaoDoPatamar($adaptativo['agentes'], $base['agentes']);

        $questoesA = $this->media(array_column($adaptativo['provas'], 'questoes_fracos'));
        $questoesB = $this->media(array_column($base['provas'], 'questoes_fracos'));

        $linhas = [
            $this->linha('Aproveitamento médio na última prova (%)', $fimA['aproveitamento'], $fimB['aproveitamento']),
            $this->linha('Aproveitamento médio em todas as provas (%)', $mediaA, $mediaB),
            $this->linha('Proficiência real nos tópicos fracos (final)', $fimA['real_fracos'], $fimB['real_fracos']),
            $this->linha('Ganho nos tópicos fracos (pontos)', $fimA['real_fracos'] - $inicio, $fimB['real_fracos'] - $inicio),
            $this->linha('Proficiência real média em todos os tópicos', $fimA['real_geral'], $fimB['real_geral']),
            $this->linha('Erro de estimativa na última prova', $fimA['erro'], $fimB['erro']),
            $this->linha('Erro de estimativa médio', $erroMedioA, $erroMedioB),
            $this->linha('Viés da estimativa na última prova', $fimA['vies'], $fimB['vies']),
            $this->linha('Tópicos fracos acima do patamar (%)', $fimA['pct_fracos_patamar'], $fimB['pct_fracos_patamar']),
            $this->linha('Agentes que cruzaram o patamar (%)', $patamar['pct_a'], $patamar['pct_b']),
            $this->linha(
                sprintf('Provas até o patamar (n = %d pareados)', $patamar['pares']),
                $patamar['media_a'],
                $patamar['media_b']
            ),
            $this->linha('Questões por prova nos tópicos fracos', $questoesA, $questoesB),
        ];

        $this->table(['Métrica', 'Adaptativo', 'Linha de base', 'Diferença'], $linhas);

        $this->newLine();
        $this->info('Teste emparelhado sobre a proficiência real nos tópicos fracos:');

        $pares = [];
        foreach ($adaptativo['agentes'] as $i => $linha) {
            $pares[] = $linha['real_fracos_fim'] - $base['agentes'][$i]['real_fracos_fim'];
        }

        $diferenca = $this->media($pares);
        $desvio = $this->desvio($pares, $diferenca);
        $n = count($pares);
        $erroPadrao = $desvio / sqrt($n);

        $this->line(sprintf(
            '  diferença média %+.2f pontos, desvio %.2f, erro padrão %.3f, t = %+.2f (n = %d)',
            $diferenca,
            $desvio,
            $erroPadrao,
            $erroPadrao > 0 ? $diferenca / $erroPadrao : 0.0,
            $n
        ));

        $vitorias = count(array_filter($pares, fn (float $d) => $d > 0));
        $this->line(sprintf(
            '  o braço adaptativo terminou à frente em %d dos %d agentes (%.1f%%)',
            $vitorias,
            $n,
            100.0 * $vitorias / $n
        ));
    }

    /** @return array<int, string> */
    private function linha(string $rotulo, float $a, float $b): array
    {
        $diferenca = $a - $b;

        return [
            $rotulo,
            number_format($a, 2, ',', '.'),
            number_format($b, 2, ',', '.'),
            // O sinal vai explicito no positivo tambem: numa tabela de
            // comparacao, saber a direcao da diferenca importa mais que o valor.
            ($diferenca > 0 ? '+' : '') . number_format($diferenca, 2, ',', '.'),
        ];
    }

    /**
     * Quantas provas foram necessarias para os topicos fracos cruzarem o patamar.
     *
     * A media so considera os agentes que cruzaram nos DOIS bracos. Comparar a
     * media de cada braco sobre o seu proprio conjunto de agentes seria
     * enganoso: se um braco faz menos agentes cruzarem, os que cruzam nele sao
     * justamente os de perfil mais favoravel, e a media dele apareceria
     * artificialmente boa. O percentual de agentes que cruzaram e reportado
     * separadamente, e e ele que mostra quantos ficaram para tras.
     *
     * @return array{media_a: float, media_b: float, pares: int, pct_a: float, pct_b: float}
     */
    private function comparacaoDoPatamar(array $a, array $b): array
    {
        $paresA = [];
        $paresB = [];
        $cruzouA = 0;
        $cruzouB = 0;

        foreach ($a as $i => $linha) {
            $va = $linha['prova_do_patamar'];
            $vb = $b[$i]['prova_do_patamar'];

            $cruzouA += $va === null ? 0 : 1;
            $cruzouB += $vb === null ? 0 : 1;

            if ($va !== null && $vb !== null) {
                $paresA[] = $va;
                $paresB[] = $vb;
            }
        }

        $total = max(1, count($a));

        return [
            'media_a' => $this->media($paresA),
            'media_b' => $this->media($paresB),
            'pares'   => count($paresA),
            'pct_a'   => 100.0 * $cruzouA / $total,
            'pct_b'   => 100.0 * $cruzouB / $total,
        ];
    }

    /** @param array<int, float|int> $valores */
    private function media(array $valores): float
    {
        return $valores === [] ? 0.0 : array_sum($valores) / count($valores);
    }

    /** @param array<int, float> $valores */
    private function desvio(array $valores, float $media): float
    {
        $n = count($valores);

        if ($n < 2) {
            return 0.0;
        }

        $soma = 0.0;
        foreach ($valores as $v) {
            $soma += ($v - $media) ** 2;
        }

        return sqrt($soma / ($n - 1));
    }
}
