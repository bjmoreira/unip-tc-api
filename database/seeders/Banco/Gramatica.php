<?php

namespace Database\Seeders\Banco;

/**
 * Gramatica: gerador curado.
 *
 * Cada frase da base foi escrita e classificada individualmente. O codigo nao
 * deduz analise sintatica: ele apenas apresenta, em formatos diferentes, a
 * classificacao que ja consta da base.
 *
 * As frases evitam construcoes em que a analise seria discutivel, como orcoes
 * reduzidas ambiguas ou casos de dupla regencia aceitos pela norma. Um gabarito
 * so entra na base quando a classificacao e consensual nas gramaticas de
 * referencia.
 */
final class Gramatica extends Gerador
{
    /** [frase, termo destacado, classificacao correta, tres classificacoes incorretas] */
    private const CLASSES = [
        ['O menino correu rapidamente para casa.', 'rapidamente', 'Advérbio de modo',
            ['Adjetivo', 'Substantivo', 'Preposição']],
        ['A casa antiga foi restaurada no ano passado.', 'antiga', 'Adjetivo',
            ['Advérbio', 'Substantivo', 'Pronome']],
        ['Ela comprou três livros na feira.', 'três', 'Numeral',
            ['Adjetivo', 'Advérbio', 'Artigo']],
        ['Nós chegamos cedo à reunião.', 'Nós', 'Pronome pessoal',
            ['Pronome possessivo', 'Substantivo', 'Numeral']],
        ['O meu caderno ficou na escola.', 'meu', 'Pronome possessivo',
            ['Pronome pessoal', 'Adjetivo', 'Artigo']],
        ['Este relatório precisa de revisão.', 'Este', 'Pronome demonstrativo',
            ['Pronome possessivo', 'Artigo', 'Numeral']],
        ['Ele estuda porque quer passar no concurso.', 'porque', 'Conjunção causal',
            ['Preposição', 'Advérbio', 'Pronome relativo']],
        ['Cheguei atrasado, mas consegui assistir à aula.', 'mas', 'Conjunção adversativa',
            ['Conjunção aditiva', 'Conjunção conclusiva', 'Preposição']],
        ['Estudou muito, portanto merecia a aprovação.', 'portanto', 'Conjunção conclusiva',
            ['Conjunção adversativa', 'Conjunção causal', 'Advérbio de tempo']],
        ['O livro de história está sobre a mesa.', 'de', 'Preposição',
            ['Conjunção', 'Artigo', 'Advérbio']],
        ['O gato dorme sobre o sofá da sala.', 'gato', 'Substantivo concreto',
            ['Substantivo abstrato', 'Adjetivo', 'Advérbio']],
        ['A aluna que venceu o concurso é da minha turma.', 'que', 'Pronome relativo',
            ['Conjunção integrante', 'Preposição', 'Advérbio']],
        ['Espero que você compareça à cerimônia.', 'que', 'Conjunção integrante',
            ['Pronome relativo', 'Preposição', 'Advérbio de dúvida']],
        ['Talvez ele mude de ideia até amanhã.', 'Talvez', 'Advérbio de dúvida',
            ['Advérbio de tempo', 'Conjunção', 'Pronome indefinido']],
        ['Ontem terminamos o projeto da disciplina.', 'Ontem', 'Advérbio de tempo',
            ['Advérbio de lugar', 'Substantivo', 'Preposição']],
        ['Alguém deixou a janela aberta.', 'Alguém', 'Pronome indefinido',
            ['Pronome pessoal', 'Substantivo', 'Numeral']],
        ['A alegria tomou conta da plateia.', 'alegria', 'Substantivo abstrato',
            ['Substantivo concreto', 'Adjetivo', 'Advérbio']],
        ['O professor entregou as provas corrigidas.', 'as', 'Artigo definido',
            ['Pronome pessoal', 'Preposição', 'Numeral']],
        ['Comprei um caderno novo para as anotações.', 'um', 'Artigo indefinido',
            ['Numeral', 'Pronome indefinido', 'Preposição']],
        ['Ela agiu com serenidade diante da crítica.', 'com serenidade', 'Locução adverbial de modo',
            ['Locução adjetiva', 'Objeto direto', 'Aposto']],
        ['Recebemos a visita do diretor da faculdade.', 'da faculdade', 'Locução adjetiva',
            ['Locução adverbial', 'Objeto indireto', 'Vocativo']],
        ['Nunca imaginei encontrar você aqui.', 'Nunca', 'Advérbio de negação',
            ['Advérbio de tempo', 'Conjunção', 'Pronome indefinido']],
        ['Ele mora longe do centro da cidade.', 'longe', 'Advérbio de lugar',
            ['Advérbio de modo', 'Adjetivo', 'Preposição']],
        ['O time jogou bastante mal naquela partida.', 'bastante', 'Advérbio de intensidade',
            ['Pronome indefinido', 'Adjetivo', 'Numeral']],
        ['Ai! Machuquei o pé na escada.', 'Ai', 'Interjeição',
            ['Advérbio', 'Conjunção', 'Preposição']],
        ['Não fui à aula nem avisei o professor.', 'nem', 'Conjunção aditiva',
            ['Conjunção adversativa', 'Conjunção alternativa', 'Preposição']],
        ['Ou você estuda, ou perde a bolsa.', 'Ou', 'Conjunção alternativa',
            ['Conjunção aditiva', 'Conjunção conclusiva', 'Advérbio']],
        ['Volte logo, pois precisamos de você aqui.', 'pois', 'Conjunção explicativa',
            ['Conjunção conclusiva', 'Conjunção adversativa', 'Preposição']],
        ['Terminei o relatório enquanto esperava o ônibus.', 'enquanto', 'Conjunção temporal',
            ['Conjunção causal', 'Conjunção condicional', 'Advérbio de tempo']],
        ['Se chover, adiaremos o passeio.', 'Se', 'Conjunção condicional',
            ['Conjunção integrante', 'Conjunção causal', 'Pronome relativo']],
        ['Cada aluno recebeu um exemplar do manual.', 'Cada', 'Pronome indefinido invariável',
            ['Numeral', 'Artigo definido', 'Adjetivo']],
        ['A quinta colocada subiu ao pódio.', 'quinta', 'Numeral ordinal',
            ['Numeral cardinal', 'Adjetivo', 'Substantivo']],
    ];

