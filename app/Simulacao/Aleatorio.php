<?php

namespace App\Simulacao;

/**
 * Gerador pseudoaleatorio proprio, com semente explicita.
 *
 * O experimento precisa ser reproduzivel: relatar um numero sem que outra
 * pessoa possa reobtelo nao e resultado, e opiniao. As funcoes nativas
 * (mt_rand, random_int) foram descartadas por dois motivos. A primeira depende
 * de um estado global, e qualquer trecho de codigo que sorteie algo no meio da
 * execucao desloca toda a sequencia seguinte; a segunda e criptografica e nao
 * aceita semente.
 *
 * Com o estado encapsulado, cada sorteio do experimento tem seu proprio fluxo,
 * o que permite dar aos dois bracos de comparacao exatamente os mesmos numeros
 * nas mesmas posicoes (ver Experimento).
 */
final class Aleatorio
{
    /**
     * Congruencia linear de Lehmer nos parametros de Numerical Recipes.
     * Periodo de 2^32, suficiente para a escala deste experimento, e barato o
     * bastante para ser chamado alguns milhoes de vezes.
     */
    private const MULTIPLICADOR = 1664525;
    private const INCREMENTO    = 1013904223;
    private const MODULO        = 4294967296; // 2^32

    private int $estado;

    public function __construct(int $semente)
    {
        $this->estado = abs($semente) % self::MODULO;
    }

    private function proximo(): int
    {
        return $this->estado = (self::MULTIPLICADOR * $this->estado + self::INCREMENTO) % self::MODULO;
    }

    /**
     * Real em [0, 1).
     *
     * Descarta os oito bits baixos. Em congruencias lineares de modulo potencia
     * de dois os bits menos significativos tem periodo curto, e usa-los
     * produziria padroes visiveis nos sorteios de acerto.
     */
    public function real(): float
    {
        return ($this->proximo() >> 8) / 16777216.0;
    }

    /** Inteiro em [$min, $max], inclusive nos dois extremos. */
    public function inteiro(int $min, int $max): int
    {
        return $min + (int) floor($this->real() * ($max - $min + 1));
    }

    /**
     * Amostra de uma normal, pela transformacao de Box-Muller.
     *
     * A proficiencia de base dos agentes vem de uma normal, e nao de uma
     * uniforme, porque o interesse e que a populacao tenha concentracao no meio
     * e caudas ralas: um experimento em que todos os niveis de habilidade sao
     * igualmente frequentes exageraria o peso dos extremos nas medias.
     */
    public function normal(float $media, float $desvio): float
    {
        $u = max(1e-12, $this->real());
        $v = $this->real();

        return $media + $desvio * sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
    }

    /**
     * Permutacao de Fisher-Yates.
     *
     * @param array<int, mixed> $itens
     * @return array<int, mixed>
     */
    public function embaralhar(array $itens): array
    {
        for ($i = count($itens) - 1; $i > 0; $i--) {
            $j = $this->inteiro(0, $i);
            [$itens[$i], $itens[$j]] = [$itens[$j], $itens[$i]];
        }

        return $itens;
    }
}
