<?php

namespace Database\Seeders\Banco;

/**
 * Probabilidade: gerador computado.
 *
 * As respostas saem de contagem exata sobre o espaco amostral descrito no
 * enunciado, e sao apresentadas como fracao ja simplificada. Os distratores
 * reproduzem os enganos classicos do topico: usar o complementar, esquecer que
 * a retirada foi sem reposicao, trocar arranjo por combinacao.
 */
final class Probabilidade extends Gerador
{
    public static function topico(): string
    {
        return 'probabilidade';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::dado(),
            fn () => self::urnaSimples(),
            fn () => self::moedas(),
            fn () => self::doisDados(),
            fn () => self::semReposicao(),
            fn () => self::combinacoes(),
            fn () => self::baralho(),
            fn () => self::independentes(),
        ], $quantidade);
    }

    private static function fatorial(int $n): int
    {
        $r = 1;
        for ($i = 2; $i <= $n; $i++) {
            $r *= $i;
        }

        return $r;
    }

    private static function combinacao(int $n, int $k): int
    {
        if ($k < 0 || $k > $n) {
            return 0;
        }

        return intdiv(self::fatorial($n), self::fatorial($k) * self::fatorial($n - $k));
    }

    /** Dado de n faces, evento "resultado maior que k". */
    private static function dado(): iterable
    {
        foreach ([6, 8, 10, 12, 20] as $faces) {
            foreach (range(1, min(10, $faces - 1)) as $k) {
                $favoraveis = $faces - $k;

                yield new Item(
                    sprintf(
                        'Um dado honesto de %d faces, numeradas de 1 a %d, é lançado uma vez. A probabilidade de sair um número maior que %d é:',
                        $faces, $faces, $k
                    ),
                    'facil',
                    self::fracao($favoraveis, $faces),
                    self::distratores(self::fracao($favoraveis, $faces), [
                        self::fracao($k, $faces),                  // conta o evento complementar
                        self::fracao($favoraveis + 1, $faces),      // inclui o proprio k
                        self::fracao(1, $faces),                    // considera um unico resultado
                    ], [
                        self::fracao($favoraveis, $faces + 1),
                        self::fracao($faces, $favoraveis),
                    ])
                );
            }
        }
    }

    /** Urna com bolas de tres cores, uma retirada. */
    private static function urnaSimples(): iterable
    {
        foreach ([[3, 5, 2], [4, 4, 4], [6, 3, 1], [2, 7, 5], [5, 5, 10], [8, 2, 6], [1, 4, 5], [7, 7, 6], [9, 3, 3], [2, 2, 6], [10, 5, 5], [4, 9, 7]] as [$b, $p, $v]) {
            $total = $b + $p + $v;

            foreach ([['branca', $b], ['preta', $p], ['vermelha', $v]] as [$cor, $qtd]) {
                yield new Item(
                    sprintf(
                        'Uma urna contém %d bolas brancas, %d pretas e %d vermelhas. Retirando-se uma bola ao acaso, a probabilidade de ela ser %s é:',
                        $b, $p, $v, $cor
                    ),
                    'facil',
                    self::fracao($qtd, $total),
                    self::distratores(self::fracao($qtd, $total), [
                        self::fracao($total - $qtd, $total),        // calcula o complementar
                        self::fracao($qtd, $total - $qtd),          // usa razao em vez de proporcao
                        self::fracao(1, 3),                         // supoe as cores equiprovaveis
                    ], [
                        self::fracao($qtd + 1, $total),
                        self::fracao($qtd, $total + 1),
                    ])
                );
            }
        }
    }

    /** n lancamentos de uma moeda, exatamente k caras. */
    private static function moedas(): iterable
    {
        foreach (range(2, 6) as $n) {
            $espaco = 2 ** $n;

            foreach (range(0, $n) as $k) {
                $favoraveis = self::combinacao($n, $k);

                yield new Item(
                    sprintf(
                        'Uma moeda honesta é lançada %d vezes. A probabilidade de sair exatamente %d %s é:',
                        $n, $k, $k === 1 ? 'cara' : 'caras'
                    ),
                    'medio',
                    self::fracao($favoraveis, $espaco),
                    self::distratores(self::fracao($favoraveis, $espaco), [
                        self::fracao($k, $n),                       // confunde com a proporcao pedida
                        self::fracao(1, $espaco),                   // conta uma unica sequencia
                        self::fracao($favoraveis, $n * 2),          // erra o espaco amostral
                    ], [
                        self::fracao(1, 2),
                        self::fracao($favoraveis + 1, $espaco),
                        self::fracao($espaco - $favoraveis, $espaco),
                        self::fracao($favoraveis, $espaco * 2),
                        self::fracao(1, $n + 1),
                        self::fracao($n, $espaco),
                    ])
                );
            }
        }
    }

    /** Soma de dois dados de seis faces. */
    private static function doisDados(): iterable
    {
        $contagem = array_fill(2, 11, 0);
        for ($a = 1; $a <= 6; $a++) {
            for ($b = 1; $b <= 6; $b++) {
                $contagem[$a + $b]++;
            }
        }

        foreach (range(2, 12) as $soma) {
            $favoraveis = $contagem[$soma];

            yield new Item(
                sprintf(
                    'Dois dados honestos de seis faces são lançados simultaneamente. A probabilidade de a soma dos resultados ser igual a %d é:',
                    $soma
                ),
                'medio',
                self::fracao($favoraveis, 36),
                self::distratores(self::fracao($favoraveis, 36), [
                    self::fracao($favoraveis, 12),                  // soma os espacos em vez de multiplicar
                    self::fracao(1, 36),                            // conta um unico par
                    self::fracao(1, 11),                            // supoe as somas equiprovaveis
                ], [
                    self::fracao($favoraveis + 1, 36),
                    self::fracao(6, 36),
                ])
            );
        }
    }

    /** Duas retiradas sucessivas, sem reposicao. */
    private static function semReposicao(): iterable
    {
        foreach ([[4, 6], [5, 5], [3, 7], [6, 4], [7, 3], [8, 2], [5, 10], [9, 6], [2, 8], [10, 4], [6, 9], [3, 12], [7, 8], [11, 5]] as [$b, $p]) {
            $total = $b + $p;

            $favoraveis = $b * ($b - 1);
            $espaco = $total * ($total - 1);

            yield new Item(
                sprintf(
                    'Uma urna contém %d bolas brancas e %d bolas pretas. Duas bolas são retiradas sucessivamente, sem reposição. A probabilidade de ambas serem brancas é:',
                    $b, $p
                ),
                'dificil',
                self::fracao($favoraveis, $espaco),
                self::distratores(self::fracao($favoraveis, $espaco), [
                    self::fracao($b * $b, $total * $total),         // trata como se houvesse reposicao
                    self::fracao($b, $total),                       // considera uma unica retirada
                    self::fracao($b - 1, $total - 1),               // considera so a segunda retirada
                ], [
                    self::fracao($b * 2, $total * 2),
                    self::fracao($favoraveis, $total * $total),
                ])
            );

            $mistas = 2 * $b * $p;

            yield new Item(
                sprintf(
                    'De uma urna com %d bolas brancas e %d bolas pretas retiram-se duas bolas, uma após a outra, sem reposição. A probabilidade de sair uma bola de cada cor é:',
                    $b, $p
                ),
                'dificil',
                self::fracao($mistas, $espaco),
                self::distratores(self::fracao($mistas, $espaco), [
                    self::fracao($b * $p, $espaco),                 // esquece as duas ordens possiveis
                    self::fracao(2 * $b * $p, $total * $total),     // trata como se houvesse reposicao
                    self::fracao($b, $total),
                ], [
                    self::fracao($mistas + 2, $espaco),
                    self::fracao(1, 2),
                ])
            );
        }
    }

    /** Baralho comum de 52 cartas, sem coringas. */
    private static function baralho(): iterable
    {
        foreach (['copas', 'espadas', 'ouros', 'paus'] as $naipe) {
            yield new Item(
                sprintf(
                    'De um baralho comum de 52 cartas, sem coringas, retira-se uma carta ao acaso. A probabilidade de ela ser do naipe de %s é:',
                    $naipe
                ),
                'facil',
                self::fracao(13, 52),
                self::distratores(self::fracao(13, 52), [
                    self::fracao(1, 52),                // conta uma unica carta
                    self::fracao(4, 52),                // confunde com o numero de naipes
                    self::fracao(13, 4),                // inverte a razao
                ], [
                    self::fracao(1, 13),
                    self::fracao(1, 2),
                ])
            );
        }

        $valores = ['ás', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete',
                    'oito', 'nove', 'dez', 'valete', 'dama', 'rei'];

        foreach ($valores as $valor) {
            yield new Item(
                sprintf(
                    'Retirando-se uma carta ao acaso de um baralho comum de 52 cartas, a probabilidade de ela ser um %s é:',
                    $valor
                ),
                'facil',
                self::fracao(4, 52),
                self::distratores(self::fracao(4, 52), [
                    self::fracao(13, 52),               // confunde com o naipe
                    self::fracao(1, 52),                // conta uma unica carta
                    self::fracao(4, 13),                // erra o espaco amostral
                ], [
                    self::fracao(1, 4),
                    self::fracao(2, 52),
                ])
            );
        }

        yield new Item(
            'De um baralho comum de 52 cartas, a probabilidade de se retirar uma figura (valete, dama ou rei) é:',
            'medio',
            self::fracao(12, 52),
            self::distratores(self::fracao(12, 52), [
                self::fracao(3, 52),                    // conta so tres cartas
                self::fracao(4, 52),                    // conta so um dos valores
                self::fracao(13, 52),                   // confunde com o naipe
            ], [
                self::fracao(1, 4),
                self::fracao(12, 13),
            ])
        );

        yield new Item(
            'De um baralho comum de 52 cartas, a probabilidade de se retirar uma carta vermelha é:',
            'facil',
            self::fracao(26, 52),
            self::distratores(self::fracao(26, 52), [
                self::fracao(13, 52),                   // conta um unico naipe vermelho
                self::fracao(26, 26),
                self::fracao(2, 52),
            ], [
                self::fracao(1, 4),
                self::fracao(3, 4),
            ])
        );
    }

    /** Dois experimentos independentes: um dado e uma moeda. */
    private static function independentes(): iterable
    {
        foreach ([6, 8, 10, 12] as $faces) {
            foreach (range(1, 5) as $k) {
                $favoraveis = $faces - $k;
                $numerador = $favoraveis;
                $denominador = $faces * 2;

                yield new Item(
                    sprintf(
                        'Lançam-se, ao mesmo tempo, um dado honesto de %d faces e uma moeda honesta. A probabilidade de sair um número maior que %d no dado e cara na moeda é:',
                        $faces, $k
                    ),
                    'medio',
                    self::fracao($numerador, $denominador),
                    self::distratores(self::fracao($numerador, $denominador), [
                        self::fracao($favoraveis, $faces),                      // ignora a moeda
                        self::fracao($favoraveis * 2 + $faces, $faces * 2),     // soma em vez de multiplicar
                        self::fracao(1, 2),                                     // considera so a moeda
                    ], [
                        self::fracao($numerador + 1, $denominador),
                        self::fracao($faces, $denominador),
                        self::fracao(1, $faces),
                    ])
                );
            }
        }
    }

    /** Contagem: comissoes e anagramas. */
    private static function combinacoes(): iterable
    {
        foreach ([[6, 2], [7, 3], [8, 2], [9, 4], [10, 3], [12, 2], [5, 3], [11, 2], [6, 3], [7, 2], [8, 5], [9, 2], [10, 4], [13, 2]] as [$n, $k]) {
            $c = self::combinacao($n, $k);
            $arranjo = intdiv(self::fatorial($n), self::fatorial($n - $k));

            yield new Item(
                sprintf(
                    'De quantas maneiras é possível formar uma comissão de %d pessoas escolhidas em um grupo de %d?',
                    $k, $n
                ),
                'dificil',
                (string) $c,
                self::distratores((string) $c, [
                    (string) $arranjo,          // usa arranjo, contando a ordem
                    (string) ($n * $k),         // multiplica os dois numeros
                    (string) ($n - $k),         // subtrai
                ], [
                    (string) ($c * 2),
                    (string) ($c + $n),
                ])
            );
        }

        foreach (['LIVRO', 'CADERNO', 'PRATO', 'JANELA', 'MUSICA', 'PLANTA', 'FOGUEIRA', 'CINTURA', 'MEDULA', 'PALCO'] as $palavra) {
            $letras = mb_strlen($palavra);
            $total = self::fatorial($letras);

            yield new Item(
                sprintf(
                    'Quantos anagramas distintos podem ser formados com as letras da palavra %s, que não possui letras repetidas?',
                    $palavra
                ),
                'dificil',
                (string) $total,
                self::distratores((string) $total, [
                    (string) ($letras * $letras),           // eleva ao quadrado
                    (string) self::fatorial($letras - 1),   // fixa uma letra sem que o enunciado peca
                    (string) ($letras * 2),
                ], [
                    (string) ($total * 2),
                    (string) intdiv($total, 2),
                ])
            );
        }
    }
}