    /** [frase, forma verbal, tempo e modo, tres alternativas incorretas] */
    private const VERBOS = [
        ['Eu estudo todos os dias pela manhã.', 'estudo', 'Presente do indicativo',
            ['Pretérito perfeito do indicativo', 'Presente do subjuntivo', 'Futuro do presente']],
        ['Ontem eu estudei até tarde da noite.', 'estudei', 'Pretérito perfeito do indicativo',
            ['Pretérito imperfeito do indicativo', 'Presente do indicativo', 'Futuro do pretérito']],
        ['Naquela época, eu estudava em outra cidade.', 'estudava', 'Pretérito imperfeito do indicativo',
            ['Pretérito perfeito do indicativo', 'Pretérito mais-que-perfeito', 'Presente do subjuntivo']],
        ['Quando cheguei, ele já saíra da sala.', 'saíra', 'Pretérito mais-que-perfeito do indicativo',
            ['Pretérito perfeito do indicativo', 'Futuro do pretérito', 'Pretérito imperfeito do subjuntivo']],
        ['Amanhã viajaremos para o congresso.', 'viajaremos', 'Futuro do presente do indicativo',
            ['Futuro do pretérito do indicativo', 'Presente do indicativo', 'Futuro do subjuntivo']],
        ['Eu viajaria se tivesse tempo livre.', 'viajaria', 'Futuro do pretérito do indicativo',
            ['Futuro do presente do indicativo', 'Pretérito imperfeito do indicativo', 'Presente do subjuntivo']],
        ['Espero que ele venha à reunião.', 'venha', 'Presente do subjuntivo',
            ['Presente do indicativo', 'Imperativo afirmativo', 'Futuro do subjuntivo']],
        ['Se eu tivesse tempo, terminaria o projeto.', 'tivesse', 'Pretérito imperfeito do subjuntivo',
            ['Pretérito perfeito do subjuntivo', 'Futuro do subjuntivo', 'Pretérito imperfeito do indicativo']],
        ['Quando você chegar, avise a recepção.', 'chegar', 'Futuro do subjuntivo',
            ['Infinitivo pessoal', 'Presente do subjuntivo', 'Futuro do presente']],
        ['Traga os documentos amanhã cedo.', 'Traga', 'Imperativo afirmativo',
            ['Presente do indicativo', 'Futuro do subjuntivo', 'Infinitivo']],
        ['Não faça barulho durante a prova.', 'faça', 'Imperativo negativo',
            ['Imperativo afirmativo', 'Presente do indicativo', 'Pretérito perfeito']],
        ['É importante estudar todos os dias.', 'estudar', 'Infinitivo impessoal',
            ['Gerúndio', 'Particípio', 'Presente do indicativo']],
        ['Chegamos correndo para não perder a aula.', 'correndo', 'Gerúndio',
            ['Infinitivo', 'Particípio', 'Presente do indicativo']],
        ['O trabalho foi entregue no prazo combinado.', 'entregue', 'Particípio',
            ['Gerúndio', 'Infinitivo', 'Pretérito imperfeito']],
    ];

