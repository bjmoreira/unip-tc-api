<?php

namespace App\Simulacao;

use App\Recomendacao\Elo;

/**
 * Estudante virtual.
 *
 * O agente tem uma proficiencia REAL por topico, que o algoritmo nao ve. O
 * algoritmo dispoe apenas da estimativa que o Elo constroi a partir dos acertos
 * e erros observados. E essa separacao que da sentido ao experimento: com o
 * perfil verdadeiro conhecido, e possivel medir tanto o ganho de aprendizagem
 * quanto o erro da propria estimativa, duas coisas que nao se pode medir com
 * estudantes reais.
 *
 * PREMISSAS DO MODELO
 *
 * As conclusoes do experimento valem na medida em que estas premissas valem, e
 * por isso ficam declaradas aqui, e nao escondidas no meio do codigo.
 *
 * 1. Probabilidade de acerto. Sai da mesma curva logistica que o Elo usa para
 *    prever o acerto (Elo::probabilidadeAcerto), aplicada a proficiencia REAL
 *    em vez da estimada. A premissa e que o Elo esta certo sobre a FORMA da
 *    relacao entre habilidade e dificuldade, e apenas nao conhece o valor da
 *    habilidade. E a hipotese mais favoravel ao algoritmo, e portanto a que
 *    isola o efeito que se quer medir: o da distribuicao das questoes.
 *
 * 2. Aprendizagem. A proficiencia real sobe a cada questao praticada do topico,
 *    com ganho decrescente:
 *
 *        ganho = GANHO_BASE / (1 + DECAIMENTO * praticas_no_topico)
 *
 *    O ganho decrescente reproduz a curva de aprendizagem classica: as
 *    primeiras praticas de um assunto rendem muito mais que as seguintes.
 *
 * 3. O ganho NAO depende de o agente ter acertado, nem de a dificuldade da
 *    questao ser adequada ao seu nivel. Essa escolha e deliberadamente
 *    conservadora. Fazer o ganho depender do ajuste entre dificuldade e
 *    habilidade embutiria no modelo do estudante a propria tese que se quer
 *    testar, e o experimento passaria a confirmar a hipotese por construcao.
 *    Como esta, a unica vantagem que o braco adaptativo pode obter vem de onde
 *    ele aloca as questoes, e nao de um bonus concedido pelo modelo.
 *
 * 4. Nao ha esquecimento. A proficiencia real nunca cai. Essa e a premissa mais
 *    restritiva das quatro, e trabalha CONTRA a hipotese: o fator de
 *    espacamento do recomendador existe justamente para combater o
 *    esquecimento, e num mundo sem esquecimento ele so consome vagas de prova
 *    sem produzir beneficio. O ganho que o braco adaptativo mostrar aqui e,
 *    portanto, um piso.
 */
final class Agente
{
    /**
     * Ganho de proficiencia real, em pontos da escala, na primeira pratica de um
     * topico. E o parametro mais discutivel do modelo, por isso o comando expoe
     * uma opcao para variar: a conclusao do experimento so vale se sobreviver a
     * valores diferentes deste.
     */
    public const GANHO_BASE = 6.0;

    /** Quanto o ganho encolhe a cada pratica acumulada no topico. */
    private const DECAIMENTO = 0.05;

    /**
     * Abaixo deste valor o topico conta como fraco no inicio do experimento.
     *
     * O valor fica entre a dificuldade das questoes faceis (1050) e a das
     * medias (1250): um agente abaixo dele erra a maioria das questoes de nivel
     * medio do topico.
     */
    public const LIMIAR_FRACO = 1150.0;

    /**
     * Patamar que um topico fraco precisa cruzar para contar como recuperado.
     *
     * E a pontuacao que a aplicacao atribui a um topico sobre o qual ainda nao
     * sabe nada (Elo::PONTUACAO_INICIAL). Cruza-la significa que o topico deixou
     * de ser um deficit em relacao ao ponto de referencia da escala, com folga
     * sobre o limiar de fraqueza.
     *
     * O valor foi escolhido por ser um marco ja existente no modelo, e nao por
     * produzir a comparacao mais favoravel: a coluna prova_do_patamar do CSV por
     * agente permite recalcular a metrica para qualquer outro patamar.
     */
    public const PATAMAR = 1200.0;

