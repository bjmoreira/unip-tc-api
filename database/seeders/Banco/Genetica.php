<?php

namespace Database\Seeders\Banco;

/**
 * Genetica: gerador computado.
 *
 * A resposta sai do quadro de Punnett montado em codigo a partir dos genotipos
 * do enunciado, e nao de uma tabela escrita a mao. Todos os enunciados declaram
 * dominancia completa e segregacao independente, para que o quadro seja de fato
 * o modelo correto do cruzamento descrito.
 *
 * Os distratores sao as proporcoes vizinhas mais confundidas: a genotipica no
 * lugar da fenotipica, a do cruzamento-teste no lugar do di-hibrido, e o
 * complementar da proporcao pedida.
 */
final class Genetica extends Gerador
{
    /** [letra, caracteristica, fenotipo dominante, fenotipo recessivo, organismo] */
    private const CARACTERES = [
        ['V', 'a forma da semente', 'lisa', 'rugosa', 'ervilhas'],
        ['A', 'a cor da semente', 'amarela', 'verde', 'ervilhas'],
        ['P', 'a cor da pelagem', 'preta', 'branca', 'coelhos'],
        ['C', 'o comprimento do caule', 'alto', 'baixo', 'plantas'],
        ['R', 'a cor da flor', 'vermelha', 'branca', 'plantas'],
        ['L', 'o tipo de pelo', 'liso', 'crespo', 'cobaias'],
        ['T', 'a textura da vagem', 'lisa', 'ondulada', 'ervilhas'],
        ['N', 'a cor da plumagem', 'escura', 'clara', 'aves'],
    ];

