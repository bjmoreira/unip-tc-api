<?php

namespace App\Recomendacao;

/**
 * Montagem da prova: define quantas questoes cada topico recebe.
 *
 * A primeira abordagem considerada foi deterministica: concentrar a prova no
 * topico de menor proficiencia. Ela foi descartada por produzir dois efeitos
 * indesejados. O primeiro e a fixacao, ja que o estudante receberia uma prova
 * inteira de uma unica area. O segundo e o esquecimento dos demais topicos,
 * que deixariam de ser praticados (Ebbinghaus, 1913).
 *
 * A solucao adotada distribui as questoes proporcionalmente a um peso composto
 * por dois fatores:
 *
 *   peso = fatorFraqueza * fatorEspacamento
 *
 * O primeiro privilegia os topicos de menor proficiencia. O segundo eleva o
 * peso dos topicos ha mais tempo sem praticar, de modo que areas ja dominadas
 * voltem a aparecer para revisao.
 *
 * O espacamento e medido em dias decorridos, e nao em numero de provas. Como o
 * historico do estudante persiste entre visitas, o intervalo real entre uma
 * pratica e outra e conhecido, e e esse intervalo que a curva do esquecimento
 * descreve. Contar provas mediria esforco, nao tempo.
 *
 * Esta classe nao depende do framework nem de banco de dados: recebe o estado
 * como array e devolve a decisao. Isso permite que o mesmo codigo seja usado
 * pela aplicacao e pelo ambiente de simulacao, garantindo que aquilo que e
 * avaliado no experimento seja exatamente o que atende ao usuario.
 */
class Recomendador
{
    /** Tamanhos de prova oferecidos ao estudante. */
    public const TAMANHOS = [10, 20, 30, 40, 50];

    /** Referencia de proficiencia usada para normalizar o fator de fraqueza. */
    private const REFERENCIA = 1400.0;

    /** Quanto maior, mais concentrada fica a prova nos topicos fracos. */
    private const INTENSIDADE = 3.0;

    /** Teto do fator de espacamento, para que a revisao nao domine a prova. */
    private const TETO_ESPACAMENTO = 2.5;

    /**
     * A cada quantos dias sem praticar o peso do topico cresce em uma unidade.
     * Com o teto acima, um topico atinge o peso maximo de revisao apos cerca de
     * dez dias sem aparecer.
     */
    private const JANELA_DIAS = 7.0;

    /**
     * Garante que nenhum topico desapareca por completo da prova otimizada:
     * mesmo os dominados mantem ao menos esta fracao do total, quando o
     * tamanho da prova permite.
     */
    private const PISO_COBERTURA = 1;

    /**
     * Peso de um topico na montagem da prova.
     *
     * @param float      $pontuacao        proficiencia estimada no topico
     * @param float|null $diasSemPraticar  dias desde a ultima pratica; nulo se nunca praticado
     */
    public static function peso(float $pontuacao, ?float $diasSemPraticar): float
    {
        $defasagem = max(0.0, self::REFERENCIA - $pontuacao) / self::REFERENCIA;
        $fatorFraqueza = 0.05 + $defasagem * self::INTENSIDADE;

        if ($diasSemPraticar === null) {
            $fatorEspacamento = self::TETO_ESPACAMENTO;
        } else {
            $fatorEspacamento = min(
                self::TETO_ESPACAMENTO,
                1.0 + (max(0.0, $diasSemPraticar) / self::JANELA_DIAS)
            );
        }

        return $fatorFraqueza * $fatorEspacamento;
    }

    /**
     * Distribui as questoes de uma prova otimizada entre os topicos.
     *
     * Usa o metodo dos maiores restos: aloca a parte inteira de cada cota e
     * distribui as vagas que sobram para os topicos de maior fracao residual.
     * Isso garante que a soma seja exatamente o tamanho pedido, sem o desvio
     * que o arredondamento simples produziria.
     *
     * @param array<int, array{topico_id:int, pontuacao:float, dias_sem_praticar:?float}> $estado
     * @return array<int,int> topico_id => quantidade de questoes
     */
    public static function distribuir(array $estado, int $tamanho): array
    {
        $pesos = [];
        $total = 0.0;

        foreach ($estado as $linha) {
            $p = self::peso($linha['pontuacao'], $linha['dias_sem_praticar']);
            $pesos[$linha['topico_id']] = $p;
            $total += $p;
        }

        if ($total <= 0) {
            return self::distribuirUniforme($estado, $tamanho);
        }

        // Reserva o piso de cobertura, quando a prova comporta.
        $qtdTopicos = count($pesos);
        $piso = ($tamanho >= $qtdTopicos * 2) ? self::PISO_COBERTURA : 0;
        $restante = $tamanho - ($piso * $qtdTopicos);

        $cotas = [];
        $inteiros = [];
        foreach ($pesos as $topicoId => $p) {
            $cota = ($p / $total) * $restante;
            $cotas[$topicoId] = $cota;
            $inteiros[$topicoId] = (int) floor($cota);
        }

        $alocado = array_sum($inteiros);
        $sobra = $restante - $alocado;

        // Maiores restos primeiro.
        $restos = [];
        foreach ($cotas as $topicoId => $cota) {
            $restos[$topicoId] = $cota - floor($cota);
        }
        arsort($restos);

        foreach (array_keys($restos) as $topicoId) {
            if ($sobra <= 0) {
                break;
            }
            $inteiros[$topicoId]++;
            $sobra--;
        }

        foreach ($inteiros as $topicoId => $q) {
            $inteiros[$topicoId] = $q + $piso;
        }

        return $inteiros;
    }

    /**
     * Distribuicao uniforme entre os topicos.
     *
     * Usada na prova diagnostica, quando ainda nao ha informacao sobre o
     * estudante, e como linha de base no experimento.
     *
     * @param array<int, array{topico_id:int}> $estado
     * @return array<int,int>
     */
    public static function distribuirUniforme(array $estado, int $tamanho): array
    {
        $ids = array_column($estado, 'topico_id');
        $n = count($ids);

        if ($n === 0) {
            return [];
        }

        $base = intdiv($tamanho, $n);
        $sobra = $tamanho % $n;

        $dist = [];
        foreach ($ids as $i => $topicoId) {
            $dist[$topicoId] = $base + ($i < $sobra ? 1 : 0);
        }

        return $dist;
    }

    /**
     * Nivel de dificuldade adequado a proficiencia atual.
     *
     * Mantem o estudante na faixa em que a probabilidade de acerto fica proxima
     * de 0.6 a 0.75, evitando tanto a trivialidade quanto a frustracao.
     */
    public static function nivelAlvo(float $pontuacao): string
    {
        if ($pontuacao < 1150) {
            return 'facil';
        }

        if ($pontuacao < 1350) {
            return 'medio';
        }

        return 'dificil';
    }
}
