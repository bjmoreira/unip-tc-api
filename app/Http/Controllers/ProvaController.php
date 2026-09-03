<?php

namespace App\Http\Controllers;

use App\Models\Prova;
use App\Recomendacao\Recomendador;
use App\Recomendacao\ServicoProva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProvaController extends Controller
{
    public function __construct(private ServicoProva $servico) {}

    /** GET /api/tamanhos */
    public function tamanhos(): JsonResponse
    {
        return response()->json(['tamanhos' => Recomendador::TAMANHOS]);
    }

    /** POST /api/provas */
    public function gerar(Request $request): JsonResponse
    {
        $estudante = $request->user();

        $emAberto = $estudante->provas()->where('status', 'em_andamento')->first();
        $prova = $emAberto ?: $this->servico->gerarProva($estudante);

        return response()->json([
            'prova' => [
                'id'      => $prova->id,
                'numero'  => $prova->numero,
                'tipo'    => $prova->tipo,
                'tamanho' => $prova->tamanho,
                'status'  => $prova->status,
            ],
            'questoes' => $this->servico->questoesDaProva($prova),
        ], 201);
    }

    /** POST /api/provas/{prova}/respostas */
    public function responder(Request $request, Prova $prova): JsonResponse
    {
        $this->autorizar($request, $prova);

        if ($prova->status === 'concluida') {
            return response()->json(['erro' => 'Esta prova já foi finalizada.'], 422);
        }

        $dados = $request->validate([
            'questao_id'     => 'required|integer|exists:questoes,id',
            'alternativa_id' => 'nullable|integer|exists:alternativas,id',
        ]);

        $this->servico->registrarResposta($prova, $dados['questao_id'], $dados['alternativa_id'] ?? null);

        $respondidas = $prova->itens()->whereNotNull('alternativa_id')->count();

        return response()->json([
            'respondidas' => $respondidas,
            'total'       => $prova->tamanho,
        ]);
    }

    /** POST /api/provas/{prova}/finalizar */
    public function finalizar(Request $request, Prova $prova): JsonResponse
    {
        $this->autorizar($request, $prova);

        $resultado = $this->servico->corrigir($prova);
        $estudante = $request->user()->fresh();

        return response()->json([
            'resultado' => $resultado,
            'painel'    => $this->servico->painel($estudante),
            'proxima'   => $this->servico->previsaoProximaProva($estudante),
        ]);
    }

    /** GET /api/historico */
    public function historico(Request $request): JsonResponse
    {
        $estudante = $request->user();

        $provas = $estudante->provas()
            ->where('status', 'concluida')
            ->orderBy('numero')
            ->get()
            ->map(fn ($p) => [
                'numero'         => $p->numero,
                'tipo'           => $p->tipo,
                'tamanho'        => $p->tamanho,
                'acertos'        => $p->acertos,
                'aproveitamento' => $p->tamanho > 0 ? round(100 * $p->acertos / $p->tamanho) : 0,
                'concluida_em'   => $p->concluida_em?->toDateString(),
            ]);

        // Uma prova iniciada e nao entregue continua valendo: e ela que sera
        // devolvida no proximo POST /api/provas. O front precisa saber disso
        // para nao anunciar uma prova nova que nao sera a entregue.
        $emAberto = $estudante->provas()->where('status', 'em_andamento')->first();

        return response()->json([
            'tamanho_prova'     => (int) $estudante->tamanho_prova,
            'provas'            => $provas,
            'painel'            => $this->servico->painel($estudante),
            'proxima'           => $this->servico->previsaoProximaProva($estudante),
            'prova_em_andamento' => $emAberto ? [
                'numero'      => $emAberto->numero,
                'tipo'        => $emAberto->tipo,
                'tamanho'     => $emAberto->tamanho,
                'respondidas' => $emAberto->itens()->whereNotNull('alternativa_id')->count(),
            ] : null,
        ]);
    }

    /** Uma prova só pode ser manipulada pelo estudante a quem pertence. */
    private function autorizar(Request $request, Prova $prova): void
    {
        if ($prova->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('Esta prova não pertence a você.');
        }
    }
}