    public static function topico(): string
    {
        return 'genetica';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::monoibridoFenotipo(),
            fn () => self::monoibridoGenotipo(),
            fn () => self::cruzamentoTeste(),
            fn () => self::sanguineo(),
            fn () => self::diibrido(),
            fn () => self::probabilidadeDupla(),
        ], $quantidade);
    }

    /** Gametas de um genotipo de um gene. */
    private static function gametas(string $genotipo): array
    {
        return [mb_substr($genotipo, 0, 1), mb_substr($genotipo, 1, 1)];
    }

    /** Quadro de Punnett de um gene: genotipo => quantidade em 4 casas. */
    private static function punnett(string $pai, string $mae, string $letra): array
    {
        $dominante = mb_strtoupper($letra);
        $saida = [];

        foreach (self::gametas($pai) as $g1) {
            foreach (self::gametas($mae) as $g2) {
                $par = [$g1, $g2];
                // O alelo dominante vem sempre primeiro, para que Aa e aA
                // sejam contados como o mesmo genotipo.
                usort($par, fn ($x, $y) => ($x === $dominante ? 0 : 1) <=> ($y === $dominante ? 0 : 1));
                $genotipo = implode('', $par);
                $saida[$genotipo] = ($saida[$genotipo] ?? 0) + 1;
            }
        }

        return $saida;
    }

    /** Quantas das 4 casas expressam o fenotipo dominante. */
    private static function dominantes(array $quadro, string $letra): int
    {
        $dominante = mb_strtoupper($letra);
        $total = 0;

        foreach ($quadro as $genotipo => $qtd) {
            if (str_contains($genotipo, $dominante)) {
                $total += $qtd;
            }
        }

        return $total;
    }

    private static function porcentagem(int $parte, int $total): string
    {
        return self::num(100 * $parte / $total) . '%';
    }

    private static function monoibridoFenotipo(): iterable
    {
        $cruzamentos = [['Aa', 'Aa'], ['Aa', 'aa'], ['AA', 'aa'], ['AA', 'Aa'], ['aa', 'aa'], ['AA', 'AA']];

        foreach (self::CARACTERES as [$letra, $caracteristica, $dom, $rec, $organismo]) {
            foreach ($cruzamentos as [$p1, $p2]) {
                $g1 = str_replace(['A', 'a'], [mb_strtoupper($letra), mb_strtolower($letra)], $p1);
                $g2 = str_replace(['A', 'a'], [mb_strtoupper($letra), mb_strtolower($letra)], $p2);

                $quadro = self::punnett($g1, $g2, $letra);
                $comDominante = self::dominantes($quadro, $letra);

                yield new Item(
                    sprintf(
                        'Em %s, %s é determinada por um par de alelos com dominância completa, em que o alelo %s condiciona a característica %s e o alelo %s, a característica %s. Do cruzamento entre um indivíduo %s e um indivíduo %s, a proporção esperada de descendentes com fenótipo %s é:',
                        $organismo, $caracteristica,
                        mb_strtoupper($letra), $dom,
                        mb_strtolower($letra), $rec,
                        $g1, $g2, $dom
                    ),
                    'facil',
                    self::porcentagem($comDominante, 4),
                    self::distratores(self::porcentagem($comDominante, 4), [
                        self::porcentagem(4 - $comDominante, 4),   // devolve o fenotipo recessivo
                        self::porcentagem(2, 4),                   // supoe sempre 1:1
                        self::porcentagem(3, 4),                   // supoe sempre 3:1
                    ], [
                        self::porcentagem(1, 4),
                        self::porcentagem(4, 4),
                        self::porcentagem(0, 4),
                        '12,5%',
                        '62,5%',
                    ])
                );
            }
        }
    }

    private static function monoibridoGenotipo(): iterable
    {
        $cruzamentos = [['Aa', 'Aa'], ['Aa', 'aa'], ['AA', 'Aa'], ['AA', 'aa']];

        foreach (self::CARACTERES as [$letra, $caracteristica, , , $organismo]) {
            foreach ($cruzamentos as [$p1, $p2]) {
                $D = mb_strtoupper($letra);
                $r = mb_strtolower($letra);
                $g1 = str_replace(['A', 'a'], [$D, $r], $p1);
                $g2 = str_replace(['A', 'a'], [$D, $r], $p2);

                $quadro = self::punnett($g1, $g2, $letra);
                $heterozigotos = $quadro["$D$r"] ?? 0;

                yield new Item(
                    sprintf(
                        'Considere, em %s, um gene com dominância completa que determina %s. Do cruzamento %s x %s, a proporção esperada de descendentes heterozigotos é:',
                        $organismo, $caracteristica, $g1, $g2
                    ),
                    'medio',
                    self::porcentagem($heterozigotos, 4),
                    self::distratores(self::porcentagem($heterozigotos, 4), [
                        self::porcentagem(self::dominantes($quadro, $letra), 4),  // conta o fenotipo, nao o genotipo
                        self::porcentagem(($quadro["$D$D"] ?? 0), 4),             // conta os homozigotos dominantes
                        self::porcentagem(($quadro["$r$r"] ?? 0), 4),             // conta os homozigotos recessivos
                    ], [
                        self::porcentagem(0, 4),
                        self::porcentagem(1, 4),
                        self::porcentagem(2, 4),
                        self::porcentagem(3, 4),
                        self::porcentagem(4, 4),
                    ])
                );
            }
        }
    }

    private static function cruzamentoTeste(): iterable
    {
        foreach (self::CARACTERES as [$letra, $caracteristica, $dom, $rec, $organismo]) {
            $D = mb_strtoupper($letra);
            $r = mb_strtolower($letra);

            yield new Item(
                sprintf(
                    'Um indivíduo de %s apresenta a característica %s (%s), mas seu genótipo é desconhecido. Para descobri-lo, ele é cruzado com um indivíduo %s%s. Se metade da descendência apresentar a característica %s, conclui-se que o genótipo do indivíduo testado é:',
                    $organismo, $dom, $caracteristica, $r, $r, $rec
                ),
                'medio',
                "$D$r",
                self::distratores("$D$r", [
                    "$D$D",       // conclusao oposta
                    "$r$r",       // ignora o fenotipo dominante do testado
                    'Não é possível concluir a partir desse cruzamento',
                ], [
                    "$r$D",
                    'Depende do sexo dos descendentes',
                ])
            );

            yield new Item(
                sprintf(
                    'Em %s, dois indivíduos com a característica %s cruzam entre si e geram um descendente com a característica %s. Os genótipos dos pais são, necessariamente:',
                    $organismo, $dom, $rec
                ),
                'medio',
                "$D$r e $D$r",
                self::distratores("$D$r e $D$r", [
                    "$D$D e $D$D",       // desconsidera o descendente recessivo
                    "$D$D e $r$r",       // supoe cruzamento-teste
                    "$r$r e $r$r",       // contradiz o fenotipo dos pais
                ], [
                    "$D$D e $D$r",
                    'Não é possível determinar',
                ])
            );
        }
    }

    /** Nome canonico do genotipo ABO formado por dois gametas. */
    private static function genotipoAbo(string $a, string $b): string
    {
        if ($a === 'i' && $b === 'i') {
            return 'ii';
        }

        // O alelo i vem sempre depois, e IA antes de IB, para que IAi e iIA
        // sejam o mesmo genotipo.
        if ($a === 'i') {
            return "$b$a";
        }

        if ($b === 'i') {
            return "$a$b";
        }

        return $a <= $b ? "$a$b" : "$b$a";
    }

    private static function sanguineo(): iterable
    {
        // Sistema ABO: alelos IA e IB codominantes entre si, ambos dominantes
        // sobre i.
        $genotipos = [
            'IAIA' => 'A', 'IAi' => 'A',
            'IBIB' => 'B', 'IBi' => 'B',
            'IAIB' => 'AB',
            'ii'   => 'O',
        ];

        $gametas = [
            'IAIA' => ['IA', 'IA'], 'IAi' => ['IA', 'i'],
            'IBIB' => ['IB', 'IB'], 'IBi' => ['IB', 'i'],
            'IAIB' => ['IA', 'IB'], 'ii' => ['i', 'i'],
        ];

        $pares = [
            ['IAi', 'IBi'], ['IAIB', 'ii'], ['IAi', 'IAi'], ['IBi', 'ii'],
            ['IAIB', 'IAi'], ['IAi', 'ii'], ['IAIB', 'IBi'], ['IAIB', 'IAIB'],
        ];

        foreach ($pares as [$p1, $p2]) {
            $tipos = [];
            foreach ($gametas[$p1] as $a) {
                foreach ($gametas[$p2] as $b) {
                    $tipos[$genotipos[self::genotipoAbo($a, $b)]] = true;
                }
            }

            $possiveis = array_keys($tipos);
            sort($possiveis);
            $certa = implode(', ', $possiveis);

            $todos = ['A', 'AB', 'B', 'O'];
            $ausentes = array_values(array_diff($todos, $possiveis));

            yield new Item(
                sprintf(
                    'No sistema ABO, os alelos IA e IB são codominantes entre si e ambos dominantes sobre o alelo i. De um casal com genótipos %s e %s, os tipos sanguíneos possíveis para os filhos são:',
                    $p1, $p2
                ),
                'dificil',
                $certa,
                self::distratores($certa, [
                    implode(', ', $todos),                                            // supoe todos possiveis
                    $ausentes === [] ? 'Somente O' : implode(', ', $ausentes),        // inverte o raciocinio
                    'Somente ' . $possiveis[0],                                       // para na primeira combinacao
                ], [
                    'A, B',
                    'AB, O',
                    'Somente O',
                ])
            );
        }
    }

    private static function diibrido(): iterable
    {
        $cruzamentos = [
            ['AaBb', 'AaBb'], ['AaBb', 'aabb'], ['AaBb', 'Aabb'],
            ['AABb', 'aaBb'], ['AaBB', 'aabb'], ['AaBb', 'AABb'],
        ];

        foreach (self::CARACTERES as $i => [$letra1, $car1, $dom1, $rec1, $organismo]) {
            [$letra2, $car2, $dom2, $rec2] = self::CARACTERES[($i + 1) % count(self::CARACTERES)];

            if ($letra1 === $letra2) {
                continue;
            }

            foreach ($cruzamentos as [$p1, $p2]) {
                $traduz = fn (string $g) => str_replace(
                    ['A', 'a', 'B', 'b'],
                    [mb_strtoupper($letra1), mb_strtolower($letra1), mb_strtoupper($letra2), mb_strtolower($letra2)],
                    $g
                );

                $g1 = $traduz($p1);
                $g2 = $traduz($p2);

                // Segregacao independente: as duas proporcoes se multiplicam.
                $quadro1 = self::punnett(mb_substr($g1, 0, 2), mb_substr($g2, 0, 2), $letra1);
                $quadro2 = self::punnett(mb_substr($g1, 2, 2), mb_substr($g2, 2, 2), $letra2);

                $d1 = self::dominantes($quadro1, $letra1);
                $d2 = self::dominantes($quadro2, $letra2);

                $favoraveis = $d1 * $d2;

                yield new Item(
                    sprintf(
                        'Em %s, dois genes de segregação independente determinam %s e %s, ambos com dominância completa. Do cruzamento %s x %s, a proporção esperada de descendentes que apresentam simultaneamente as características %s e %s é:',
                        $organismo, $car1, $car2, $g1, $g2, $dom1, $dom2
                    ),
                    'dificil',
                    self::fracao($favoraveis, 16),
                    self::distratores(self::fracao($favoraveis, 16), [
                        self::fracao($d1 + $d2, 16),               // soma em vez de multiplicar
                        self::fracao($d1, 4),                      // considera apenas um dos genes
                        self::fracao(9, 16),                       // supoe sempre 9:3:3:1
                    ], [
                        self::fracao(16 - $favoraveis, 16),
                        self::fracao(1, 16),
                        self::fracao(3, 16),
                    ])
                );

                $recessivoDuplo = (4 - $d1) * (4 - $d2);

                yield new Item(
                    sprintf(
                        'Considerando o cruzamento %s x %s em %s, com dois genes de segregação independente e dominância completa, a proporção esperada de descendentes %s e %s é:',
                        $g1, $g2, $organismo, $rec1, $rec2
                    ),
                    'dificil',
                    self::fracao($recessivoDuplo, 16),
                    self::distratores(self::fracao($recessivoDuplo, 16), [
                        self::fracao($favoraveis, 16),             // devolve o duplo dominante
                        self::fracao((4 - $d1) + (4 - $d2), 16),   // soma em vez de multiplicar
                        self::fracao(1, 16),                       // supoe sempre 1/16
                    ], [
                        self::fracao(3, 16),
                        self::fracao(9, 16),
                        self::fracao(0, 16),
                    ])
                );
            }
        }
    }

    private static function probabilidadeDupla(): iterable
    {
        foreach (self::CARACTERES as [$letra, $caracteristica, $dom, $rec, $organismo]) {
            $D = mb_strtoupper($letra);
            $r = mb_strtolower($letra);

            foreach ([2, 3] as $filhos) {
                // Cruzamento Aa x Aa: cada filho tem 3/4 de chance de fenotipo
                // dominante e 1/4 de recessivo, e os eventos sao independentes.
                $denominador = 4 ** $filhos;

                yield new Item(
                    sprintf(
                        'Em %s, o cruzamento entre dois indivíduos %s%s, com dominância completa, gera %d descendentes. A probabilidade de todos apresentarem a característica %s é:',
                        $organismo, $D, $r, $filhos, $rec
                    ),
                    'dificil',
                    self::fracao(1, $denominador),
                    self::distratores(self::fracao(1, $denominador), [
                        self::fracao(1, 4),                              // considera um unico descendente
                        self::fracao(1, 4 * $filhos),                    // soma em vez de multiplicar
                        self::fracao(3 ** $filhos, $denominador),        // devolve o fenotipo dominante
                    ], [
                        self::fracao(1, $denominador * 2),
                        self::fracao(2, $denominador),
                    ])
                );
            }
        }
    }
}
