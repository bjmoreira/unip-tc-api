<?php

namespace App\Recomendacao;

/**
 * Estimativa de proficiencia do estudante por topico, baseada no sistema Elo.
 *
 * Cada topico do estudante possui uma pontuacao de proficiencia e cada questao
 * possui uma pontuacao de dificuldade, ambas na mesma escala. A partir dessas
 * duas grandezas calcula-se a probabilidade esperada de acerto, e a proficiencia
 * e ajustada conforme o resultado observado se afaste do esperado.
 *
 * A escolha do Elo em vez de um modelo probabilistico de rastreamento do
 * conhecimento (Corbett e Anderson, 1995) se deve a tres caracteristicas:
 * nao exige calibracao previa com dados historicos de outros estudantes,
 * cada variacao da pontuacao e explicavel, e o custo computacional permite
 * atualizar a estimativa a cada resposta.
 *
 * Limitacao assumida: o modelo nao representa explicitamente o acerto por
 * adivinhacao nem o esquecimento, tratando ambos como ruido absorvido ao
 * longo das respostas.
 */
class Elo
{
    /** Pontuacao inicial atribuida a um topico ainda nao praticado. */
    public const PONTUACAO_INICIAL = 1200.0;

    /**
     * Amplitude da escala. Uma diferenca de 400 pontos entre proficiencia e
     * dificuldade corresponde a uma probabilidade de acerto de 10 para 1.
     */
    private const ESCALA = 400.0;

    /**
     * Fator de ajuste. Determina o quanto a pontuacao se move a cada resposta.
     * Valor alto torna a estimativa reativa mas instavel; valor baixo torna a
     * convergencia lenta. O valor adotado e reduzido conforme o topico acumula
     * praticas, de modo que a estimativa se estabilize (ver fatorK).
     */
    private const K_INICIAL = 40.0;
    private const K_MINIMO  = 16.0;

    /**
     * Probabilidade esperada de o estudante acertar a questao.
     *
     * Se proficiencia e dificuldade forem iguais, retorna 0.5. Quanto maior a
     * proficiencia em relacao a dificuldade, mais a probabilidade se aproxima de 1.
     */
    public static function probabilidadeAcerto(float $proficiencia, float $dificuldade): float
    {
        return 1.0 / (1.0 + pow(10.0, ($dificuldade - $proficiencia) / self::ESCALA));
    }

    /**
     * Fator de ajuste decrescente: quanto mais o topico foi praticado, menor a
     * amplitude do movimento, porque a estimativa ja tem mais evidencia.
     */
    public static function fatorK(int $praticas): float
    {
        $k = self::K_INICIAL - ($praticas * 1.5);

        return max(self::K_MINIMO, $k);
    }

    /**
     * Nova proficiencia apos uma resposta.
     *
     * O ajuste e proporcional a surpresa: acertar uma questao dificil (baixa
     * probabilidade esperada) eleva bastante a pontuacao; acertar uma facil
     * eleva pouco. Errar uma facil reduz de forma expressiva.
     */
    public static function atualizar(
        float $proficiencia,
        float $dificuldade,
        bool $acertou,
        int $praticas = 0
    ): float {
        $esperado  = self::probabilidadeAcerto($proficiencia, $dificuldade);
        $observado = $acertou ? 1.0 : 0.0;

        return $proficiencia + self::fatorK($praticas) * ($observado - $esperado);
    }
}
