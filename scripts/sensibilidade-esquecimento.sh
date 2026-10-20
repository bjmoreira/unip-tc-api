#!/usr/bin/env bash
# Cenario com esquecimento.
#
# Varre a constante de retencao em dias, com e sem o piso de cobertura, no
# tamanho de prova usado no experimento principal. A retencao 0 e o controle:
# reproduz o cenario sem esquecimento ja publicado.
#
# A faixa cobre de uma retencao curta (3 dias, mais curta que o maior intervalo
# entre provas) ate uma longa (30 dias, quase o horizonte do experimento), para
# que o efeito apareca nos dois extremos.
set -u

RAIZ=/var/www/storage/app/esq
AGENTES=${AGENTES:-1000}
PROVAS=${PROVAS:-18}
TAMANHO=${TAMANHO:-20}

for piso in 0 1; do
  for r in 0 3 7 14 30; do
    echo "### piso=$piso retencao=$r"
    MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
      --agentes="$AGENTES" --provas="$PROVAS" --tamanho="$TAMANHO" \
      --piso="$piso" --retencao="$r" --saida="$RAIZ/p${piso}_r${r}"
    echo
  done
done

echo "FIM DA VARREDURA"