    /** [frase, voz verbal, tres alternativas incorretas] */
    private const VOZES = [
        ['O aluno resolveu o exercício.', 'Voz ativa',
            ['Voz passiva analítica', 'Voz passiva sintética', 'Voz reflexiva']],
        ['O exercício foi resolvido pelo aluno.', 'Voz passiva analítica',
            ['Voz ativa', 'Voz passiva sintética', 'Voz reflexiva']],
        ['Resolveram-se os exercícios em sala.', 'Voz passiva sintética',
            ['Voz ativa', 'Voz passiva analítica', 'Voz reflexiva']],
        ['A criança machucou-se no parque.', 'Voz reflexiva',
            ['Voz ativa', 'Voz passiva analítica', 'Voz passiva sintética']],
        ['A prova será corrigida pela banca.', 'Voz passiva analítica',
            ['Voz ativa', 'Voz passiva sintética', 'Voz reflexiva']],
        ['Vendem-se apartamentos na região central.', 'Voz passiva sintética',
            ['Voz ativa', 'Voz passiva analítica', 'Voz reflexiva']],
        ['Os candidatos entregaram as inscrições.', 'Voz ativa',
            ['Voz passiva analítica', 'Voz passiva sintética', 'Voz reflexiva']],
        ['Eles se cumprimentaram na entrada do prédio.', 'Voz reflexiva recíproca',
            ['Voz ativa', 'Voz passiva analítica', 'Voz passiva sintética']],
    ];

    /** [frase correta, regra de colocacao, tres frases incorretas] */
    private const COLOCACAO = [
        ['Não me convidaram para a reunião.', 'próclise obrigatória com palavra de sentido negativo',
            ['Não convidaram-me para a reunião.', 'Não convidar-me-ão para a reunião de ontem.',
             'Não convidaram me para a reunião.']],
        ['Quem me avisou do resultado foi o coordenador.', 'próclise obrigatória com pronome relativo',
            ['Quem avisou-me do resultado foi o coordenador.',
             'Quem avisar-me-á do resultado foi o coordenador.',
             'Quem avisou me do resultado foi o coordenador.']],
        ['Todos se esforçaram para concluir o projeto.', 'próclise obrigatória com pronome indefinido',
            ['Todos esforçaram-se para concluir o projeto.',
             'Todos esforçar-se-ão para concluir o projeto ontem.',
             'Todos esforçaram se para concluir o projeto.']],
        ['Entregaram-me os documentos pela manhã.', 'ênclise no início da oração',
            ['Me entregaram os documentos pela manhã.',
             'Entregaram me os documentos pela manhã.',
             'Entregar-me-ão os documentos pela manhã de ontem.']],
        ['Far-se-á uma nova convocação em breve.', 'mesóclise com verbo no futuro do presente',
            ['Fará-se uma nova convocação em breve.',
             'Se fará uma nova convocação em breve, disseram eles ontem.',
             'Far-se-ia uma nova convocação em breve, se houvesse tempo hábil.']],
        ['Em se tratando de prazos, sejamos rigorosos.', 'próclise com gerúndio precedido de preposição em',
            ['Em tratando-se de prazos, sejamos rigorosos.',
             'Em tratar-se-á de prazos, sejamos rigorosos.',
             'Em tratando se de prazos, sejamos rigorosos.']],
    ];

