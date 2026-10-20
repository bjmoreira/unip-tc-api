<?php

namespace Database\Seeders\Banco;

/**
 * Funcoes: gerador computado.
 *
 * Cada familia de questao percorre um espaco de parametros escolhido para que o
 * resultado seja exato, sem dizima nem raiz irracional, o que manteria a
 * dificuldade na aritmetica em vez de no conceito. Os distratores reproduzem
 * enganos conhecidos: trocar o sinal ao isolar a incognita, somar em vez de
 * multiplicar, esquecer de dividir pelo coeficiente.
 */
final class Funcoes extends Gerador
{
    public static function topico(): string
    {
        return 'funcoes';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar(self::familias(), $quantidade);
    }

    /** @return array<callable(): iterable<Item>> */
    private static function familias(): array
    {
        return [
            fn () => self::valorNumerico(),
            fn () => self::raizAfim(),
            fn () => self::coeficienteAngular(),
            fn () => self::verticeParabola(),
            fn () => self::raizesQuadratica(),
            fn () => self::composicao(),
        ];
    }

    /** f(x) = ax + b, calcular f(k). */
    private static function valorNumerico(): iterable
    {
        foreach ([2, 3, 4, 5, -2, -3] as $a) {
            foreach ([-6, -1, 3, 7, 10] as $b) {
                foreach ([2, 5] as $k) {
                    $certo = $a * $k + $b;

                    yield new Item(
                        sprintf(
                            'Considere a função f(x) = %sx %s %s. O valor de f(%d) é:',
                            self::num($a),
                            $b < 0 ? '-' : '+',
                            self::num(abs($b)),
                            $k
                        ),
                        'facil',
                        self::num($certo),
                        self::distratores(self::num($certo), [
                            self::num($a * $k - $b),   // troca o sinal de b
                            self::num($a + $b * $k),   // inverte os papeis
                            self::num($a + $b + $k),   // soma tudo
                        ], [
                            self::num($certo + 1),
                            self::num(-$certo),
                            self::num($certo * 2),
                        ])
                    );
                }
            }
        }
    }

    /** Raiz de ax + b = 0, construida a partir da raiz inteira. */
    private static function raizAfim(): iterable
    {
        foreach ([2, 3, 4, 5, 6, 7] as $a) {
            foreach ([-6, -3, -1, 2, 4, 9] as $r) {
                $b = -$a * $r;

                yield new Item(
                    sprintf(
                        'A raiz da função f(x) = %dx %s %s é:',
                        $a,
                        $b < 0 ? '-' : '+',
                        self::num(abs($b))
                    ),
                    'facil',
                    self::num($r),
                    self::distratores(self::num($r), [
                        self::num(-$r),          // esquece de trocar o sinal
                        self::num($b),           // para em x = b
                        self::num($a * $r),      // esquece de dividir por a
                    ], [
                        self::num($r + 1),
                        self::num($r - 1),
                        self::num($a),
                    ])
                );
            }
        }
    }

    /** Coeficiente angular da reta por dois pontos, com divisao exata. */
    private static function coeficienteAngular(): iterable
    {
        foreach ([[1, 3], [2, 5], [-1, 4], [0, 2], [3, 1]] as [$x1, $y1]) {
            foreach ([2, -2, 3, -3, 4] as $m) {
                foreach ([2, 4] as $d) {
                    $x2 = $x1 + $d;
                    $y2 = $y1 + $m * $d;

                    yield new Item(
                        sprintf(
                            'A reta que passa pelos pontos A(%d, %d) e B(%d, %d) tem coeficiente angular igual a:',
                            $x1, $y1, $x2, $y2
                        ),
                        'medio',
                        self::num($m),
                        self::distratores(self::num($m), [
                            self::num(-$m),                                  // inverte a ordem dos pontos
                            self::fracao($x2 - $x1, $y2 - $y1),              // inverte a razao
                            self::num($y2 - $y1),                            // esquece o denominador
                        ], [
                            self::num($m + 1),
                            self::num($m - 1),
                            self::num($m * 2),
                        ])
                    );
                }
            }
        }
    }