    /**
     * Perfis heterogeneos: cada agente recebe estes deslocamentos sobre a sua
     * proficiencia de base, em ordem embaralhada entre os topicos. Assim todos
     * os agentes tem areas fortes e fracas, mas cada um as tem em topicos
     * diferentes. Sortear cada topico de forma independente daria agentes
     * uniformemente medianos com frequencia alta, e o recomendador nao teria o
     * que diferenciar.
     */
    private const DESLOCAMENTOS = [220.0, 150.0, 70.0, 20.0, 0.0, -30.0, -90.0, -160.0, -230.0];

    /** Limites da escala, para que nenhum agente fique fora da faixa das questoes. */
    private const MINIMO = 900.0;
    private const MAXIMO = 1520.0;

    /** @var array<int, float> proficiencia real inicial por topico */
    private array $realInicial = [];

    /** @var array<int, float> proficiencia real corrente por topico */
    public array $real = [];

    /** @var array<int, float> estimativa do Elo por topico */
    public array $estimado = [];

    /** @var array<int, int> questoes praticadas por topico */
    public array $praticas = [];

    /** @var array<int, float|null> dia da ultima pratica; nulo se o topico nunca apareceu */
    public array $ultimaPratica = [];

    /** @var array<int, int> topicos que comecaram abaixo do limiar de fraqueza */
    public array $fracosIniciais = [];

    /** @var array<int, int> intervalo em dias antes de cada prova */
    public array $intervalos = [];

    /** Dia corrente do calendario do agente. */
    public float $relogio = 0.0;

    /** Numero da prova em que os topicos fracos cruzaram o patamar; nulo se nao cruzaram. */
    public ?int $provaDoPatamar = null;

    /**
     * @param array<int, int> $topicos ids dos topicos do banco
     */
    public function __construct(
        public readonly int $indice,
        array $topicos,
        int $provas,
        Aleatorio $sorteio,
        private readonly float $ganhoBase = self::GANHO_BASE,
    ) {
        $base = $sorteio->normal(1200.0, 90.0);
        $deslocamentos = $sorteio->embaralhar(self::DESLOCAMENTOS);

        foreach (array_values($topicos) as $i => $topicoId) {
            $valor = $base + ($deslocamentos[$i % count($deslocamentos)] ?? 0.0);
            $this->realInicial[$topicoId] = min(self::MAXIMO, max(self::MINIMO, $valor));
        }

        foreach ($this->realInicial as $topicoId => $valor) {
            if ($valor < self::LIMIAR_FRACO) {
                $this->fracosIniciais[] = $topicoId;
            }
        }

        // Um agente forte em todos os topicos nao tem area fraca e sairia da
        // metrica de ganho, o que deixaria a media do experimento apoiada so
        // nos perfis ruins. Nesse caso conta como fraco o seu pior topico.
        if ($this->fracosIniciais === []) {
            $pior = array_keys($this->realInicial, min($this->realInicial), true)[0];
            $this->fracosIniciais[] = $pior;
        }

        // O calendario e sorteado uma vez e vale para os dois bracos: o fator de
        // espacamento so pode ser comparado se os dois enfrentarem os mesmos
        // intervalos entre provas.
        for ($p = 0; $p < $provas; $p++) {
            $this->intervalos[] = $p === 0 ? 0 : $sorteio->inteiro(1, 4);
        }

        $this->reiniciar();
    }

    /**
     * Devolve o agente ao estado inicial.
     *
     * Os dois bracos precisam partir da mesma populacao. Reiniciar o mesmo
     * objeto, em vez de sortear agentes novos, garante que a diferenca medida
     * entre os bracos venha da estrategia de distribuicao e nao da amostra.
     */
    public function reiniciar(): void
    {
        $this->real = $this->realInicial;
        $this->relogio = 0.0;
        $this->provaDoPatamar = null;

        foreach (array_keys($this->realInicial) as $topicoId) {
            $this->estimado[$topicoId] = Elo::PONTUACAO_INICIAL;
            $this->praticas[$topicoId] = 0;
            $this->ultimaPratica[$topicoId] = null;
        }
    }

