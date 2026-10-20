<?php

namespace Tests\Unit;

use App\Recomendacao\Elo;
use PHPUnit\Framework\TestCase;

/**
 * Garantias sobre os estimadores de proficiencia.
 *
 * Os dois estimadores alternativos existem para atacar o vies identificado no
 * experimento, e so servem a esse proposito se respeitarem tres condicoes: que
 * o estimador da aplicacao continue intacto, que a memoria que eles carregam
 * seja de fato usada, e que cada um se comporte na direcao que o justifica.
 *
 * Nao se testa aqui qual estimador e melhor. Isso e resultado de experimento, e
 * um teste que o exigisse impediria que o resultado fosse outro.
 */
class EstimadorTest extends TestCase
{
    private const DIFICULDADE = 1250.0;

    public function test_o_modo_padrao_reproduz_o_estimador_da_aplicacao(): void
    {
        foreach ([0, 5, 20, 60] as $praticas) {
            foreach ([true, false] as $acertou) {
                [$nova, ] = Elo::atualizarComEstado(
                    1200.0, self::DIFICULDADE, $acertou, $praticas, Elo::MODO_PADRAO, 0.0
                );

                $this->assertSame(
                    Elo::atualizar(1200.0, self::DIFICULDADE, $acertou, $praticas),
                    $nova,
                    "o modo padrao divergiu da aplicacao com {$praticas} praticas"
                );
            }
        }
    }

    public function test_o_modo_padrao_ignora_a_memoria(): void
    {
        [$semMemoria, ] = Elo::atualizarComEstado(1200.0, self::DIFICULDADE, true, 10, Elo::MODO_PADRAO, 0.0);
        [$comMemoria, ] = Elo::atualizarComEstado(1200.0, self::DIFICULDADE, true, 10, Elo::MODO_PADRAO, 0.9);

        $this->assertSame($semMemoria, $comMemoria);
    }

    /**
     * Com o K no piso, acertos consecutivos deveriam acelerar a subida da
     * estimativa. E exatamente o que o fator adaptativo promete e o que o
     * estimador atual nao faz.
     */
    public function test_o_fator_adaptativo_acelera_diante_de_erros_do_mesmo_lado(): void
    {
        $adaptativo = 1200.0; $memoria = 0.0;
        $padrao = 1200.0;

        for ($i = 0; $i < 8; $i++) {
            [$adaptativo, $memoria] = Elo::atualizarComEstado(
                $adaptativo, self::DIFICULDADE, true, 60, Elo::MODO_K_ADAPTATIVO, $memoria
            );
            $padrao = Elo::atualizar($padrao, self::DIFICULDADE, true, 60);
        }

        $this->assertGreaterThan(
            $padrao,
            $adaptativo,
            'o fator adaptativo deveria subir mais que o fator travado no piso'
        );
    }

    public function test_o_fator_adaptativo_volta_ao_piso_quando_os_erros_se_alternam(): void
    {
        $pontuacao = 1200.0; $memoria = 0.0;

        for ($i = 0; $i < 40; $i++) {
            [$pontuacao, $memoria] = Elo::atualizarComEstado(
                $pontuacao, self::DIFICULDADE, $i % 2 === 0, 60, Elo::MODO_K_ADAPTATIVO, $memoria
            );
        }

        $this->assertLessThan(
            0.35,
            abs($memoria),
            'erros alternados deveriam levar a memoria perto de zero, devolvendo o K ao piso'
        );
    }

    public function test_o_termo_de_tendencia_antecipa_o_movimento(): void
    {
        $tendencia = 1200.0; $memoria = 0.0;
        $padrao = 1200.0;

        for ($i = 0; $i < 6; $i++) {
            [$tendencia, $memoria] = Elo::atualizarComEstado(
                $tendencia, self::DIFICULDADE, true, 60, Elo::MODO_TENDENCIA, $memoria
            );
            $padrao = Elo::atualizar($padrao, self::DIFICULDADE, true, 60);
        }

        $this->assertGreaterThan($padrao, $tendencia);
        $this->assertGreaterThan(0.0, $memoria, 'a memoria deveria guardar a direcao dos ajustes');
    }

    /** A primeira resposta nao tem historico, logo os tres modos partem juntos. */
    public function test_na_primeira_resposta_os_tres_modos_coincidem_em_direcao(): void
    {
        $valores = [];

        foreach ([Elo::MODO_PADRAO, Elo::MODO_K_ADAPTATIVO, Elo::MODO_TENDENCIA] as $modo) {
            [$v, ] = Elo::atualizarComEstado(1200.0, self::DIFICULDADE, true, 0, $modo, 0.0);
            $valores[] = $v;
            $this->assertGreaterThan(1200.0, $v, "o modo {$modo} baixou a pontuacao apos um acerto");
        }

        $this->assertCount(3, $valores);
    }

    public function test_errar_reduz_a_pontuacao_em_todos_os_modos(): void
    {
        foreach ([Elo::MODO_PADRAO, Elo::MODO_K_ADAPTATIVO, Elo::MODO_TENDENCIA] as $modo) {
            [$v, ] = Elo::atualizarComEstado(1400.0, self::DIFICULDADE, false, 10, $modo, 0.0);
            $this->assertLessThan(1400.0, $v, "o modo {$modo} nao penalizou um erro");
        }
    }
}