    /** [frase, pergunta sobre o termo, classificacao correta, tres incorretas] */
    private const SINTAXE = [
        ['O aluno resolveu o exercício.', 'O aluno', 'Sujeito simples',
            ['Objeto direto', 'Predicativo do sujeito', 'Adjunto adverbial']],
        ['O aluno resolveu o exercício.', 'o exercício', 'Objeto direto',
            ['Sujeito', 'Objeto indireto', 'Complemento nominal']],
        ['Precisamos de mais tempo.', 'de mais tempo', 'Objeto indireto',
            ['Objeto direto', 'Adjunto adverbial', 'Aposto']],
        ['A prova estava difícil.', 'difícil', 'Predicativo do sujeito',
            ['Objeto direto', 'Adjunto adnominal', 'Aposto']],
        ['Chegamos cedo à universidade.', 'cedo', 'Adjunto adverbial de tempo',
            ['Objeto direto', 'Predicativo', 'Sujeito']],
        ['Meu irmão, engenheiro civil, mudou-se para o interior.', 'engenheiro civil', 'Aposto',
            ['Vocativo', 'Objeto direto', 'Predicativo do objeto']],
        ['Bruno, traga o material amanhã.', 'Bruno', 'Vocativo',
            ['Sujeito', 'Aposto', 'Objeto direto']],
        ['Choveu bastante durante a madrugada.', 'a oração', 'Oração sem sujeito',
            ['Sujeito simples', 'Sujeito oculto', 'Sujeito indeterminado']],
        ['Falaram mal do resultado na reunião.', 'a oração', 'Sujeito indeterminado',
            ['Sujeito simples', 'Oração sem sujeito', 'Sujeito composto']],
        ['Estudamos a noite toda para a prova.', 'a oração', 'Sujeito oculto',
            ['Sujeito indeterminado', 'Oração sem sujeito', 'Sujeito composto']],
        ['O professor e os alunos organizaram a feira.', 'O professor e os alunos', 'Sujeito composto',
            ['Sujeito simples', 'Sujeito oculto', 'Objeto direto']],
        ['A cidade de São Paulo cresceu rapidamente.', 'de São Paulo', 'Adjunto adnominal',
            ['Complemento nominal', 'Aposto', 'Objeto indireto']],
        ['Tenho necessidade de silêncio.', 'de silêncio', 'Complemento nominal',
            ['Adjunto adnominal', 'Objeto indireto', 'Aposto']],
        ['O menino, que estava cansado, dormiu cedo.', 'que estava cansado',
            'Oração subordinada adjetiva explicativa',
            [
                'Oração subordinada adjetiva restritiva',
                'Oração coordenada aditiva',
                'Oração subordinada substantiva objetiva direta',
            ]],
        ['Os alunos que estudaram passaram no exame.', 'que estudaram',
            'Oração subordinada adjetiva restritiva',
            [
                'Oração subordinada adjetiva explicativa',
                'Oração coordenada adversativa',
                'Oração subordinada adverbial causal',
            ]],
        ['Espero que tudo dê certo.', 'que tudo dê certo',
            'Oração subordinada substantiva objetiva direta',
            [
                'Oração subordinada adjetiva restritiva',
                'Oração coordenada conclusiva',
                'Oração subordinada adverbial final',
            ]],
        ['Saímos cedo porque o trânsito estava congestionado.', 'porque o trânsito estava congestionado',
            'Oração subordinada adverbial causal',
            [
                'Oração subordinada adverbial consecutiva',
                'Oração coordenada explicativa',
                'Oração subordinada substantiva completiva nominal',
            ]],
        ['Estudou tanto que adoeceu.', 'que adoeceu',
            'Oração subordinada adverbial consecutiva',
            [
                'Oração subordinada adverbial causal',
                'Oração subordinada adjetiva restritiva',
                'Oração coordenada aditiva',
            ]],
        ['Se você quiser, podemos remarcar a entrevista.', 'Se você quiser',
            'Oração subordinada adverbial condicional',
            [
                'Oração subordinada adverbial temporal',
                'Oração subordinada adverbial causal',
                'Oração coordenada alternativa',
            ]],
        ['Quando o sinal tocou, todos saíram da sala.', 'Quando o sinal tocou',
            'Oração subordinada adverbial temporal',
            [
                'Oração subordinada adverbial condicional',
                'Oração subordinada adjetiva restritiva',
                'Oração coordenada conclusiva',
            ]],
        ['Ele não estudou, portanto não foi aprovado.', 'portanto não foi aprovado',
            'Oração coordenada sindética conclusiva',
            [
                'Oração coordenada sindética adversativa',
                'Oração subordinada adverbial causal',
                'Oração coordenada sindética aditiva',
            ]],
        ['Ou você decide agora, ou perde a vaga.', 'ou perde a vaga',
            'Oração coordenada sindética alternativa',
            [
                'Oração coordenada sindética aditiva',
                'Oração subordinada adverbial condicional',
                'Oração coordenada sindética explicativa',
            ]],
        ['Ele chegou cedo e organizou a sala.', 'e organizou a sala',
            'Oração coordenada sindética aditiva',
            [
                'Oração coordenada sindética conclusiva',
                'Oração subordinada adverbial temporal',
                'Oração subordinada adjetiva restritiva',
            ]],
        ['Fale mais baixo, que o bebê está dormindo.', 'que o bebê está dormindo',
            'Oração coordenada sindética explicativa',
            [
                'Oração subordinada adverbial causal',
                'Oração subordinada substantiva objetiva direta',
                'Oração coordenada sindética adversativa',
            ]],
        ['Ela agiu conforme o combinado na reunião.', 'conforme o combinado na reunião',
            'Oração subordinada adverbial conformativa',
            [
                'Oração subordinada adverbial comparativa',
                'Oração subordinada adverbial condicional',
                'Oração coordenada sindética conclusiva',
            ]],
        ['Corri tanto quanto pude naquela prova.', 'quanto pude naquela prova',
            'Oração subordinada adverbial comparativa',
            [
                'Oração subordinada adverbial consecutiva',
                'Oração subordinada adverbial conformativa',
                'Oração subordinada adjetiva restritiva',
            ]],
        ['Estudamos bastante para que o resultado fosse bom.', 'para que o resultado fosse bom',
            'Oração subordinada adverbial final',
            [
                'Oração subordinada adverbial causal',
                'Oração subordinada adverbial consecutiva',
                'Oração coordenada sindética conclusiva',
            ]],
        ['Embora chovesse muito, a partida foi realizada.', 'Embora chovesse muito',
            'Oração subordinada adverbial concessiva',
            [
                'Oração subordinada adverbial condicional',
                'Oração subordinada adverbial causal',
                'Oração coordenada sindética adversativa',
            ]],
        ['É necessário que todos assinem a lista.', 'que todos assinem a lista',
            'Oração subordinada substantiva subjetiva',
            [
                'Oração subordinada substantiva objetiva direta',
                'Oração subordinada adjetiva explicativa',
                'Oração subordinada adverbial final',
            ]],
        ['Tenho certeza de que ele virá.', 'de que ele virá',
            'Oração subordinada substantiva completiva nominal',
            [
                'Oração subordinada substantiva objetiva indireta',
                'Oração subordinada adjetiva restritiva',
                'Oração subordinada adverbial causal',
            ]],
    ];

