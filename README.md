# SISTEMA DE RECOMENDAÇÃO ADAPTATIVA DE QUESTÕES PARA REFORÇO DA APRENDIZAGEM COM BASE NO DESEMPENHO DO ESTUDANTE: API

Serviço que estima a proficiência de um estudante por tópico e monta cada nova
prova concentrando as questões nas áreas em que ele teve o pior desempenho.

Trabalho de Curso, Ciência da Computação, Universidade Paulista.

## Como funciona

O estudante cria uma conta e escolhe quantas questões cada prova terá: 10, 20,
30, 40 ou 50.

A **primeira prova é diagnóstica**. Como ainda não há informação sobre o
estudante, as questões são distribuídas igualmente entre os tópicos.

Ao final da prova o sistema corrige, apresenta o desempenho detalhado por tópico
e atualiza a estimativa de proficiência. A correção acontece apenas na entrega,
e não a cada resposta: isso reproduz a situação de um simulado real e impede que
o estudante infira o gabarito observando a mudança da recomendação.

As provas seguintes são **otimizadas**. A quantidade de questões que cada tópico
recebe é proporcional a um peso que combina dois fatores:

- **fraqueza**: quanto menor a proficiência estimada, maior o peso;
- **espaçamento**: quanto mais dias sem praticar, maior o peso.

O primeiro fator concentra o treino onde há dificuldade. O segundo devolve à
prova os tópicos que ficaram de fora, para que o que já foi aprendido não se
perca (Ebbinghaus, 1913). A distribuição usa o método dos maiores restos, de
modo que a soma seja exatamente o tamanho pedido, e reserva um piso de cobertura
para que nenhum tópico desapareça por completo quando a prova comporta.

A proficiência é mantida na mesma escala usada para a dificuldade das questões e
atualizada segundo a lógica do sistema Elo: acertar uma questão difícil eleva
bastante a pontuação, acertar uma fácil eleva pouco, errar uma fácil reduz de
forma expressiva. Dentro de cada tópico, o sistema ainda prioriza as questões do
nível de dificuldade adequado à proficiência atual.

## Persistência do modelo do estudante

As proficiências e o histórico de provas pertencem à conta, e não à sessão de
navegação. O estudante retoma o estudo de onde parou, em qualquer dispositivo,
sem que o algoritmo volte a estimar sua proficiência do zero.

É essa persistência que torna o fator de espaçamento significativo: com datas
reais registradas, o intervalo entre uma prática e outra pode ser medido em
dias, que é a grandeza que a curva do esquecimento descreve. Provas feitas no
mesmo dia têm intervalo zero e, nesse caso, apenas o fator de fraqueza
diferencia os tópicos.

Uma questão já respondida volta a ficar disponível após três provas. A regra
anterior, de nunca repetir, esgotaria o banco em um histórico permanente.

## Estrutura

```
app/Recomendacao/
  Elo.php             estimativa de proficiência por tópico
  Recomendador.php    distribuição das questões entre os tópicos
  ServicoProva.php    orquestração do ciclo de provas sobre o banco

app/Simulacao/
  Aleatorio.php       sorteio com semente explícita
  Agente.php          estudante virtual e premissas do modelo
  Experimento.php     um braço do experimento, prova a prova

app/Console/Commands/
  Simular.php         comando que roda o experimento e grava os dados

database/seeders/Banco/
  Gerador.php         base dos geradores de questões
  Item.php            uma questão antes da carga
  Verificador.php     conferência do banco antes de gravar
  <Topico>.php        um gerador por tópico
```

`Elo` e `Recomendador` não dependem do framework nem do banco de dados. Recebem
o estado como array e devolvem a decisão. Isso permite que o mesmo código seja
usado pela aplicação e pelo ambiente de simulação, garantindo que aquilo que é
avaliado no experimento seja exatamente o que atende ao usuário.

## Ambiente de simulação

O experimento compara duas estratégias de montagem de prova sobre a mesma
população de estudantes virtuais e a mesma semente:

- **adaptativo**: `Recomendador::distribuir`;
- **linha de base**: `Recomendador::distribuirUniforme` em todas as provas.

```bash
docker compose exec app php artisan simular
```

