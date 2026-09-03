<?php

namespace Database\Seeders\Banco;

/**
 * Geometria: gerador computado.
 *
 * As medidas foram escolhidas para produzir resultados exatos. Nas figuras
 * circulares o resultado fica expresso em funcao de pi, o que evita que a
 * questao se transforme em exercicio de arredondamento. Nos triangulos
 * retangulos so aparecem ternos pitagoricos e seus multiplos, pela mesma razao.
 */
final class Geometria extends Gerador
{
    /** Ternos pitagoricos primitivos usados como base. */
    private const TERNOS = [[3, 4, 5], [5, 12, 13], [8, 15, 17], [7, 24, 25], [20, 21, 29]];

    /** Poligonos cujo angulo interno e inteiro. */
    private const LADOS = [3, 4, 5, 6, 8, 9, 10, 12, 15, 18, 20, 24, 30, 36];

    public static function topico(): string
    {
        return 'geometria';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::retangulo(),
            fn () => self::triangulo(),
            fn () => self::pitagoras(),
            fn () => self::circulo(),
            fn () => self::volume(),
            fn () => self::poligono(),
            fn () => self::trapezio(),
        ], $quantidade);
    }

    private static function retangulo(): iterable
    {
        foreach ([4, 6, 7, 9, 12, 15] as $base) {
            foreach ([3, 5, 8, 11] as $altura) {
                $area = $base * $altura;
                $perimetro = 2 * ($base + $altura);

                yield new Item(
                    sprintf('Um retângulo tem %d cm de base e %d cm de altura. Sua área é:', $base, $altura),
                    'facil',
                    "$area cm²",
                    self::distratores("$area cm²", [
                        "$perimetro cm²",                  // calcula o perimetro
                        ($base + $altura) . ' cm²',        // soma os lados
                        ($area * 2) . ' cm²',              // dobra sem necessidade
                    ], [
                        ($area + $base) . ' cm²',
                        intdiv($area, 2) . ' cm²',
                    ])
                );

                yield new Item(
                    sprintf('O perímetro de um retângulo de %d cm por %d cm é:', $base, $altura),
                    'facil',
                    "$perimetro cm",
                    self::distratores("$perimetro cm", [
                        "$area cm",                        // calcula a area
                        ($base + $altura) . ' cm',         // soma so dois lados
                        (2 * $base + $altura) . ' cm',     // esquece um lado
                    ], [
                        ($perimetro + 2) . ' cm',
                        ($perimetro * 2) . ' cm',
                    ])
                );
            }
        }
    }

    private static function triangulo(): iterable
    {
        foreach ([4, 6, 8, 10, 12, 14, 16] as $base) {
            foreach ([3, 5, 7, 9] as $altura) {
                $area = $base * $altura / 2;

                yield new Item(
                    sprintf(
                        'Um triângulo tem base de %d cm e altura de %d cm. Sua área é:',
                        $base, $altura
                    ),
                    'facil',
                    self::num($area) . ' cm²',
                    self::distratores(self::num($area) . ' cm²', [
                        ($base * $altura) . ' cm²',            // esquece de dividir por 2
                        ($base + $altura) . ' cm²',            // soma
                        self::num($area / 2) . ' cm²',         // divide duas vezes
                    ], [
                        ($base * $altura + 2) . ' cm²',
                        (2 * ($base + $altura)) . ' cm²',
                    ])
                );
            }
        }
    }

    private static function pitagoras(): iterable
    {
        foreach (self::TERNOS as [$a, $b, $c]) {
            foreach ([1, 2, 3, 4] as $k) {
                $ka = $a * $k;
                $kb = $b * $k;
                $kc = $c * $k;

                yield new Item(
                    sprintf(
                        'Em um triângulo retângulo, os catetos medem %d cm e %d cm. A hipotenusa mede:',
                        $ka, $kb
                    ),
                    'medio',
                    "$kc cm",
                    self::distratores("$kc cm", [
                        ($ka + $kb) . ' cm',                        // soma os catetos
                        ($kb - $ka) . ' cm',                        // subtrai
                        self::num(sqrt($ka * $kb)) . ' cm',         // confunde com media geometrica
                    ], [
                        ($kc + 1) . ' cm',
                        ($kc - 1) . ' cm',
                        ($ka * $kb) . ' cm',
                    ])
                );

                yield new Item(
                    sprintf(
                        'A hipotenusa de um triângulo retângulo mede %d cm e um dos catetos mede %d cm. O outro cateto mede:',
                        $kc, $kb
                    ),
                    'medio',
                    "$ka cm",
                    self::distratores("$ka cm", [
                        ($kc - $kb) . ' cm',                        // subtrai as medidas em vez dos quadrados
                        ($kc + $kb) . ' cm',                        // soma
                        self::num(sqrt($kc * $kc + $kb * $kb)) . ' cm', // soma os quadrados
                    ], [
                        ($ka + 1) . ' cm',
                        ($ka * 2) . ' cm',
                    ])
                );
            }
        }
    }

    private static function circulo(): iterable
    {
        foreach (range(2, 15) as $r) {
            $area = $r * $r;
            $comprimento = 2 * $r;

            yield new Item(
                sprintf('A área de um círculo de raio %d cm é:', $r),
                'medio',
                "{$area}π cm²",
                self::distratores("{$area}π cm²", [
                    "{$comprimento}π cm²",          // usa a formula do comprimento
                    "{$r}π cm²",                    // esquece de elevar ao quadrado
                    (4 * $area) . 'π cm²',          // usa o diametro como raio
                ], [
                    ($area + 1) . 'π cm²',
                    ($area * 2) . 'π cm²',
                ])
            );

            yield new Item(
                sprintf('O comprimento de uma circunferência de raio %d cm é:', $r),
                'medio',
                "{$comprimento}π cm",
                self::distratores("{$comprimento}π cm", [
                    "{$area}π cm",                  // usa a formula da area
                    "{$r}π cm",                     // esquece o fator 2
                    (4 * $r) . 'π cm',              // dobra o diametro
                ], [
                    ($comprimento + 2) . 'π cm',
                    ($comprimento - 2) . 'π cm',
                ])
            );
        }
    }

    private static function volume(): iterable
    {
        foreach ([[2, 3, 4], [3, 5, 6], [4, 4, 7], [5, 6, 8], [2, 7, 9], [6, 6, 10]] as [$x, $y, $z]) {
            $v = $x * $y * $z;
            $superficie = 2 * ($x * $y + $x * $z + $y * $z);

            yield new Item(
                sprintf(
                    'Um paralelepípedo retângulo tem dimensões %d cm, %d cm e %d cm. Seu volume é:',
                    $x, $y, $z
                ),
                'dificil',
                "$v cm³",
                self::distratores("$v cm³", [
                    "$superficie cm³",                  // calcula a area total
                    ($x + $y + $z) . ' cm³',            // soma as dimensoes
                    ($x * $y) . ' cm³',                 // usa so duas dimensoes
                ], [
                    ($v + 10) . ' cm³',
                    intdiv($v, 2) . ' cm³',
                ])
            );
        }

        foreach ([2, 3, 4, 5, 6, 7] as $r) {
            foreach ([5, 10] as $h) {
                $v = $r * $r * $h;

                yield new Item(
                    sprintf(
                        'Um cilindro reto tem raio da base igual a %d cm e altura %d cm. Seu volume é:',
                        $r, $h
                    ),
                    'dificil',
                    "{$v}π cm³",
                    self::distratores("{$v}π cm³", [
                        ($r * $h) . 'π cm³',                // esquece de elevar o raio ao quadrado
                        ($r * $r) . 'π cm³',                // calcula so a area da base
                        (2 * $r * $h) . 'π cm³',            // usa a area lateral
                    ], [
                        ($v + $h) . 'π cm³',
                        ($v * 2) . 'π cm³',
                    ])
                );
            }
        }
    }

    private static function poligono(): iterable
    {
        foreach (self::LADOS as $n) {
            $soma = ($n - 2) * 180;
            $interno = intdiv($soma, $n);
            $externo = intdiv(360, $n);

            yield new Item(
                sprintf('A soma dos ângulos internos de um polígono convexo de %d lados é:', $n),
                'medio',
                "{$soma}°",
                self::distratores("{$soma}°", [
                    ($n * 180) . '°',              // esquece de subtrair 2
                    "{$interno}°",                 // devolve o angulo, nao a soma
                    '360°',                        // confunde com a soma dos externos
                ], [
                    (($n - 1) * 180) . '°',
                    ($soma + 180) . '°',
                    ($soma - 180) . '°',
                    (($n - 2) * 90) . '°',
                ])
            );

            if ($n >= 4) {
                yield new Item(
                    sprintf('Cada ângulo interno de um polígono regular de %d lados mede:', $n),
                    'dificil',
                    "{$interno}°",
                    self::distratores("{$interno}°", [
                        "{$externo}°",                 // devolve o angulo externo
                        "{$soma}°",                    // devolve a soma
                        '180°',                        // esquece de subtrair 2 antes de dividir
                    ], [
                        ($interno + 10) . '°',
                        ($interno - 10) . '°',
                        (180 - $interno) . '°',
                        '90°',
                    ])
                );
            }
        }
    }

    private static function trapezio(): iterable
    {
        foreach ([[6, 10], [4, 8], [5, 11], [7, 13], [3, 9], [8, 14]] as [$menor, $maior]) {
            foreach ([4, 6, 8] as $altura) {
                $area = ($menor + $maior) * $altura / 2;

                yield new Item(
                    sprintf(
                        'Um trapézio tem bases de %d cm e %d cm e altura de %d cm. Sua área é:',
                        $menor, $maior, $altura
                    ),
                    'dificil',
                    self::num($area) . ' cm²',
                    self::distratores(self::num($area) . ' cm²', [
                        (($menor + $maior) * $altura) . ' cm²',      // esquece de dividir por 2
                        ($menor + $maior + $altura) . ' cm²',        // soma tudo
                        ($maior * $altura) . ' cm²',                 // usa so a base maior
                    ], [
                        self::num($area + $altura) . ' cm²',
                        self::num($area / 2) . ' cm²',
                    ])
                );
            }
        }
    }
}
