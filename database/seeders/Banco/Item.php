<?php

namespace Database\Seeders\Banco;

/**
 * Uma questao do banco, antes de ir para o banco de dados.
 *
 * Os geradores devolvem objetos desta classe. O texto da alternativa correta e
 * declarado separadamente dos distratores, e nao por indice, para que um erro
 * de contagem no gerador nao produza gabarito trocado. O embaralhamento e a
 * definicao da letra acontecem depois, em um unico lugar.
 */
final class Item
{
    /**
     * @param string        $enunciado
     * @param string        $nivel        facil | medio | dificil
     * @param string        $correta      texto da alternativa correta
     * @param array<string> $distratores  alternativas incorretas
     */
    public function __construct(
        public readonly string $enunciado,
        public readonly string $nivel,
        public readonly string $correta,
        public readonly array $distratores,
    ) {
    }

    /**
     * Alternativas na ordem final, com a posicao da correta.
     *
     * Distratores repetidos ou iguais a resposta certa sao descartados. Se
     * sobrarem menos de tres, a questao e considerada malformada: e melhor
     * falhar aqui, na geracao, do que publicar uma questao com duas
     * alternativas identicas.
     *
     * @return array{0: array<string>, 1: int}
     */
    public function alternativas(int $semente): array
    {
        $distratores = [];
        foreach ($this->distratores as $d) {
            $d = trim($d);
            if ($d === '' || $d === $this->correta || in_array($d, $distratores, true)) {
                continue;
            }
            $distratores[] = $d;
        }

        if (count($distratores) < 3) {
            throw new \RuntimeException(
                "Questao sem distratores suficientes: {$this->enunciado}"
            );
        }

        $opcoes = array_merge([$this->correta], array_slice($distratores, 0, 3));

        // Embaralhamento deterministico: o mesmo banco e reproduzido a cada
        // execucao do seeder, o que e condicao para repetir o experimento.
        mt_srand($semente);
        $ordem = range(0, 3);
        for ($i = 3; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$ordem[$i], $ordem[$j]] = [$ordem[$j], $ordem[$i]];
        }

        $final = [];
        $indiceCorreta = 0;
        foreach ($ordem as $posicao => $original) {
            $final[$posicao] = $opcoes[$original];
            if ($original === 0) {
                $indiceCorreta = $posicao;
            }
        }

        return [$final, $indiceCorreta];
    }
}
