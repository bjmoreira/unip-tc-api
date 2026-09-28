<?php

namespace Tests\Unit;

use App\Simulacao\Agente;
use App\Simulacao\Aleatorio;
use App\Simulacao\Experimento;
use PHPUnit\Framework\TestCase;

/**
 * Garantias sobre o ambiente de simulacao.
 *
 * Nao se verifica aqui se a estrategia adaptativa e melhor: isso e o resultado
 * do experimento, e um teste que o exigisse passaria a impedir que o resultado
 * fosse negativo. O que se verifica sao as condicoes sem as quais o resultado,
 * qualquer que seja, nao teria valor: que a execucao seja reproduzivel, que a
 * semente realmente mude o sorteio, que os dois bracos partam da mesma
 * populacao e que a linha de base seja de fato uniforme.
 *
 * O teste estende a TestCase do PHPUnit, e nao a da aplicacao, porque as classes
 * de simulacao nao dependem do framework. Se algum dia passarem a depender, este
 * teste quebra, e a quebra e a informacao desejada.
 */
class SimulacaoTest extends TestCase
{
    private const TOPICOS = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    private const SEMENTE = 4242;

    /** @return array<int, array<string, array{dificuldade: float, total: int}>> */
    private function banco(): array
    {
        $banco = [];

        foreach (self::TOPICOS as $topicoId) {
            $banco[$topicoId] = [
                'facil'   => ['dificuldade' => 1050.0, 'total' => 60],
                'medio'   => ['dificuldade' => 1250.0, 'total' => 80],
                'dificil' => ['dificuldade' => 1450.0, 'total' => 60],
            ];
        }

        return $banco;
    }

    /** @return array<int, Agente> */
    private function agentes(int $quantidade, int $provas, int $semente = self::SEMENTE): array
    {
        $sorteio = new Aleatorio($semente);
        $agentes = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $agentes[] = new Agente($i, self::TOPICOS, $provas, $sorteio);
        }

        return $agentes;
    }

    public function test_a_mesma_semente_produz_o_mesmo_resultado(): void
    {
        $experimento = new Experimento($this->banco(), 20, 8, self::SEMENTE);

        $primeira = $experimento->rodar($this->agentes(30, 8), true);
        $segunda  = $experimento->rodar($this->agentes(30, 8), true);

        $this->assertSame($primeira['provas'], $segunda['provas']);
        $this->assertSame($primeira['agentes'], $segunda['agentes']);
    }

    public function test_semente_diferente_produz_resultado_diferente(): void
    {
        $banco = $this->banco();

        $a = (new Experimento($banco, 20, 8, self::SEMENTE))->rodar($this->agentes(30, 8), true);
        $b = (new Experimento($banco, 20, 8, self::SEMENTE + 1))->rodar($this->agentes(30, 8, self::SEMENTE + 1), true);

        $this->assertNotSame($a['provas'], $b['provas']);
    }

    public function test_os_dois_bracos_partem_da_mesma_populacao(): void
    {
        $experimento = new Experimento($this->banco(), 20, 8, self::SEMENTE);
        $agentes = $this->agentes(30, 8);

        $adaptativo = $experimento->rodar($agentes, true);
        $base       = $experimento->rodar($agentes, false);

        // A primeira prova e diagnostica nos dois bracos, logo tudo o que e
        // medido ao final dela tem de coincidir. Se nao coincidir, algum estado
        // vazou de uma execucao para a outra.
        $this->assertEquals($adaptativo['provas'][0], $base['provas'][0]);

        $this->assertSame(
            array_column($adaptativo['agentes'], 'real_fracos_inicio'),
            array_column($base['agentes'], 'real_fracos_inicio')
        );
    }

    public function test_a_linha_de_base_distribui_sempre_a_mesma_quantidade(): void
    {
        $experimento = new Experimento($this->banco(), 20, 8, self::SEMENTE);
        $base = $experimento->rodar($this->agentes(30, 8), false);

        $questoes = array_column($base['provas'], 'questoes_fracos');

        $this->assertCount(1, array_unique(array_map(fn (float $v) => round($v, 6), $questoes)));
    }

    public function test_o_braco_adaptativo_concentra_mais_nos_topicos_fracos(): void
    {
        $experimento = new Experimento($this->banco(), 20, 12, self::SEMENTE);
        $agentes = $this->agentes(60, 12);

        $adaptativo = $experimento->rodar($agentes, true);
        $base       = $experimento->rodar($agentes, false);

        $ultima = count($adaptativo['provas']) - 1;

        // Isto nao afirma que o resultado de aprendizagem e melhor: afirma que o
        // mecanismo que se pretende medir esta de fato operando.
        $this->assertGreaterThan(
            $base['provas'][$ultima]['questoes_fracos'],
            $adaptativo['provas'][$ultima]['questoes_fracos']
        );
    }

    public function test_o_agente_reiniciado_volta_a_proficiencia_inicial(): void
    {
        $agente = $this->agentes(1, 5)[0];
        $inicial = $agente->real;

        $agente->registrar(1, 1250.0, true);
        $agente->registrar(1, 1250.0, true);

        $this->assertNotSame($inicial, $agente->real);

        $agente->reiniciar();

        $this->assertSame($inicial, $agente->real);
        $this->assertSame(0, $agente->praticas[1]);
    }

    public function test_o_calendario_do_agente_nao_muda_entre_bracos(): void
    {
        $agente = $this->agentes(1, 10)[0];
        $intervalos = $agente->intervalos;

        $agente->reiniciar();

        $this->assertSame($intervalos, $agente->intervalos);
        $this->assertSame(0.0, $agente->relogio);
    }
}
