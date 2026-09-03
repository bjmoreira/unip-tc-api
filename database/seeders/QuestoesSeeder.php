<?php

namespace Database\Seeders;

use Database\Seeders\Banco\Citologia;
use Database\Seeders\Banco\Ecologia;
use Database\Seeders\Banco\Funcoes;
use Database\Seeders\Banco\Genetica;
use Database\Seeders\Banco\Geometria;
use Database\Seeders\Banco\Gramatica;
use Database\Seeders\Banco\Interpretacao;
use Database\Seeders\Banco\Ortografia;
use Database\Seeders\Banco\Probabilidade;
use Database\Seeders\Banco\Verificador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Carga do banco de questoes.
 *
 * Cada topico e produzido por um gerador proprio, descrito em Banco\Gerador. O
 * seeder nao conhece o conteudo: pede as questoes, confere o que recebeu e
 * grava.
 *
 * A gravacao e feita em lote. Com quase duas mil questoes e cerca de sete mil
 * alternativas, inserir linha a linha pelo Eloquent levaria minutos e tornaria
 * penoso o ciclo de recriar o banco durante o desenvolvimento.
 */
class QuestoesSeeder extends Seeder
{
    /** Quantas questoes pedir a cada gerador. */
    private const POR_TOPICO = 200;

    /** A dificuldade usa a mesma escala Elo da proficiencia do estudante. */
    private const DIFICULDADE = [
        'facil'   => 1050,   // estudante de proficiencia media acerta com folga
        'medio'   => 1250,
        'dificil' => 1450,
    ];

    private const ESTRUTURA = [
        'portugues' => [
            'nome'    => 'Português',
            'topicos' => [
                ['Interpretação de texto', Interpretacao::class],
                ['Gramática', Gramatica::class],
                ['Ortografia', Ortografia::class],
            ],
        ],
        'matematica' => [
            'nome'    => 'Matemática',
            'topicos' => [
                ['Funções', Funcoes::class],
                ['Geometria', Geometria::class],
                ['Probabilidade', Probabilidade::class],
            ],
        ],
        'biologia' => [
            'nome'    => 'Biologia',
            'topicos' => [
                ['Genética', Genetica::class],
                ['Ecologia', Ecologia::class],
                ['Citologia', Citologia::class],
            ],
        ],
    ];

    public function run(): void
    {
        $agora = now();
        $totalQuestoes = 0;
        $totalAlternativas = 0;

        foreach (self::ESTRUTURA as $discSlug => $disciplina) {
            $disciplinaId = DB::table('disciplinas')->insertGetId([
                'nome'       => $disciplina['nome'],
                'slug'       => $discSlug,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            foreach ($disciplina['topicos'] as [$nomeTopico, $gerador]) {
                $topicoId = DB::table('topicos')->insertGetId([
                    'disciplina_id' => $disciplinaId,
                    'nome'          => $nomeTopico,
                    'slug'          => $gerador::topico(),
                    'created_at'    => $agora,
                    'updated_at'    => $agora,
                ]);

                $itens = $gerador::gerar(self::POR_TOPICO);

                $problemas = Verificador::conferir($itens, $gerador::topico());
                if ($problemas !== []) {
                    throw new RuntimeException(
                        "Banco de questões inválido em {$gerador::topico()}:\n  "
                        . implode("\n  ", array_slice($problemas, 0, 10))
                    );
                }

                [$questoes, $alternativas] = $this->gravar($itens, $topicoId, $agora);
                $totalQuestoes += $questoes;
                $totalAlternativas += $alternativas;

                $this->command?->info(sprintf('  %-24s %4d questões', $nomeTopico, $questoes));
            }
        }

        $this->command?->info(sprintf(
            'Banco carregado: %d questões e %d alternativas.',
            $totalQuestoes,
            $totalAlternativas
        ));
    }

    /**
     * Grava as questoes de um topico e suas alternativas.
     *
     * @param array<\Database\Seeders\Banco\Item> $itens
     * @return array{0:int, 1:int}
     */
    private function gravar(array $itens, int $topicoId, mixed $agora): array
    {
        $linhas = [];
        foreach ($itens as $item) {
            $linhas[] = [
                'topico_id'   => $topicoId,
                'enunciado'   => $item->enunciado,
                'nivel'       => $item->nivel,
                'dificuldade' => self::DIFICULDADE[$item->nivel],
                'created_at'  => $agora,
                'updated_at'  => $agora,
            ];
        }

        foreach (array_chunk($linhas, 250) as $lote) {
            DB::table('questoes')->insert($lote);
        }

        // Os ids saem na mesma ordem em que as linhas foram inseridas, o que
        // permite casar cada questao gravada com o item que a originou.
        $ids = DB::table('questoes')
            ->where('topico_id', $topicoId)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (count($ids) !== count($itens)) {
            throw new RuntimeException('Divergência entre questões geradas e gravadas.');
        }

        $alternativas = [];
        foreach ($itens as $posicao => $item) {
            [$opcoes, $correta] = $item->alternativas($posicao);

            foreach ($opcoes as $i => $texto) {
                $alternativas[] = [
                    'questao_id' => $ids[$posicao],
                    'letra'      => chr(65 + $i),
                    'texto'      => $texto,
                    'correta'    => $i === $correta,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];
            }
        }

        foreach (array_chunk($alternativas, 500) as $lote) {
            DB::table('alternativas')->insert($lote);
        }

        return [count($itens), count($alternativas)];
    }
}