Cada agente tem uma proficiência **real** por tópico, sorteada e desconhecida do
algoritmo, com áreas fortes e fracas próprias. A chance de acerto sai da mesma
curva logística do Elo aplicada à proficiência real, e a proficiência real sobe a
cada questão praticada do tópico, com ganho decrescente. As premissas do modelo
do estudante, inclusive as que trabalham contra a hipótese, estão declaradas no
cabeçalho de `app/Simulacao/Agente.php`; a mais discutível delas, o incremento de
aprendizagem, é exposta em `--ganho` justamente para que o resultado possa ser
testado contra outros valores.

Como o perfil real é conhecido, a simulação mede duas coisas que não se podem
medir em campo: o ganho de aprendizagem nos tópicos que começaram fracos e o erro
da própria estimativa do Elo.

Os dados brutos vão para `storage/app/simulacao/`: `experimento.csv`, com uma
linha por prova por braço, e `agentes.csv`, com o resultado final de cada agente.
Esse diretório é ignorado pelo Git, por conter saída gerada.

Opções: `--agentes`, `--provas`, `--tamanho`, `--semente`, `--ganho` e `--saida`.

## Executando

```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
```

A API fica disponível em `http://localhost:8000`.

### Sobre o desempenho no Windows

As dependências ficam em um volume nomeado montado em `/var/www/vendor`, e não
no diretório compartilhado com o host. Ler os milhares de arquivos de `vendor/`
através do bind mount do Windows dominava o tempo de resposta: só o boot do
framework levava 22 segundos, e uma rota sem acesso ao banco respondia em 4
segundos. Com `vendor` em disco nativo do contêiner, o boot caiu para 0,25
segundo e a mesma rota passou a responder em 40 milissegundos.

Por isso, ao alterar `composer.json`, é preciso reconstruir a imagem:

```bash
docker compose up -d --build
```

## Endpoints

| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/tamanhos` | Tamanhos de prova disponíveis |
| POST | `/api/cadastro` | Cria a conta do estudante e devolve o token |
| POST | `/api/login` | Autentica e devolve o token |
| POST | `/api/demonstracao` | Entra na conta fixa de demonstração, sem cadastro |
| POST | `/api/logout` | Revoga o token atual |
| GET | `/api/eu` | Perfil do estudante autenticado |
| PATCH | `/api/eu` | Altera o tamanho de prova preferido |
| GET | `/api/historico` | Provas concluídas, proficiências e previsão da próxima |
| POST | `/api/provas` | Monta a próxima prova e devolve suas questões |
| POST | `/api/provas/{prova}/respostas` | Registra a resposta de uma questão, sem corrigir |
| POST | `/api/provas/{prova}/finalizar` | Corrige a prova e atualiza as proficiências |

As rotas autenticadas usam token Bearer, emitido pelo Laravel Sanctum.

## Banco de questões

1792 questões distribuídas em 3 disciplinas, com 3 tópicos cada, em três níveis
de dificuldade.

| Disciplina | Tópicos |
|---|---|
| Português | Interpretação de texto, Gramática, Ortografia |
| Matemática | Funções, Geometria, Probabilidade |
| Biologia | Genética, Ecologia, Citologia |

As questões são produzidas por geradores, um por tópico, de dois tipos:

- **computados** (Funções, Geometria, Probabilidade e Genética): o enunciado é
  parametrizado e a resposta correta é calculada a partir dos parâmetros. Não há
  risco de gabarito errado, e a quantidade é limitada apenas pelo espaço de
  parâmetros;
- **curados** (Interpretação de texto, Gramática, Ortografia, Ecologia e
  Citologia): partem de uma base de textos ou de fatos escrita e revisada item a
  item. A combinatória apenas varia a forma de perguntar, e a quantidade é
  limitada pelo tamanho da base.

Em ambos os casos os distratores correspondem a erros plausíveis, e não a
alternativas aleatórias: em Matemática são os resultados de trocar um sinal ou
esquecer uma etapa; nos curados, são fatos verdadeiros aplicados ao objeto
errado.

Antes de gravar, o `Verificador` recusa a carga se encontrar questão com
alternativa repetida ou vazia, questão duplicada, nível inválido ou gabarito
divergente da resposta declarada.

## Stack

Laravel 13, Sanctum, MySQL 8, Docker.