    /**
     * Estado no formato que o Recomendador espera.
     *
     * O que vai aqui e a pontuacao ESTIMADA. O algoritmo nunca ve a real.
     *
     * @return array<int, array{topico_id:int, pontuacao:float, dias_sem_praticar:?float}>
     */
    public function estado(): array
    {
        $linhas = [];

        foreach ($this->estimado as $topicoId => $pontuacao) {
            $ultima = $this->ultimaPratica[$topicoId];

            $linhas[] = [
                'topico_id'         => $topicoId,
                'pontuacao'         => $pontuacao,
                'dias_sem_praticar' => $ultima === null ? null : $this->relogio - $ultima,
            ];
        }

        return $linhas;
    }

    /** Sorteia o resultado de uma questao a partir da proficiencia real. */
    public function responder(int $topicoId, float $dificuldade, Aleatorio $sorteio): bool
    {
        return $sorteio->real() < Elo::probabilidadeAcerto($this->real[$topicoId], $dificuldade);
    }

    /** Atualiza estimativa e proficiencia real depois de uma questao respondida. */
    public function registrar(int $topicoId, float $dificuldade, bool $acertou): void
    {
        $this->estimado[$topicoId] = Elo::atualizar(
            $this->estimado[$topicoId],
            $dificuldade,
            $acertou,
            $this->praticas[$topicoId]
        );

        $this->real[$topicoId] += $this->ganhoBase / (1.0 + self::DECAIMENTO * $this->praticas[$topicoId]);
        $this->praticas[$topicoId]++;
    }

    /** Proficiencia real media nos topicos que comecaram fracos. */
    public function realNosFracos(): float
    {
        $soma = 0.0;

        foreach ($this->fracosIniciais as $topicoId) {
            $soma += $this->real[$topicoId];
        }

        return $soma / count($this->fracosIniciais);
    }

    /** Proficiencia real inicial media nos topicos que comecaram fracos. */
    public function realInicialNosFracos(): float
    {
        $soma = 0.0;

        foreach ($this->fracosIniciais as $topicoId) {
            $soma += $this->realInicial[$topicoId];
        }

        return $soma / count($this->fracosIniciais);
    }

    /** Proficiencia real media em todos os topicos. */
    public function realGeral(): float
    {
        return array_sum($this->real) / count($this->real);
    }

    /**
     * Erro absoluto medio da estimativa.
     *
     * Esta metrica so existe porque o perfil real e conhecido, o que nunca
     * acontece em campo. Mede se o modelo do estudante esta convergindo para a
     * habilidade verdadeira ou apenas oscilando em torno do valor inicial.
     */
    public function erroEstimativa(): float
    {
        $soma = 0.0;

        foreach ($this->real as $topicoId => $valor) {
            $soma += abs($this->estimado[$topicoId] - $valor);
        }

        return $soma / count($this->real);
    }

    /**
     * Erro medio com sinal: estimativa menos proficiencia real.
     *
     * Separar o sinal do valor absoluto responde a uma pergunta que a media
     * absoluta esconde: se o Elo erra por subestimar ou por superestimar. Como a
     * proficiencia real sobe enquanto o estudante pratica, espera-se um vies
     * negativo, de um estimador que persegue um alvo em movimento.
     */
    public function viesEstimativa(): float
    {
        $soma = 0.0;

        foreach ($this->real as $topicoId => $valor) {
            $soma += $this->estimado[$topicoId] - $valor;
        }

        return $soma / count($this->real);
    }

    /** Quantos dos topicos inicialmente fracos ja cruzaram o patamar. */
    public function fracosNoPatamar(): int
    {
        $n = 0;

        foreach ($this->fracosIniciais as $topicoId) {
            if ($this->real[$topicoId] >= self::PATAMAR) {
                $n++;
            }
        }

        return $n;
    }
}
