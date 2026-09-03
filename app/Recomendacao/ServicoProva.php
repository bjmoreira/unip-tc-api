<?php

namespace App\Recomendacao;

use App\Models\Proficiencia;
use App\Models\Prova;
use App\Models\ProvaQuestao;
use App\Models\Questao;
use App\Models\Topico;
use App\Models\User;

/**
 * Ciclo de estudo por provas.
 *
 * O estudante responde uma prova fechada de tamanho definido. Ao concluir, o
 * sistema corrige, atualiza a estimativa de proficiencia de cada topico e passa
 * a dispor de informacao para montar a prova seguinte, agora com a distribuicao
 * de questoes ajustada ao seu desempenho.
 *
 * Todo esse estado pertence a conta do estudante e sobrevive ao encerramento da
 * sessao de navegacao. Ao voltar, ele retoma a numeracao das provas e as
 * proficiencias acumuladas, e o intervalo desde a ultima pratica passa a contar
 * a favor da revisao dos topicos que ficaram parados.
 *
 * A decisao algoritmica fica em Elo e Recomendador. Esta classe traduz entre o
 * banco de dados e essas duas.
 */
class ServicoProva
{
    /**
     * Por quantas provas uma questao ja respondida fica fora do sorteio.
     *
     * Com o historico permanente, a regra anterior, de nunca repetir uma
     * questao, esgotaria o banco. O reaproveitamento apos algumas provas e
     * tambem desejavel do ponto de vista pedagogico: rever um item ja
     * respondido faz parte da repeticao espacada.
     */
    private const JANELA_REPETICAO = 3;

    /** Cria as linhas de proficiencia que faltarem para o estudante. */
    public function garantirProficiencias(User $estudante): void
    {
        $existentes = $estudante->proficiencias()->pluck('topico_id')->all();

        foreach (Topico::pluck('id') as $topicoId) {
            if (in_array($topicoId, $existentes)) {
                continue;
            }

            Proficiencia::create([
                'user_id'   => $estudante->id,
                'topico_id' => $topicoId,
                'pontuacao' => Elo::PONTUACAO_INICIAL,
            ]);
        }
    }

    /** Estado das proficiencias no formato que o Recomendador espera. */
    public function estado(User $estudante): array
    {
        $agora = now();

        return $estudante->proficiencias()
            ->get()
            ->map(fn ($p) => [
                'topico_id'         => $p->topico_id,
                'pontuacao'         => (float) $p->pontuacao,
                'dias_sem_praticar' => $p->ultima_pratica
                    ? (float) $p->ultima_pratica->floatDiffInDays($agora)
                    : null,
            ])
            ->all();
    }

    /**
     * Monta a proxima prova.
     *
     * A primeira e diagnostica, com distribuicao uniforme entre os topicos,
     * porque ainda nao ha informacao sobre o estudante. As seguintes sao
     * otimizadas pelo desempenho acumulado.
     */
    public function gerarProva(User $estudante): Prova
    {
        $this->garantirProficiencias($estudante);

        $numero  = (int) $estudante->provas()->max('numero') + 1;
        $tipo    = $numero === 1 ? 'diagnostica' : 'otimizada';
        $tamanho = (int) $estudante->tamanho_prova;
        $estado  = $this->estado($estudante);

        $distribuicao = $tipo === 'diagnostica'
            ? Recomendador::distribuirUniforme($estado, $tamanho)
            : Recomendador::distribuir($estado, $tamanho);

        $prova = Prova::create([
            'user_id' => $estudante->id,
            'numero'  => $numero,
            'tipo'    => $tipo,
            'tamanho' => $tamanho,
            'status'  => 'em_andamento',
        ]);

        $usadas = $this->questoesRecentes($estudante, $numero);

        $ordem = 0;
        $selecionadas = [];

        foreach ($distribuicao as $topicoId => $quantidade) {
            if ($quantidade < 1) {
                continue;
            }

            $proficiencia = $estudante->proficiencias()->where('topico_id', $topicoId)->first();
            $nivelAlvo = $proficiencia
                ? Recomendador::nivelAlvo((float) $proficiencia->pontuacao)
                : 'medio';

            // Prioriza o nivel adequado a proficiencia; completa com outros
            // niveis quando nao ha questoes suficientes no nivel alvo.
            $doTopico = Questao::where('topico_id', $topicoId)
                ->whereNotIn('id', array_merge($usadas, $selecionadas))
                ->get()
                ->sortBy(fn ($q) => $q->nivel === $nivelAlvo ? 0 : 1)
                ->take($quantidade);

            foreach ($doTopico as $questao) {
                $selecionadas[] = $questao->id;
                ProvaQuestao::create([
                    'prova_id'   => $prova->id,
                    'questao_id' => $questao->id,
                    'ordem'      => ++$ordem,
                ]);
            }
        }

        // Se o banco nao tinha questoes suficientes em algum topico, completa
        // com qualquer questao disponivel, para respeitar o tamanho pedido.
        $faltam = $tamanho - $ordem;
        if ($faltam > 0) {
            $extras = Questao::whereNotIn('id', array_merge($usadas, $selecionadas))
                ->inRandomOrder()
                ->take($faltam)
                ->get();

            foreach ($extras as $questao) {
                $selecionadas[] = $questao->id;
                ProvaQuestao::create([
                    'prova_id'   => $prova->id,
                    'questao_id' => $questao->id,
                    'ordem'      => ++$ordem,
                ]);
            }
        }

        // Ultimo recurso: banco pequeno demais para o tamanho pedido sem
        // repeticao. Libera as questoes mais antigas para completar a prova.
        $faltam = $tamanho - $ordem;
        if ($faltam > 0) {
            $extras = Questao::whereNotIn('id', $selecionadas)
                ->inRandomOrder()
                ->take($faltam)
                ->get();

            foreach ($extras as $questao) {
                ProvaQuestao::create([
                    'prova_id'   => $prova->id,
                    'questao_id' => $questao->id,
                    'ordem'      => ++$ordem,
                ]);
            }
        }

        return $prova->fresh();
    }

