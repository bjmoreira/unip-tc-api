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
     * Estimadores disponiveis.
     *
     * O padrao e o que a aplicacao usa. Os outros dois existem porque o
     * experimento mostrou um vies sistematico: o K decrescente encosta no piso
     * e, a partir dai, a estimativa deixa de acompanhar uma proficiencia real
     * que continua subindo. O Elo classico supoe habilidade estacionaria, e
     * aqui ela nao e: o estudante aprende enquanto e medido.
     *
     * PADRAO       K decrescente com piso fixo.
     * K_ADAPTATIVO O K volta a crescer quando os erros de previsao ficam
     *              sistematicamente do mesmo lado, sinal de que a estimativa
     *              esta atrasada, e cai ao piso quando se alternam, sinal de
     *              que o que resta e ruido.
     * TENDENCIA    O K continua como no padrao, mas a pontuacao recebe tambem
     *              uma fracao da media movel dos proprios ajustes, antecipando
     *              o movimento em vez de so reagir a ele.
     */
    public const MODO_PADRAO       = 'padrao';
    public const MODO_K_ADAPTATIVO = 'k_adaptativo';
    public const MODO_TENDENCIA    = 'tendencia';

    /** Peso do historico na media movel dos dois modos alternativos. */
    private const BETA = 0.7;

    /** Quanto da tendencia estimada entra na pontuacao, no modo TENDENCIA. */
    private const GAMA = 0.5;

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

    /**
     * Atualizacao com estado, usada pelos estimadores alternativos.
     *
     * Os dois modos alternativos precisam lembrar algo entre uma resposta e a
     * seguinte, e o Elo nao guarda estado. Em vez de dar memoria a classe, o
     * chamador carrega esse unico numero e o devolve a cada chamada, o que
     * mantem a funcao pura e testavel.
     *
     * No modo PADRAO o estado e ignorado e o resultado e identico ao de
     * atualizar(), o que permite trocar de estimador sem ramificar o chamador.
     *
     * @param float $memoria media movel; sinal medio dos erros no modo
     *                       K_ADAPTATIVO, ajuste medio em pontos no modo TENDENCIA
     * @return array{0: float, 1: float} nova pontuacao e nova memoria
     */
    public static function atualizarComEstado(
        float $proficiencia,
        float $dificuldade,
        bool $acertou,
        int $praticas,
        string $modo,
        float $memoria
    ): array {
        $esperado  = self::probabilidadeAcerto($proficiencia, $dificuldade);
        $observado = $acertou ? 1.0 : 0.0;
        $erro      = $observado - $esperado;

        if ($modo === self::MODO_K_ADAPTATIVO) {
            // A memoria acompanha o SINAL do erro. Erros sempre do mesmo lado
            // levam |memoria| a 1 e devolvem ao K a amplitude inicial; erros
            // alternados levam a zero e o K fica no piso.
            $memoria = self::BETA * $memoria + (1.0 - self::BETA) * ($erro <=> 0.0);
            $k = self::K_MINIMO + (self::K_INICIAL - self::K_MINIMO) * abs($memoria);

            return [$proficiencia + $k * $erro, $memoria];
        }

        if ($modo === self::MODO_TENDENCIA) {
            $ajuste  = self::fatorK($praticas) * $erro;
            $memoria = self::BETA * $memoria + (1.0 - self::BETA) * $ajuste;

            return [$proficiencia + $ajuste + self::GAMA * $memoria, $memoria];
        }

        return [$proficiencia + self::fatorK($praticas) * $erro, $memoria];
    }
}
