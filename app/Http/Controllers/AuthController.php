<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Recomendacao\Recomendador;
use App\Recomendacao\ServicoProva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Identificacao do estudante.
 *
 * A conta existe para que o modelo de conhecimento persista entre visitas. Sem
 * ela, o algoritmo recomecaria a estimativa de proficiencia a cada retorno, e o
 * fator de espacamento, que depende do intervalo real entre praticas, nao teria
 * como ser calculado.
 */
class AuthController extends Controller
{
    /** Conta usada pelo botao de demonstracao. */
    private const EMAIL_DEMONSTRACAO = 'demonstracao@exemplo.com';

    public function __construct(private ServicoProva $servico) {}

    /** POST /api/cadastro */
    public function cadastrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name'          => 'required|string|max:120',
            'email'         => 'required|email|max:180|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'tamanho_prova' => 'required|integer|in:' . implode(',', Recomendador::TAMANHOS),
        ]);

        $estudante = User::create($dados);

        // O modelo de conhecimento nasce junto com a conta, com todos os
        // topicos na pontuacao inicial.
        $this->servico->garantirProficiencias($estudante);

        return response()->json([
            'token'     => $estudante->createToken('estudo')->plainTextToken,
            'estudante' => $this->perfil($estudante),
        ], 201);
    }

    /** POST /api/login */
    public function entrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $estudante = User::where('email', $dados['email'])->first();

        if (! $estudante || ! Hash::check($dados['password'], $estudante->password)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        // Topicos criados depois do cadastro entram aqui, na pontuacao inicial.
        $this->servico->garantirProficiencias($estudante);

        return response()->json([
            'token'     => $estudante->createToken('estudo')->plainTextToken,
            'estudante' => $this->perfil($estudante),
        ]);
    }

    /**
     * POST /api/demonstracao
     *
     * Conta fixa para demonstrar o sistema sem exigir cadastro. Existe para a
     * apresentacao do trabalho: permite mostrar o ciclo completo em um clique.
     * O historico dela e compartilhado por quem usar o botao, e por isso ela
     * nao serve para medir desempenho individual.
     */
    public function demonstracao(): JsonResponse
    {
        $estudante = User::firstOrCreate(
            ['email' => self::EMAIL_DEMONSTRACAO],
            [
                'name'          => 'Estudante de demonstração',
                'password'      => 'demonstracao',
                'tamanho_prova' => 20,
            ]
        );

        $this->servico->garantirProficiencias($estudante);

        return response()->json([
            'token'     => $estudante->createToken('demonstracao')->plainTextToken,
            'estudante' => $this->perfil($estudante),
        ]);
    }

    /** POST /api/logout */
    public function sair(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }

    /** GET /api/eu */
    public function eu(Request $request): JsonResponse
    {
        return response()->json(['estudante' => $this->perfil($request->user())]);
    }

    /** PATCH /api/eu */
    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'tamanho_prova' => 'required|integer|in:' . implode(',', Recomendador::TAMANHOS),
        ]);

        $estudante = $request->user();
        $estudante->update($dados);

        return response()->json(['estudante' => $this->perfil($estudante)]);
    }

    private function perfil(User $estudante): array
    {
        return [
            'nome'           => $estudante->name,
            'email'          => $estudante->email,
            'tamanho_prova'  => (int) $estudante->tamanho_prova,
            'provas_feitas'  => $estudante->provas()->where('status', 'concluida')->count(),
            'estuda_desde'   => $estudante->created_at?->toDateString(),
        ];
    }
}