    /** Questoes usadas nas ultimas provas, que ficam fora do sorteio. */
    private function questoesRecentes(User $estudante, int $numeroAtual): array
    {
        $recentes = $estudante->provas()
            ->where('numero', '>', $numeroAtual - 1 - self::JANELA_REPETICAO)
            ->pluck('id');

        return ProvaQuestao::whereIn('prova_id', $recentes)->pluck('questao_id')->all();
    }

    /** Questoes da prova, na ordem, sem revelar a alternativa correta. */
    public function questoesDaProva(Prova $prova): array
    {
        return $prova->itens()
            ->with('questao.alternativas', 'questao.topico.disciplina')
            ->orderBy('ordem')
            ->get()
            ->map(fn ($item) => [
                'ordem'        => $item->ordem,
                'questao_id'   => $item->questao_id,
                'enunciado'    => $item->questao->enunciado,
                'nivel'        => $item->questao->nivel,
                'topico'       => $item->questao->topico->nome,
                'disciplina'   => $item->questao->topico->disciplina->nome,
                // A alternativa marcada volta para que uma prova retomada
                // reapareca como o estudante a deixou. Nao revela o gabarito:
                // e apenas o que ele proprio escolheu.
                'alternativa_id' => $item->alternativa_id,
                'alternativas' => $item->questao->alternativas
                    ->sortBy('letra')
                    ->map(fn ($a) => ['id' => $a->id, 'letra' => $a->letra, 'texto' => $a->texto])
                    ->values(),
            ])
            ->all();
    }

    /** Grava a resposta de uma questao, sem corrigir ainda. */
    public function registrarResposta(Prova $prova, int $questaoId, ?int $alternativaId): void
    {
        $item = $prova->itens()->where('questao_id', $questaoId)->firstOrFail();
        $item->update(['alternativa_id' => $alternativaId]);
    }

    /**
     * Corrige a prova e atualiza a proficiencia de cada topico.
     *
     * A correcao acontece apenas ao final: durante a prova o estudante nao
     * recebe retorno, o que reproduz a situacao de um simulado real e evita
     * que ele infira o gabarito pela mudanca da recomendacao.
     */
    public function corrigir(Prova $prova): array
    {
        if ($prova->status === 'concluida') {
            return $this->resultado($prova);
        }

        $estudante = $prova->estudante;
        $agora = now();
        $acertos = 0;

        $itens = $prova->itens()->with('questao.alternativas')->orderBy('ordem')->get();

        foreach ($itens as $item) {
            $alternativa = $item->alternativa_id
                ? $item->questao->alternativas->firstWhere('id', $item->alternativa_id)
                : null;

            $acertou = (bool) ($alternativa?->correta);
            $item->update(['correta' => $acertou]);

            if ($acertou) {
                $acertos++;
            }

            $proficiencia = $estudante->proficiencias()
                ->where('topico_id', $item->questao->topico_id)
                ->first();

            if (! $proficiencia) {
                continue;
            }

            $praticas = $proficiencia->acertos + $proficiencia->erros;

            $proficiencia->update([
                'pontuacao'      => Elo::atualizar(
                    (float) $proficiencia->pontuacao,
                    (float) $item->questao->dificuldade,
                    $acertou,
                    $praticas
                ),
                'ultima_pratica' => $agora,
                'acertos'        => $proficiencia->acertos + ($acertou ? 1 : 0),
                'erros'          => $proficiencia->erros + ($acertou ? 0 : 1),
            ]);
        }

        $prova->update([
            'status'       => 'concluida',
            'acertos'      => $acertos,
            'concluida_em' => $agora,
        ]);

        return $this->resultado($prova->fresh());
    }

