#!/usr/bin/env bash
# Comparacao dos tres estimadores de proficiencia.
#
# O vies e medido contra a proficiencia REAL do agente, que so a simulacao
# conhece. Roda nos dois cenarios, com e sem esquecimento, porque o vies nasce
# justamente de a proficiencia real se mover enquanto e estimada.
set -u
RAIZ=/var/www/storage/app/vies
for r in 0 7; do
  for e in padrao k_adaptativo tendencia; do
    echo "### retencao=$r estimador=$e"
    MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
      --agentes=1000 --provas=18 --tamanho=20 --piso=1 \
      --retencao="$r" --estimador="$e" --saida="$RAIZ/r${r}_${e}"
    echo
  done
done
echo "FIM"
