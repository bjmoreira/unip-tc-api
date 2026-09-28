<?php

namespace App\Simulacao;

use App\Recomendacao\Recomendador;

/**
 * Um braco do experimento: roda a mesma populacao de agentes sob uma estrategia
 * de distribuicao de questoes e devolve as metricas prova a prova.
 *
 * A decisao algoritmica nao e reimplementada aqui. Quem escolhe quantas questoes
 * cada topico recebe e Recomendador::distribuir ou Recomendador::distribuirUniforme,
 * quem escolhe o nivel de dificuldade e Recomendador::nivelAlvo e quem atualiza a
 * estimativa e Elo::atualizar, pela mao do Agente. Sao as mesmas classes que a
 * API executa para o estudante real. Se a logica fosse copiada para o ambiente de
 * simulacao, o experimento avaliaria uma copia, e nada garantiria que a copia e o
 * original continuassem iguais.
 *
 * O que esta classe substitui e apenas a camada de persistencia. ServicoProva faz
 * o mesmo ciclo sobre o banco de dados, com uma escrita por questao respondida;
 * nesta escala, centenas de milhares de provas, isso seria inviavel em tempo. As
 * questoes ficam em arrays de contagem por nivel, carregados do banco real.
 *
 * Uma simplificacao usada nessa substituicao merece registro: no banco atual a
 * dificuldade de uma questao e funcao apenas do seu nivel (facil 1050, medio
 * 1250, dificil 1450). Para o modelo de resposta, portanto, a identidade da
 * questao dentro do nivel e indiferente, e o que importa e quantas questoes de
 * cada nivel o topico tem disponiveis. E por isso que a janela de repeticao aqui
 * desconta contagens por nivel em vez de guardar identificadores.
 */
final class Experimento
{
    /**
     * Por quantas provas uma questao respondida fica fora do sorteio.
     * Espelha a mesma constante de ServicoProva.
     */
    private const JANELA_REPETICAO = 3;

    /** Ordem em que os niveis sao consultados quando o nivel alvo se esgota. */
    private const NIVEIS = ['facil', 'medio', 'dificil'];

    /**
     * @param array<int, array<string, array{dificuldade: float, total: int}>> $banco
     */
    public function __construct(
        private readonly array $banco,
        private readonly int $tamanho,
        private readonly int $provas,
        private readonly int $semente,
    ) {}