    /** Resultado da prova, com o desempenho detalhado por topico. */
    public function resultado(Prova $prova): array
    {
        $itens = $prova->itens()->with('questao.topico.disciplina')->get();

        $porTopico = [];
        foreach ($itens as $item) {
            $nome = $item->questao->topico->nome;
            $porTopico[$nome] ??= [
                'topico'     => $nome,
                'disciplina' => $item->questao->topico->disciplina->nome,
                'questoes'   => 0,
                'acertos'    => 0,
                'erros'      => 0,
            ];
            $porTopico[$nome]['questoes']++;
            $item->correta ? $porTopico[$nome]['acertos']++ : $porTopico[$nome]['erros']++;
        }

        foreach ($porTopico as &$linha) {
            $linha['aproveitamento'] = $linha['questoes'] > 0
                ? round(100 * $linha['acertos'] / $linha['questoes'])
                : 0;
        }
        unset($linha);

        // Pior desempenho primeiro. Em caso de empate, o topico com mais erros
        // absolutos aparece na frente, por ser o de maior evidencia acumulada.
        usort($porTopico, fn ($a, $b) => [$a['aproveitamento'], -$a['erros']]
            <=> [$b['aproveitamento'], -$b['erros']]);

        return [
            'numero'         => $prova->numero,
            'tipo'           => $prova->tipo,
            'tamanho'        => $prova->tamanho,
            'acertos'        => $prova->acertos,
            'aproveitamento' => $prova->tamanho > 0
                ? round(100 * $prova->acertos / $prova->tamanho)
                : 0,
            'por_topico'     => array_values($porTopico),
        ];
    }

    /** Estado de conhecimento acumulado do estudante. */
    public function painel(User $estudante): array
    {
        $agora = now();

        return $estudante->proficiencias()
            ->with('topico.disciplina')
            ->get()
            ->sortByDesc('pontuacao')
            ->values()
            ->map(function ($p) use ($agora) {
                $dias = $p->ultima_pratica
                    ? (float) $p->ultima_pratica->floatDiffInDays($agora)
                    : null;

                return [
                    'topico'            => $p->topico->nome,
                    'disciplina'        => $p->topico->disciplina->nome,
                    'pontuacao'         => round((float) $p->pontuacao, 1),
                    'acertos'           => $p->acertos,
                    'erros'             => $p->erros,
                    'nivel_alvo'        => Recomendador::nivelAlvo((float) $p->pontuacao),
                    'dias_sem_praticar' => $dias === null ? null : round($dias, 1),
                    'peso'              => round(Recomendador::peso((float) $p->pontuacao, $dias), 3),
                ];
            })
            ->all();
    }

    /**
     * Quantas questoes cada topico terá na proxima prova.
     * Permite ao estudante ver o efeito do seu desempenho antes de comecar.
     */
    public function previsaoProximaProva(User $estudante): array
    {
        $numero = (int) $estudante->provas()->max('numero') + 1;
        $estado = $this->estado($estudante);
        $tamanho = (int) $estudante->tamanho_prova;

        $dist = $numero === 1
            ? Recomendador::distribuirUniforme($estado, $tamanho)
            : Recomendador::distribuir($estado, $tamanho);

        $nomes = Topico::whereIn('id', array_keys($dist))->pluck('nome', 'id');

        $saida = [];
        foreach ($dist as $topicoId => $qtd) {
            $saida[] = ['topico' => $nomes[$topicoId] ?? '?', 'questoes' => $qtd];
        }

        usort($saida, fn ($a, $b) => $b['questoes'] <=> $a['questoes']);

        return $saida;
    }
}
