<?php

namespace Tests\Unit;

use App\Recomendacao\Recomendador;
use PHPUnit\Framework\TestCase;

/**
 * Garantias sobre o piso de cobertura na montagem da prova.
 *
 * O piso deixou de ser constante fechada e passou a parametro opcional de
 * distribuir(), para que a analise de sensibilidade possa varre-lo. Estes
 * testes protegem as duas pontas dessa mudanca: que o comportamento da
 * aplicacao continua exatamente o de antes quando ninguem passa o parametro, e
 * que o parametro, quando passado, faz o que promete.
 *
 * Estende a TestCase do PHPUnit porque o Recomendador nao depende do framework.
 */
class PisoCoberturaTest extends TestCase
{
    private const TOPICOS = 9;

    /**
     * Estado com um unico topico fraco, para que a distribuicao ponderada
     * tenha uma direcao clara e o efeito do piso fique visivel.
     *
     * @return array<int, array{topico_id:int, pontuacao:float, dias_sem_praticar:?float}>
     */
    private function estado(): array
    {
        $estado = [];

        for ($i = 1; $i <= self::TOPICOS; $i++) {
            $estado[] = [
                'topico_id'         => $i,
                'pontuacao'         => $i === 1 ? 900.0 : 1350.0,
                'dias_sem_praticar' => 1.0,
            ];
        }

        return $estado;
    }

    public function test_sem_parametro_usa_o_piso_da_aplicacao(): void
    {
        $this->assertSame(
            Recomendador::distribuir($this->estado(), 20),
            Recomendador::distribuir($this->estado(), 20, Recomendador::PISO_COBERTURA),
            'a chamada da aplicacao mudou de comportamento ao ganhar o parametro'
        );
    }

    public function test_o_piso_garante_ao_menos_uma_questao_por_topico(): void
    {
        foreach (Recomendador::distribuir($this->estado(), 20, 1) as $topicoId => $qtd) {
            $this->assertGreaterThanOrEqual(1, $qtd, "topico {$topicoId} ficou de fora da prova");
        }
    }

    public function test_piso_zero_libera_vagas_para_o_topico_fraco(): void
    {
        $comPiso = Recomendador::distribuir($this->estado(), 20, 1);
        $semPiso = Recomendador::distribuir($this->estado(), 20, 0);

        $this->assertGreaterThan(
            $comPiso[1],
            $semPiso[1],
            'retirar o piso deveria concentrar mais questoes no topico fraco'
        );
    }

    /**
     * Com nove topicos, uma prova de vinte nao comporta piso 2: reservaria
     * dezoito das vinte vagas e sobraria quase nada para a ponderacao. Nesse
     * caso o piso e descartado por inteiro, e o resultado tem de ser igual ao
     * de piso zero. E o limite que a analise de sensibilidade precisa mostrar.
     */
    public function test_piso_grande_demais_para_a_prova_e_descartado(): void
    {
        $this->assertSame(
            Recomendador::distribuir($this->estado(), 20, 0),
            Recomendador::distribuir($this->estado(), 20, 2),
            'piso que nao cabe deveria ser ignorado, nao aplicado pela metade'
        );
    }

    public function test_a_soma_fecha_no_tamanho_pedido_em_qualquer_combinacao(): void
    {
        foreach ([10, 20, 30, 40, 50] as $tamanho) {
            foreach ([0, 1, 2, 3, 4, 5] as $piso) {
                $this->assertSame(
                    $tamanho,
                    array_sum(Recomendador::distribuir($this->estado(), $tamanho, $piso)),
                    "prova de {$tamanho} com piso {$piso} nao somou o tamanho pedido"
                );
            }
        }
    }

    public function test_piso_negativo_e_tratado_como_zero(): void
    {
        $this->assertSame(
            Recomendador::distribuir($this->estado(), 20, 0),
            Recomendador::distribuir($this->estado(), 20, -3)
        );
    }
}