    /** [frase correta, tres frases com erro do mesmo tipo, tipo de erro] */
    private const CONCORDANCIA = [
        ['Fazem-se consertos de aparelhos eletrônicos.',
            ['Faz-se consertos de aparelhos eletrônicos.',
             'Fazem-se conserto de aparelhos eletrônicos.',
             'Faz-se consertam aparelhos eletrônicos.'],
            'concordância verbal na voz passiva sintética'],
        ['Houve muitos problemas durante a apresentação.',
            ['Houveram muitos problemas durante a apresentação.',
             'Haviam muitos problemas durante a apresentação.',
             'Houveram muito problema durante a apresentação.'],
            'concordância do verbo haver no sentido de existir'],
        ['Faz dois anos que ele se formou.',
            ['Fazem dois anos que ele se formou.',
             'Fazem dois ano que ele se formou.',
             'Fazem-se dois anos que ele se formou.'],
            'concordância do verbo fazer indicando tempo decorrido'],
        ['Existem várias soluções para o problema.',
            ['Existe várias soluções para o problema.',
             'Existem várias solução para o problema.',
             'Existe várias solução para o problema.'],
            'concordância do verbo existir com o sujeito'],
        ['Os documentos anexos seguem por e-mail.',
            ['Os documentos anexo seguem por e-mail.',
             'Os documento anexos seguem por e-mail.',
             'Os documentos em anexos seguem por e-mail.'],
            'concordância nominal do adjetivo anexo'],
        ['É proibida a entrada de pessoas não autorizadas.',
            ['É proibido a entrada de pessoas não autorizadas.',
             'É proibidas a entrada de pessoas não autorizadas.',
             'São proibida a entrada de pessoas não autorizadas.'],
            'concordância nominal com o artigo definido'],
        ['Mais de um candidato se inscreveu no processo.',
            ['Mais de um candidato se inscreveram no processo.',
             'Mais de um candidatos se inscreveu no processo.',
             'Mais de um candidato se inscreveram nos processos.'],
            'concordância verbal com a expressão mais de um'],
        ['A maioria dos alunos entregou o trabalho no prazo.',
            ['A maioria dos alunos entregaram o trabalho no prazo.',
             'A maioria dos aluno entregou o trabalho no prazo.',
             'As maioria dos alunos entregou o trabalho no prazo.'],
            'concordância com expressão partitiva'],
        ['Bastantes candidatos compareceram à prova.',
            ['Bastante candidatos compareceram à prova.',
             'Bastantes candidato compareceram à prova.',
             'Bastante candidato compareceram à prova.'],
            'concordância de bastante como pronome indefinido'],
        ['Ela mesma resolveu a questão.',
            ['Ela mesmo resolveu a questão.',
             'Elas mesma resolveram a questão.',
             'Ela mesmos resolveu a questão.'],
            'concordância nominal do pronome mesmo'],
        ['Vossa Excelência foi convidado a se pronunciar.',
            ['Vossa Excelência fostes convidada a vos pronunciar.',
             'Vossa Excelência foi convidada a te pronunciar.',
             'Vossa Excelência fostes convidado a se pronunciar.'],
            'concordância com pronome de tratamento'],
        ['Prefiro caminhar a correr.',
            ['Prefiro mais caminhar do que correr.',
             'Prefiro caminhar do que correr.',
             'Prefiro mais caminhar a correr.'],
            'regência do verbo preferir'],
        ['Assisti ao filme na sessão da tarde.',
            ['Assisti o filme na sessão da tarde.',
             'Assisti no filme na sessão da tarde.',
             'Assisti do filme na sessão da tarde.'],
            'regência do verbo assistir no sentido de ver'],
        ['Ele obedeceu às normas do regulamento.',
            ['Ele obedeceu as normas do regulamento.',
             'Ele obedeceu nas normas do regulamento.',
             'Ele obedeceu das normas do regulamento.'],
            'regência do verbo obedecer'],
        ['Aspiro ao cargo de coordenador.',
            ['Aspiro o cargo de coordenador.',
             'Aspiro pelo cargo de coordenador.',
             'Aspiro no cargo de coordenador.'],
            'regência do verbo aspirar no sentido de almejar'],
        ['Informei ao cliente o novo prazo.',
            ['Informei o cliente do novo prazo ao gerente.',
             'Informei com o cliente o novo prazo.',
             'Informei para ao cliente o novo prazo.'],
            'regência do verbo informar'],
        ['Ele visa ao cargo de diretor.',
            ['Ele visa o cargo de diretor com esforço e dedicação plena.',
             'Ele visa no cargo de diretor.',
             'Ele visa pelo cargo de diretor.'],
            'regência do verbo visar no sentido de pretender'],
        ['Cheguei ao aeroporto com duas horas de antecedência.',
            ['Cheguei no aeroporto com duas horas de antecedência.',
             'Cheguei em o aeroporto com duas horas de antecedência.',
             'Cheguei até no aeroporto com duas horas de antecedência.'],
            'regência do verbo chegar'],
    ];