    /** Vertice de f(x) = a(x - h)^2 + k, apresentada na forma expandida. */
    private static function verticeParabola(): iterable
    {
        foreach ([1, 2, -1, -2] as $a) {
            foreach ([-3, -1, 2, 4] as $h) {
                foreach ([-5, 0, 6] as $k) {
                    // f(x) = a x^2 - 2ah x + (a h^2 + k)
                    $b = -2 * $a * $h;
                    $c = $a * $h * $h + $k;

                    $termoA = $a === 1 ? 'x²' : ($a === -1 ? '-x²' : self::num($a) . 'x²');
                    $termoB = $b === 0 ? '' : sprintf(' %s %sx', $b < 0 ? '-' : '+', self::num(abs($b)));
                    $termoC = $c === 0 ? '' : sprintf(' %s %s', $c < 0 ? '-' : '+', self::num(abs($c)));

                    yield new Item(
                        sprintf(
                            'O vértice da parábola dada por f(x) = %s%s%s é o ponto:',
                            $termoA, $termoB, $termoC
                        ),
                        'dificil',
                        sprintf('(%s, %s)', self::num($h), self::num($k)),
                        self::distratores(sprintf('(%s, %s)', self::num($h), self::num($k)), [
                            sprintf('(%s, %s)', self::num(-$h), self::num($k)),  // esquece o sinal em -b/2a
                            sprintf('(%s, %s)', self::num($k), self::num($h)),   // troca as coordenadas
                            sprintf('(%s, %s)', self::num($h), self::num($c)),   // usa o termo independente
                        ], [
                            sprintf('(%s, %s)', self::num($h), self::num($k + 1)),
                            sprintf('(%s, %s)', self::num($b), self::num($c)),
                            sprintf('(%s, %s)', self::num(0), self::num($c)),
                        ])
                    );
                }
            }
        }
    }

    /** Raizes de uma quadratica montada a partir de duas raizes inteiras. */
    private static function raizesQuadratica(): iterable
    {
        foreach ([[1, 2], [-1, 3], [2, 5], [-2, -4], [1, -6], [3, 4], [-3, 5]] as [$r1, $r2]) {
            foreach ([1, 2] as $a) {
                // a(x - r1)(x - r2) = a x^2 - a(r1+r2) x + a r1 r2
                $b = -$a * ($r1 + $r2);
                $c = $a * $r1 * $r2;

                $termoA = $a === 1 ? 'x²' : self::num($a) . 'x²';
                $termoB = $b === 0 ? '' : sprintf(' %s %sx', $b < 0 ? '-' : '+', self::num(abs($b)));
                $termoC = $c === 0 ? '' : sprintf(' %s %s', $c < 0 ? '-' : '+', self::num(abs($c)));

                $menor = min($r1, $r2);
                $maior = max($r1, $r2);
                $certa = sprintf('%s e %s', self::num($menor), self::num($maior));

                yield new Item(
                    sprintf('As raízes da equação %s%s%s = 0 são:', $termoA, $termoB, $termoC),
                    'dificil',
                    $certa,
                    self::distratores($certa, [
                        sprintf('%s e %s', self::num(-$menor), self::num(-$maior)),  // troca os sinais
                        sprintf('%s e %s', self::num($menor), self::num(-$maior)),
                        sprintf('%s e %s', self::num($b), self::num($c)),            // confunde com os coeficientes
                    ], [
                        sprintf('%s e %s', self::num($menor + 1), self::num($maior + 1)),
                        sprintf('%s e %s', self::num(0), self::num($maior)),
                        'A equação não possui raízes reais',
                    ])
                );
            }
        }
    }

    /** Composicao f(g(x)) avaliada em um ponto. */
    private static function composicao(): iterable
    {
        foreach ([[2, 1], [3, -2], [-1, 4], [4, 3]] as [$a, $b]) {
            foreach ([[1, 5], [2, -3], [-2, 2]] as [$c, $d]) {
                foreach ([1, 3] as $k) {
                    $g = $c * $k + $d;
                    $certo = $a * $g + $b;

                    yield new Item(
                        sprintf(
                            'Sejam f(x) = %sx %s %s e g(x) = %sx %s %s. O valor de f(g(%d)) é:',
                            self::num($a), $b < 0 ? '-' : '+', self::num(abs($b)),
                            self::num($c), $d < 0 ? '-' : '+', self::num(abs($d)),
                            $k
                        ),
                        'medio',
                        self::num($certo),
                        self::distratores(self::num($certo), [
                            self::num($c * ($a * $k + $b) + $d),   // calcula g(f(k))
                            self::num($a * $k + $b),               // para em f(k)
                            self::num($g),                         // para em g(k)
                        ], [
                            self::num($certo + 2),
                            self::num(-$certo),
                            self::num($certo * 2),
                        ])
                    );
                }
            }
        }
    }
}
