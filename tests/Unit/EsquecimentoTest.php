<?php

namespace Tests\Unit;

use App\Simulacao\Agente;
use App\Simulacao\Aleatorio;
use PHPUnit\Framework\TestCase;

/**
 * Garantias sobre o esquecimento do estudante virtual.
 *
 * O cenario com esquecimento e o segundo dos dois que o experimento compara, e
 * so tem valor se o decaimento fizer exatamente o que a premissa 4 declara. O
 * que se protege aqui nao e o resultado do experimento, e sim a honestidade do
 * modelo: que o cenario antigo continue existindo intacto, que nada decaia
 * abaixo do ponto de partida e que chamar o metodo a cada prova nao conte o
 * mesmo intervalo duas vezes.
 */
class EsquecimentoTest extends TestCase
{
    private const TOPICOS = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    private const SEMENTE = 4242;

    private function agente(float $retencao): Agente
    {
        return new Agente(0, self::TOPICOS, 6, new Aleatorio(self::SEMENTE), Agente::GANHO_BASE, $retencao);
    }

    /** Pratica um topico algumas vezes, para que exista algo a esquecer. */
    private function praticar(Agente $agente, int $topicoId, int $vezes): void
    {
        for ($i = 0; $i < $vezes; $i++) {
            $agente->registrar($topicoId, 1250.0, true);
        }
    }

    public function test_sem_retencao_configurada_nada_decai(): void
    {
        $agente = $this->agente(0.0);
        $this->praticar($agente, 1, 10);

        $antes = $agente->real[1];
        $agente->esquecer(30.0);

        $this->assertSame($antes, $agente->real[1], 'o cenario sem esquecimento foi alterado');
    }

    public function test_o_que_foi_aprendido_decai(): void
    {
        $agente = $this->agente(7.0);
        $this->praticar($agente, 1, 10);

        $antes = $agente->real[1];
        $agente->esquecer(7.0);

        $this->assertLessThan($antes, $agente->real[1]);
    }

    public function test_nunca_cai_abaixo_da_proficiencia_inicial(): void
    {
        $agente = $this->agente(1.0);
        $inicial = $agente->real[1];
        $this->praticar($agente, 1, 5);

        $agente->esquecer(500.0);

        $this->assertGreaterThanOrEqual($inicial, $agente->real[1]);
    }

    public function test_topico_nunca_praticado_nao_muda(): void
    {
        $agente = $this->agente(7.0);
        $antes = $agente->real[2];

        $agente->esquecer(100.0);

        $this->assertSame($antes, $agente->real[2], 'nao se esquece o que nunca se aprendeu');
    }

    /**
     * O metodo e chamado uma vez por prova, com o intervalo daquela prova. Se o
     * decaimento nao se compusesse, o total esquecido dependeria de quantas
     * vezes o metodo foi chamado, e nao do tempo decorrido.
     */
    public function test_o_decaimento_se_compoe_ao_longo_das_chamadas(): void
    {
        $emPartes = $this->agente(7.0);
        $this->praticar($emPartes, 1, 10);
        $emPartes->esquecer(2.0);
        $emPartes->esquecer(3.0);

        $deUmaVez = $this->agente(7.0);
        $this->praticar($deUmaVez, 1, 10);
        $deUmaVez->esquecer(5.0);

        $this->assertEqualsWithDelta($deUmaVez->real[1], $emPartes->real[1], 1e-9);
    }

    public function test_retencao_maior_esquece_menos(): void
    {
        $curta = $this->agente(3.0);
        $this->praticar($curta, 1, 10);
        $curta->esquecer(7.0);

        $longa = $this->agente(30.0);
        $this->praticar($longa, 1, 10);
        $longa->esquecer(7.0);

        $this->assertGreaterThan($curta->real[1], $longa->real[1]);
    }

    public function test_reiniciar_desfaz_o_esquecimento(): void
    {
        $agente = $this->agente(3.0);
        $inicial = $agente->real;

        $this->praticar($agente, 1, 10);
        $agente->esquecer(50.0);
        $agente->reiniciar();

        $this->assertSame($inicial, $agente->real, 'os dois bracos partiriam de populacoes diferentes');
    }
}