    /** [singular, plural correto, tres plurais incorretos] */
    private const PLURAIS = [
        ['cidadão', 'cidadãos', ['cidadães', 'cidadões', 'cidadãoes']],
        ['pão', 'pães', ['pãos', 'pões', 'pãoes']],
        ['coração', 'corações', ['coraçãos', 'corações-', 'coraçães']],
        ['cão', 'cães', ['cãos', 'cões', 'cãoes']],
        ['guarda-chuva', 'guarda-chuvas', ['guardas-chuvas', 'guardas-chuva', 'guarda-chuvais']],
        ['couve-flor', 'couves-flores', ['couve-flores', 'couves-flor', 'couve-flors']],
        ['beija-flor', 'beija-flores', ['beijas-flores', 'beija-flors', 'beijas-flor']],
        ['segunda-feira', 'segundas-feiras', ['segunda-feiras', 'segundas-feira', 'segunda-feirais']],
        ['pé-de-moleque', 'pés-de-moleque', ['pé-de-moleques', 'pés-de-moleques', 'pés-des-moleque']],
        ['girassol', 'girassóis', ['girassols', 'girassoles', 'girassóies']],
        ['funil', 'funis', ['funils', 'funies', 'funíis']],
        ['réptil', 'répteis', ['réptis', 'réptils', 'reptéis']],
        ['fóssil', 'fósseis', ['fóssis', 'fóssils', 'fosseis-']],
        ['caráter', 'caracteres', ['carácteres', 'caráteres', 'caractéres']],
        ['mal', 'males', ['mais', 'mals', 'maus']],
        ['cônsul', 'cônsules', ['cônsuis', 'cônsuls', 'consúles']],
        ['azul', 'azuis', ['azules', 'azuls', 'azúis']],
        ['troféu', 'troféus', ['troféis', 'trofés', 'troféos']],
        ['cidadã', 'cidadãs', ['cidadães', 'cidadans', 'cidadãos']],
        ['limão', 'limões', ['limãos', 'limães', 'limõis']],
    ];

