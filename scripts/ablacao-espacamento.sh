#!/usr/bin/env bash
# Ablacao do fator de espacamento.
#
# Teto 1 anula o espacamento: o peso passa a depender so da fraqueza estimada.
# Comparar os dois tetos no cenario COM esquecimento isola a contribuicao do
# componente que a repeticao espacada justifica.
set -u
for r in 0 7; do
  for t in 1 2.5; do
    echo "### retencao=$r teto=$t"
    MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
      --agentes=1000 --provas=18 --tamanho=20 --piso=1 \
      --retencao="$r" --teto="$t" --saida="/var/www/storage/app/abl/r${r}_t${t}"
    echo
  done
done
