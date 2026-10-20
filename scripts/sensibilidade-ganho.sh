#!/usr/bin/env bash
# Sensibilidade ao ganho de aprendizagem por pratica.
#
# O ganho base e o parametro mais discutivel do modelo do agente. A conclusao
# so vale se sobreviver a valores diferentes dele.
set -u
for g in 3 6 9 12; do
  echo "### ganho=$g"
  MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
    --agentes=1000 --provas=18 --tamanho=20 --piso=1 --ganho="$g" \
    --saida="/var/www/storage/app/ganho/g${g}"
  echo
done
echo FIM
