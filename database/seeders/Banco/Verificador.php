<?php

namespace Database\Seeders\Banco;

/**
 * Conferencia do banco antes da carga.
 *
 * O gerador produz muitas questoes a partir de pouco codigo, e um engano em uma
 * familia se multiplica por dezenas de itens. Esta classe percorre tudo o que
 * foi gerado e recusa a carga se encontrar questao com alternativa repetida,
 * enunciado duplicado, nivel invalido ou alternativa vazia.
 *
 * A verificacao roda sempre que o seeder e executado, e nao apenas em testes,
 * porque um banco malformado invalidaria tanto o uso quanto o experimento.
 */
final class Verificador
{
    private const NIVEIS = ['facil', 'medio', 'dificil'];

    /**
     * @param array<Item> $itens
     * @return array<string> problemas encontrados
     */
    public static function conferir(array $itens, string $topico): array
    {
        $problemas = [];
        $enunciados = [];

        foreach ($itens as $i => $item) {
            $onde = "$topico #$i";

            if (! in_array($item->nivel, self::NIVEIS, true)) {
                $problemas[] = "$onde: nível inválido \"{$item->nivel}\"";
            }

            if (trim($item->enunciado) === '') {
                $problemas[] = "$onde: enunciado vazio";
            }

            // Enunciado repetido so e problema quando a resposta tambem se
            // repete: ai sao duas copias da mesma questao.
            $chave = $item->enunciado . '|' . $item->correta;

            if (isset($enunciados[$chave])) {
                $problemas[] = "$onde: questão repetida";
            }
            $enunciados[$chave] = true;

            try {
                [$alternativas, $indice] = $item->alternativas($i);
            } catch (\RuntimeException $e) {
                $problemas[] = "$onde: " . $e->getMessage();
                continue;
            }

            if (count($alternativas) !== 4) {
                $problemas[] = "$onde: " . count($alternativas) . ' alternativas em vez de 4';
            }

            if (count(array_unique($alternativas)) !== count($alternativas)) {
                $problemas[] = "$onde: alternativas repetidas";
            }

            foreach ($alternativas as $a) {
                if (trim((string) $a) === '') {
                    $problemas[] = "$onde: alternativa vazia";
                }
            }

            if ($alternativas[$indice] !== $item->correta) {
                $problemas[] = "$onde: gabarito não corresponde à resposta declarada";
            }
        }

        return $problemas;
    }
}
