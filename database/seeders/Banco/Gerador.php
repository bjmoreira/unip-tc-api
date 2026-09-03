<?php

namespace Database\Seeders\Banco;

/**
 * Base dos geradores de questoes.
 *
 * Ha dois tipos de gerador no banco, e a distincao e relevante para a leitura
 * dos resultados do experimento:
 *
 * - Geradores computados (Matematica e Genetica): o enunciado e parametrizado e
 *   a resposta correta e calculada a partir dos parametros. Nao existe risco de
 *   gabarito errado, e a quantidade de questoes e limitada apenas pelo espaco
 *   de parametros.
 *
 * - Geradores curados (Portugues, Ecologia e Citologia): partem de uma base de
 *   fatos ou de textos escritos e revisados um a um. A combinatoria apenas
 *   varia a forma de perguntar. A quantidade e limitada pelo tamanho da base.
 *
 * Os distratores, nos dois casos, correspondem a erros plausiveis, e nao a
 * alternativas aleatorias: em Matematica sao os resultados de trocar um sinal
 * ou esquecer uma etapa; nos curados, sao fatos verdadeiros aplicados ao objeto
 * errado.
 */
abstract class Gerador
{
    /**
     * Gera ate $quantidade questoes distintas do topico.
     *
     * @return array<Item>
     */
    abstract public static function gerar(int $quantidade): array;

    /** Slug do topico a que as questoes pertencem. */
    abstract public static function topico(): string;

    /**
     * Toma questoes das familias em rodizio, ate atingir a quantidade pedida.
     *
     * Percorrer uma familia de cada vez e cortar no fim deixaria de fora as
     * ultimas, e com elas um nivel inteiro de dificuldade. O rodizio garante
     * que o corte atinja todas as familias na mesma proporcao.
     *
     * @param array<callable(): iterable<Item>> $familias
     * @return array<Item>
     */
    protected static function intercalar(array $familias, int $quantidade): array
    {
        $filas = [];
        foreach ($familias as $familia) {
            $filas[] = is_array($familia()) ? $familia() : iterator_to_array($familia(), false);
        }

        $itens = [];
        $vistos = [];
        $posicao = 0;

        while (count($itens) < $quantidade) {
            $avancou = false;

            foreach ($filas as $fila) {
                if (! isset($fila[$posicao])) {
                    continue;
                }

                $avancou = true;
                $item = $fila[$posicao];

                // Duas questoes podem compartilhar o enunciado desde que a
                // resposta seja outra: e o caso das questoes de grafia, em que
                // o comando e sempre o mesmo e o que muda sao as alternativas.
                $chave = $item->enunciado . '|' . $item->correta;

                if (isset($vistos[$chave])) {
                    continue;
                }

                $vistos[$chave] = true;
                $itens[] = $item;

                if (count($itens) === $quantidade) {
                    return $itens;
                }
            }

            if (! $avancou) {
                break;
            }

            $posicao++;
        }

        return $itens;
    }

    /** Numero no formato brasileiro, sem casas decimais desnecessarias. */
    protected static function num(int|float $v): string
    {
        if (is_int($v) || abs($v - round($v)) < 1e-9) {
            return (string) (int) round($v);
        }

        return rtrim(rtrim(number_format($v, 2, ',', ''), '0'), ',');
    }

    /** Fracao ja simplificada, no formato "a/b". */
    protected static function fracao(int $numerador, int $denominador): string
    {
        $d = self::mdc(abs($numerador), abs($denominador));
        if ($d === 0) {
            return '0';
        }

        $numerador = intdiv($numerador, $d);
        $denominador = intdiv($denominador, $d);

        return $denominador === 1 ? (string) $numerador : "$numerador/$denominador";
    }

    protected static function mdc(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a;
    }

    /**
     * Monta a lista de distratores a partir dos erros plausiveis previstos.
     *
     * Descarta os que coincidem com a resposta certa ou entre si, e completa
     * com a reserva quando os erros previstos nao rendem tres alternativas.
     *
     * @param array<string> $erros   resultados de enganos tipicos
     * @param array<string> $reserva usada apenas para completar
     * @return array<string>
     */
    protected static function distratores(string $correta, array $erros, array $reserva = []): array
    {
        $saida = [];

        foreach (array_merge($erros, $reserva) as $texto) {
            $texto = trim((string) $texto);
            if ($texto === '' || $texto === $correta || in_array($texto, $saida, true)) {
                continue;
            }
            $saida[] = $texto;
            if (count($saida) === 3) {
                break;
            }
        }

        return $saida;
    }
}