    /**
     * Roda o braco sobre os agentes dados.
     *
     * @param array<int, Agente> $agentes
     * @param bool $adaptativo false usa a distribuicao uniforme em todas as provas
     * @return array{provas: array<int, array<string, float>>, agentes: array<int, array<string, float>>}
     */
    public function rodar(array $agentes, bool $adaptativo): array
    {
        $soma = [];
        for ($p = 1; $p <= $this->provas; $p++) {
            $soma[$p] = [
                'aproveitamento'   => 0.0,
                'real_fracos'      => 0.0,
                'real_geral'       => 0.0,
                'erro'             => 0.0,
                'vies'             => 0.0,
                'fracos_patamar'   => 0.0,
                'pares_fracos'     => 0.0,
                'questoes_fracos'  => 0.0,
            ];
        }

        $porAgente = [];

        foreach ($agentes as $agente) {
            $agente->reiniciar();

            // Consumo das ultimas provas, por topico e nivel, para aplicar a
            // janela de repeticao. A fila guarda uma entrada por prova recente.
            $recentes = [];

            for ($numero = 1; $numero <= $this->provas; $numero++) {
                $agente->relogio += $agente->intervalos[$numero - 1];

                $estado = $agente->estado();

                // A primeira prova e diagnostica nos dois bracos, como na
                // aplicacao: sem nenhuma resposta observada, nao existe
                // informacao para o recomendador usar.
                $distribuicao = ($adaptativo && $numero > 1)
                    ? Recomendador::distribuir($estado, $this->tamanho)
                    : Recomendador::distribuirUniforme($estado, $this->tamanho);

                $bloqueadas = $this->somarRecentes($recentes);
                $consumo = [];

                // Numeros aleatorios comuns: a semente depende do agente e do
                // numero da prova, nunca da ordem em que os sorteios acontecem.
                // Assim a questao na posicao k da prova p de um agente recebe o
                // mesmo numero nos dois bracos, o que reduz a variancia da
                // comparacao sem introduzir vies.
                $sorteio = new Aleatorio(
                    $this->semente
                    ^ (($agente->indice + 1) * 2654435761)
                    ^ (($numero + 1) * 2246822519)
                );

                $acertos = 0;
                $respondidas = 0;
                $questoesNosFracos = 0;

                foreach ($distribuicao as $topicoId => $quantidade) {
                    if ($quantidade < 1) {
                        continue;
                    }

                    $nivelAlvo = Recomendador::nivelAlvo($agente->estimado[$topicoId]);
                    $itens = $this->selecionar($topicoId, $quantidade, $nivelAlvo, $bloqueadas[$topicoId] ?? []);

                    foreach ($itens as $nivel => $qtd) {
                        $dificuldade = $this->banco[$topicoId][$nivel]['dificuldade'];
                        $consumo[$topicoId][$nivel] = ($consumo[$topicoId][$nivel] ?? 0) + $qtd;

                        for ($i = 0; $i < $qtd; $i++) {
                            $acertou = $agente->responder($topicoId, $dificuldade, $sorteio);
                            $agente->registrar($topicoId, $dificuldade, $acertou);

                            $respondidas++;
                            if ($acertou) {
                                $acertos++;
                            }
                        }
                    }

                    $agente->ultimaPratica[$topicoId] = $agente->relogio;

                    if (in_array($topicoId, $agente->fracosIniciais, true)) {
                        $questoesNosFracos += array_sum($itens);
                    }
                }

                $recentes[] = $consumo;
                if (count($recentes) > self::JANELA_REPETICAO) {
                    array_shift($recentes);
                }

                if ($agente->provaDoPatamar === null && $agente->realNosFracos() >= Agente::PATAMAR) {
                    $agente->provaDoPatamar = $numero;
                }

                $soma[$numero]['aproveitamento']  += $respondidas > 0 ? 100.0 * $acertos / $respondidas : 0.0;
                $soma[$numero]['real_fracos']     += $agente->realNosFracos();
                $soma[$numero]['real_geral']      += $agente->realGeral();
                $soma[$numero]['erro']            += $agente->erroEstimativa();
                $soma[$numero]['vies']            += $agente->viesEstimativa();
                $soma[$numero]['fracos_patamar']  += $agente->fracosNoPatamar();
                $soma[$numero]['pares_fracos']    += count($agente->fracosIniciais);
                $soma[$numero]['questoes_fracos'] += $questoesNosFracos;
            }

            $porAgente[] = [
                'agente'            => $agente->indice,
                'topicos_fracos'    => count($agente->fracosIniciais),
                'real_fracos_inicio'=> $agente->realInicialNosFracos(),
                'real_fracos_fim'   => $agente->realNosFracos(),
                'real_geral_fim'    => $agente->realGeral(),
                'erro_fim'          => $agente->erroEstimativa(),
                'vies_fim'          => $agente->viesEstimativa(),
                'prova_do_patamar'  => $agente->provaDoPatamar,
            ];
        }

        $n = count($agentes);
        $linhas = [];

        foreach ($soma as $numero => $s) {
            $linhas[] = [
                'prova'                => $numero,
                'aproveitamento'       => $s['aproveitamento'] / $n,
                'real_fracos'          => $s['real_fracos'] / $n,
                'real_geral'           => $s['real_geral'] / $n,
                'erro'                 => $s['erro'] / $n,
                'vies'                 => $s['vies'] / $n,
                'pct_fracos_patamar'   => $s['pares_fracos'] > 0
                    ? 100.0 * $s['fracos_patamar'] / $s['pares_fracos']
                    : 0.0,
                'questoes_fracos'      => $s['questoes_fracos'] / $n,
            ];
        }

        return ['provas' => $linhas, 'agentes' => $porAgente];
    }

    /**
     * Soma o consumo das provas ainda dentro da janela de repeticao.
     *
     * @param array<int, array<int, array<string, int>>> $recentes
     * @return array<int, array<string, int>>
     */
    private function somarRecentes(array $recentes): array
    {
        $total = [];

        foreach ($recentes as $prova) {
            foreach ($prova as $topicoId => $porNivel) {
                foreach ($porNivel as $nivel => $qtd) {
                    $total[$topicoId][$nivel] = ($total[$topicoId][$nivel] ?? 0) + $qtd;
                }
            }
        }

        return $total;
    }

    /**
     * Quantas questoes de cada nivel o topico entrega para esta prova.
     *
     * Espelha a regra de ServicoProva: prioriza o nivel adequado a proficiencia
     * estimada e recorre aos outros niveis quando o alvo nao tem questoes
     * suficientes fora da janela de repeticao.
     *
     * @param array<string, int> $bloqueadas questoes ja usadas, por nivel
     * @return array<string, int> nivel => quantidade
     */
    private function selecionar(int $topicoId, int $quantidade, string $nivelAlvo, array $bloqueadas): array
    {
        $ordem = array_merge([$nivelAlvo], array_diff(self::NIVEIS, [$nivelAlvo]));
        $escolhido = [];
        $faltam = $quantidade;

        foreach ($ordem as $nivel) {
            if ($faltam <= 0) {
                break;
            }

            $disponivel = ($this->banco[$topicoId][$nivel]['total'] ?? 0) - ($bloqueadas[$nivel] ?? 0);
            $pega = max(0, min($faltam, $disponivel));

            if ($pega > 0) {
                $escolhido[$nivel] = $pega;
                $faltam -= $pega;
            }
        }

        // Ultimo recurso, como na aplicacao: banco pequeno demais para o tamanho
        // pedido sem repetir. Libera a janela e completa pelo nivel alvo.
        if ($faltam > 0) {
            $teto = $this->banco[$topicoId][$nivelAlvo]['total'] ?? 0;
            $escolhido[$nivelAlvo] = ($escolhido[$nivelAlvo] ?? 0) + min($faltam, $teto);
        }

        return $escolhido;
    }
}
