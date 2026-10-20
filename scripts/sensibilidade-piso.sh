#!/usr/bin/env bash
# Analise de sensibilidade do piso de cobertura.
#
# O piso so e aplicado quando a prova comporta, ao menos, uma vaga livre por
# topico alem das reservadas: tamanho >= topicos * (piso + 1). Com nove topicos,
# isso limita o piso efetivo ao tamanho da prova, e e por isso que a varredura
# percorre as duas dimensoes em vez de so o piso.
#
# Todas as celulas usam a mesma semente, logo a mesma populacao de agentes.
set -u

RAIZ=/var/www/storage/app/sens
AGENTES=${AGENTES:-1000}
PROVAS=${PROVAS:-18}

celula() {
  local tamanho=$1 piso=$2
  local dir="$RAIZ/t${tamanho}_p${piso}"
  echo "### tamanho=$tamanho piso=$piso"
  MSYS_NO_PATHCONV=1 docker compose exec -T app php artisan simular \
    --agentes="$AGENTES" --provas="$PROVAS" --tamanho="$tamanho" \
    --piso="$piso" --saida="$dir"
  echo
}

for par in "10 0" "10 1" "20 0" "20 1" "20 2" "30 0" "30 1" "30 2" "50 0" "50 1" "50 2" "50 4"; do
  celula $par
done

echo "FIM DA VARREDURA"