    /** [palavra, classificacao quanto a silaba tonica] */
    private const TONICAS = [
        ['café', 'Oxítona'],
        ['caneta', 'Paroxítona'],
        ['lâmpada', 'Proparoxítona'],
        ['urubu', 'Oxítona'],
        ['médico', 'Proparoxítona'],
        ['janela', 'Paroxítona'],
        ['sofá', 'Oxítona'],
        ['árvore', 'Proparoxítona'],
        ['cadeira', 'Paroxítona'],
        ['caju', 'Oxítona'],
        ['número', 'Proparoxítona'],
        ['livro', 'Paroxítona'],
        ['avó', 'Oxítona'],
        ['máquina', 'Proparoxítona'],
        ['escola', 'Paroxítona'],
        ['Jesus', 'Oxítona'],
        ['fósforo', 'Proparoxítona'],
        ['problema', 'Paroxítona'],
        ['rapaz', 'Oxítona'],
        ['ônibus', 'Proparoxítona'],
        ['tesoura', 'Paroxítona'],
        ['cipó', 'Oxítona'],
    ];

    public static function topico(): string
    {
        return 'gramatica';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::classeGramatical(),
            fn () => self::funcaoSintatica(),
            fn () => self::frasesCorretas(),
            fn () => self::frasesIncorretas(),
            fn () => self::identificarTermo(),
            fn () => self::tempoVerbal(),
            fn () => self::vozVerbal(),
            fn () => self::colocacaoPronominal(),
            fn () => self::plural(),
            fn () => self::silabaTonica(),
        ], $quantidade);
    }

    private static function plural(): iterable
    {
        foreach (self::PLURAIS as $singular => $dados) {
            [$palavra, $certa, $erradas] = [$dados[0], $dados[1], $dados[2]];
            unset($singular);

            yield new Item(
                sprintf('Assinale a alternativa que apresenta o plural correto de "%s".', $palavra),
                'facil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function silabaTonica(): iterable
    {
        $classes = ['Oxítona', 'Paroxítona', 'Proparoxítona'];

        foreach (self::TONICAS as [$palavra, $certa]) {
            $erradas = array_values(array_diff($classes, [$certa]));
            $erradas[] = 'Monossílaba tônica';

            yield new Item(
                sprintf('Quanto à posição da sílaba tônica, a palavra "%s" classifica-se como:', $palavra),
                'facil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function tempoVerbal(): iterable
    {
        foreach (self::VERBOS as [$frase, $forma, $certa, $erradas]) {
            yield new Item(
                sprintf('Em "%s", a forma verbal "%s" está no:', $frase, $forma),
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function vozVerbal(): iterable
    {
        foreach (self::VOZES as [$frase, $certa, $erradas]) {
            yield new Item(
                sprintf('A oração "%s" está na:', $frase),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function colocacaoPronominal(): iterable
    {
        foreach (self::COLOCACAO as [$certa, $regra, $erradas]) {
            yield new Item(
                sprintf(
                    'Assinale a alternativa em que a colocação pronominal está correta, considerando a regra de %s.',
                    $regra
                ),
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function classeGramatical(): iterable
    {
        foreach (self::CLASSES as [$frase, $termo, $certa, $erradas]) {
            yield new Item(
                sprintf('Em "%s", a palavra "%s" classifica-se como:', $frase, $termo),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function funcaoSintatica(): iterable
    {
        foreach (self::SINTAXE as [$frase, $termo, $certa, $erradas]) {
            $pergunta = $termo === 'a oração'
                ? sprintf('Quanto ao sujeito, a oração "%s" classifica-se como:', $frase)
                : sprintf('Em "%s", o termo "%s" exerce a função de:', $frase, $termo);

            yield new Item($pergunta, 'dificil', $certa, self::distratores($certa, $erradas));
        }
    }

    private static function frasesCorretas(): iterable
    {
        foreach (self::CONCORDANCIA as [$certa, $erradas, $tipo]) {
            yield new Item(
                sprintf(
                    'Assinale a alternativa correta quanto à %s.',
                    $tipo
                ),
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function frasesIncorretas(): iterable
    {
        $total = count(self::CONCORDANCIA);

        foreach (self::CONCORDANCIA as $i => [, $erradas, $tipo]) {
            // A alternativa a assinalar e a frase incorreta; as demais sao
            // frases corretas de outros itens da base.
            $outras = [
                self::CONCORDANCIA[($i + 1) % $total][0],
                self::CONCORDANCIA[($i + 5) % $total][0],
                self::CONCORDANCIA[($i + 9) % $total][0],
            ];

            yield new Item(
                'Assinale a alternativa em que há erro de concordância ou de regência.',
                'dificil',
                $erradas[0],
                self::distratores($erradas[0], $outras)
            );
        }
    }

    /** Pergunta invertida: dada a classificacao, qual frase a exemplifica. */
    private static function identificarTermo(): iterable
    {
        $total = count(self::CLASSES);

        foreach (self::CLASSES as $i => [$frase, $termo, $certa]) {
            $outras = [];
            foreach ([1, 3, 5, 7] as $passo) {
                $vizinho = self::CLASSES[($i + $passo) % $total];
                $outras[] = sprintf('"%s", em "%s"', $vizinho[1], $vizinho[0]);
            }

            yield new Item(
                sprintf('Assinale a alternativa em que o termo destacado é um exemplo de %s.', mb_strtolower($certa)),
                'dificil',
                sprintf('"%s", em "%s"', $termo, $frase),
                self::distratores(sprintf('"%s", em "%s"', $termo, $frase), $outras)
            );
        }
    }
}
