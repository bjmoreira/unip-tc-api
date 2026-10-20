#!/usr/bin/env bash
# Calendario intermitente: o fator de espacamento acorda com intervalos longos?
#
# A ablacao anterior mostrou o componente inerte sob provas separadas por 1 a 4
# dias. A hipotese e que o regime denso e a causa. Aqui o intervalo cresce e a
# ablacao e repetida: se o teto passar a importar, a hipotese se confirma.
set -u
for faixa in "1 4" "7 15" "15 30"; do
  set -- $faixa
  for t in 1 2.5; do
    echo "### intervalo=$1-$2 teto=$t"
    MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
      --agentes=1000 --provas=18 --tamanho=20 --piso=1 --retencao=7 --teto="$t" \
      --intervalo-min="$1" --intervalo-max="$2" --saida="/var/www/storage/app/cal/i${1}_${2}_t${t}"
    echo
  done
done
echo FIM
