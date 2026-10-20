<?php

namespace Database\Seeders\Banco;

/**
 * Interpretacao de texto: gerador curado.
 *
 * Este e o topico em que a geracao automatica menos ajuda. Interpretar exige um
 * texto, e um texto nao se parametriza: cada trecho precisou ser escrito e cada
 * pergunta, formulada sobre aquele trecho especifico. O codigo aqui apenas
 * organiza e apresenta o que foi escrito.
 *
 * Os trechos sao curtos de proposito. Em uma prova aplicada na tela, textos
 * longos deslocam a dificuldade da compreensao para a paciencia de leitura, o
 * que contaminaria a estimativa de proficiencia do topico.
 *
 * As perguntas evitam pedir opiniao ou julgamento de valor. Todas tem resposta
 * verificavel no proprio trecho, seja por leitura literal, seja por inferencia
 * que o texto sustenta.
 */
final class Interpretacao extends Gerador
{
    /**
     * [trecho, pergunta literal, resposta, tres erradas,
     *          pergunta de inferencia, resposta, tres erradas]
     */
    private const TEXTOS = [
        [
            'A biblioteca fechava às dezoito horas, mas Helena continuava ali, às dezoito e quarenta, com o mesmo livro aberto na mesma página. O bibliotecário passou por ela três vezes e, nas três, desistiu de avisar.',
            'De acordo com o trecho, quanto tempo Helena permaneceu na biblioteca após o horário de fechamento?',
            'Quarenta minutos', ['Três horas', 'Dezoito minutos', 'Uma hora e quarenta minutos'],
            'A atitude do bibliotecário sugere que ele:',
            'Percebeu que Helena não estava em condições de ser interrompida',
            ['Não notou a presença de Helena', 'Estava com pressa de ir embora', 'Desconhecia o horário de fechamento'],
        ],
        [
            'O relatório apontava queda de vinte por cento nas vendas. O diretor leu, agradeceu à equipe pelo trabalho e pediu que a apresentação fosse refeita com outros gráficos.',
            'Segundo o trecho, qual foi a variação registrada nas vendas?',
            'Queda de vinte por cento', ['Alta de vinte por cento', 'Estabilidade', 'Queda de dois por cento'],
            'O pedido do diretor ao final do trecho indica que ele:',
            'Não estava satisfeito com a forma como o dado foi apresentado',
            ['Discordava do cálculo feito pela equipe', 'Pretendia demitir a equipe', 'Havia entendido mal o relatório'],
        ],
        [
            'Chovia desde a madrugada. Ainda assim, às sete em ponto, o senhor Aniceto abriu a banca de jornais, arrumou as revistas na ordem de sempre e sentou-se para esperar clientes que ele sabia que não viriam.',
            'A que horas o senhor Aniceto abriu a banca?',
            'Às sete em ponto', ['Na madrugada', 'Às oito horas', 'Ao meio-dia'],
            'A expressão "que ele sabia que não viriam" revela que o personagem:',
            'Mantinha a rotina mesmo sem esperar resultado',
            ['Estava confiante em um dia movimentado', 'Desconhecia o efeito da chuva', 'Pretendia fechar a banca em seguida'],
        ],
        [
            'O aplicativo prometia organizar a rotina em cinco minutos por dia. Depois de duas semanas, Marcos havia passado quarenta minutos configurando lembretes e nenhum minuto cumprindo as tarefas que eles anunciavam.',
            'Quanto tempo Marcos dedicou à configuração do aplicativo?',
            'Quarenta minutos', ['Cinco minutos por dia', 'Duas semanas inteiras', 'Nenhum minuto'],
            'O trecho constrói uma crítica baseada principalmente:',
            'No contraste entre a promessa da ferramenta e o seu uso real',
            ['Na falha técnica do aplicativo', 'No preço cobrado pelo serviço', 'Na falta de tempo de Marcos'],
        ],
        [
            'A professora devolveu as provas em ordem crescente de nota. Quando restava apenas uma folha em sua mão, a turma inteira já sabia de quem era, e ninguém olhou para trás.',
            'Em que ordem as provas foram devolvidas?',
            'Da menor para a maior nota', ['Da maior para a menor nota', 'Em ordem alfabética', 'Aleatoriamente'],
            'O fato de ninguém olhar para trás sugere:',
            'Constrangimento coletivo diante da situação',
            ['Desinteresse pelas notas', 'Alegria com o resultado', 'Distração da turma'],
        ],
        [
            'O edital tinha quarenta páginas. Na página trinta e sete, em nota de rodapé, informava que a inscrição só seria válida mediante entrega presencial dos documentos, na capital, em dia útil.',
            'Onde estava a informação sobre a entrega presencial?',
            'Em nota de rodapé, na página trinta e sete',
            ['No título do edital', 'Na primeira página', 'Em destaque na capa'],
            'A posição em que a informação aparece no edital indica que:',
            'Uma exigência decisiva ficou pouco visível para o candidato',
            ['O edital era claro e bem organizado', 'A informação era irrelevante', 'Havia erro de digitação no documento'],
        ],
        [
            'Durante toda a reunião, Cláudia anotou. Não fez uma pergunta, não levantou objeção, não concordou em voz alta. Ao final, entregou ao coordenador duas folhas com dezessete pontos que ninguém havia considerado.',
            'Quantos pontos Cláudia entregou ao coordenador?',
            'Dezessete', ['Duas', 'Sete', 'Nenhum'],
            'O comportamento de Cláudia na reunião mostra que o silêncio dela:',
            'Correspondia a uma observação atenta, e não a desinteresse',
            ['Indicava concordância com tudo o que foi dito', 'Revelava despreparo para o assunto', 'Era resultado de timidez sem consequência'],
        ],
        [
            'A cidade instalou cem novos bebedouros públicos. Seis meses depois, uma vistoria encontrou oitenta e dois funcionando, doze quebrados e seis que nunca chegaram a ser ligados à rede de água.',
            'Quantos bebedouros nunca foram ligados à rede de água?',
            'Seis', ['Doze', 'Oitenta e dois', 'Cem'],
            'Os dados da vistoria permitem concluir que:',
            'Parte do investimento não chegou a produzir qualquer efeito',
            ['Todos os bebedouros funcionaram como previsto', 'A vistoria foi mal conduzida', 'A cidade instalou bebedouros demais'],
        ],
        [
            'O texto do contrato dizia que o prazo era de trinta dias. Uma cláusula posterior, na mesma página, esclarecia que o prazo começava a contar apenas após a aprovação de um documento que a própria empresa levaria até sessenta dias para emitir.',
            'De quanto era o prazo previsto na primeira menção do contrato?',
            'Trinta dias', ['Sessenta dias', 'Noventa dias', 'Quinze dias'],
            'A relação entre as duas cláusulas mostra que o prazo real:',
            'Poderia ser bem maior do que o anunciado inicialmente',
            ['Era exatamente o que constava na primeira cláusula', 'Havia sido reduzido pela segunda cláusula', 'Não dependia de nenhum documento'],
        ],
        [
            'Perguntaram ao pescador se o rio havia mudado. Ele olhou para a água, coçou a barba e respondeu que o rio estava igual. Depois acrescentou que só não dava mais peixe.',
            'O que o pescador respondeu inicialmente sobre o rio?',
            'Que estava igual', ['Que havia secado', 'Que estava poluído', 'Que havia mudado muito'],
            'O acréscimo final da fala do pescador:',
            'Contradiz a afirmação que ele mesmo acabara de fazer',
            ['Confirma que nada mudou no rio', 'Muda o assunto da conversa', 'Explica por que ele parou de pescar'],
        ],
        [
            'O manual do aparelho tinha versões em oito idiomas. A versão em português ocupava duas páginas; a versão em inglês, catorze. As instruções de segurança apareciam apenas na segunda.',
            'Quantas páginas tinha a versão em português do manual?',
            'Duas', ['Catorze', 'Oito', 'Dezesseis'],
            'A diferença entre as versões representa, para o leitor brasileiro:',
            'A ausência de informações essenciais de segurança',
            ['Uma tradução mais objetiva e eficiente', 'Um erro de numeração das páginas', 'Uma vantagem na leitura do manual'],
        ],
        [
            'Todo mês, dona Lurdes separava um envelope com o dinheiro do remédio antes de qualquer outra despesa. No mês em que o preço subiu, ela separou o mesmo envelope e passou a comprar meia caixa.',
            'O que dona Lurdes fazia antes de qualquer outra despesa?',
            'Separava o dinheiro do remédio', ['Pagava o aluguel', 'Comprava alimentos', 'Guardava dinheiro na poupança'],
            'A decisão de dona Lurdes diante do aumento indica que ela:',
            'Ajustou o consumo porque o orçamento não podia crescer',
            ['Passou a economizar mais do que antes', 'Deixou de tomar o remédio', 'Recebeu um desconto na farmácia'],
        ],
        [
            'A empresa divulgou que noventa e oito por cento dos clientes estavam satisfeitos. A pesquisa havia sido enviada por e-mail a todos os clientes, e respondida por quarenta e um deles, de um total de nove mil.',
            'Quantos clientes responderam à pesquisa?',
            'Quarenta e um', ['Nove mil', 'Noventa e oito', 'Todos os clientes'],
            'O dado divulgado pela empresa é frágil porque:',
            'A amostra que respondeu é pequena demais diante do total',
            ['A pergunta feita era muito longa', 'O e-mail não foi entregue a ninguém', 'Os clientes responderam duas vezes'],
        ],
        [
            'O ônibus das seis passava às seis. Quando a linha foi reformulada, passou a haver um ônibus às cinco e quarenta e outro às seis e vinte, e o folheto anunciava aumento de frequência.',
            'Em que horários passou a circular o ônibus após a reformulação?',
            'Às cinco e quarenta e às seis e vinte',
            ['Apenas às seis', 'Às seis e às sete', 'Às cinco e às seis'],
            'Para quem precisava embarcar exatamente às seis, a mudança significou:',
            'Ter de sair mais cedo ou chegar mais tarde',
            ['Uma redução no tempo de espera', 'Nenhuma alteração prática', 'A supressão completa da linha'],
        ],
        [
            'O menino aprendeu a ler aos quatro anos com as legendas dos jogos de futebol na televisão. Aos sete, na escola, foi classificado como aluno com dificuldade de leitura porque não reconhecia as palavras da cartilha.',
            'Com que material o menino aprendeu a ler?',
            'Com as legendas dos jogos de futebol',
            ['Com a cartilha da escola', 'Com livros infantis', 'Com jornais impressos'],
            'A classificação recebida na escola revela que a avaliação:',
            'Media o domínio de um material específico, e não a capacidade de ler',
            ['Estava correta, pois o menino não sabia ler', 'Levou em conta a leitura feita em casa', 'Considerou a idade do menino'],
        ],
        [
            'Anunciaram que a fila seria organizada por ordem de chegada. Às sete da manhã havia trinta pessoas. Às oito, abriram duas portas ao mesmo tempo, e quem estava na frente passou a estar no meio.',
            'Quantas pessoas havia na fila às sete da manhã?',
            'Trinta', ['Duas', 'Oito', 'Setenta'],
            'A abertura simultânea das duas portas:',
            'Desfez na prática o critério que havia sido anunciado',
            ['Reforçou a ordem de chegada', 'Reduziu o número de pessoas na fila', 'Foi combinada previamente com os presentes'],
        ],
        [
            'O médico explicou o diagnóstico em quatro minutos, usando sete termos técnicos. Ao final, perguntou se havia dúvidas. O paciente disse que não e saiu sem saber se poderia continuar trabalhando.',
            'Quanto tempo durou a explicação do médico?',
            'Quatro minutos', ['Sete minutos', 'Quatro horas', 'Meia hora'],
            'A resposta do paciente à pergunta do médico mostra que:',
            'Dizer que não há dúvidas nem sempre significa ter compreendido',
            ['O paciente entendeu toda a explicação', 'A consulta foi bem conduzida', 'O médico foi excessivamente detalhista'],
        ],
        [
            'A placa dizia: proibido pisar na grama. Ao lado, um caminho de terra batida atravessava o gramado de ponta a ponta, aberto pelos próprios pedestres ao longo de anos.',
            'O que estava escrito na placa?',
            'Proibido pisar na grama', ['Cuidado com a grama molhada', 'Caminho em obras', 'Passagem liberada'],
            'A existência do caminho de terra ao lado da placa indica que:',
            'A proibição não correspondia ao trajeto de que as pessoas precisavam',
            ['Os pedestres desconheciam a placa', 'A grama havia morrido naturalmente', 'A placa era recente'],
        ],
        [
            'A prefeitura anunciou a construção de uma ciclovia de doze quilômetros. Executou três, sinalizou um e, no relatório de gestão, registrou a obra como concluída.',
            'Quantos quilômetros de ciclovia foram efetivamente executados?',
            'Três', ['Doze', 'Um', 'Nenhum'],
            'O registro no relatório de gestão caracteriza:',
            'Uma divergência entre o que foi feito e o que foi informado',
            ['Um erro de cálculo dos engenheiros', 'O cumprimento integral do projeto', 'Um adiamento formal da obra'],
        ],
        [
            'Meu avô guardava pregos tortos em uma lata. Dizia que endireitar prego dava trabalho, mas comprar prego novo dava trabalho também, e o dele já estava ali.',
            'Onde o avô guardava os pregos tortos?',
            'Em uma lata', ['Em uma caixa de madeira', 'Em um saco de pano', 'Na bancada da oficina'],
            'O raciocínio do avô se apoia principalmente na ideia de que:',
            'O que já se tem à mão vale o esforço de reaproveitar',
            ['Pregos novos são de qualidade inferior', 'Endireitar pregos é uma tarefa fácil', 'Comprar pregos é impossível'],
        ],
        [
            'O aviso no elevador dizia que a manutenção ocorreria na terça. Estava colado ali havia sete meses, com a data já apagada pelo sol, e o elevador continuava parando entre andares.',
            'Há quanto tempo o aviso estava colado no elevador?',
            'Sete meses', ['Sete dias', 'Uma terça-feira', 'Três anos'],
            'A permanência do aviso no elevador sugere que:',
            'A manutenção anunciada nunca foi realizada',
            ['O elevador havia sido consertado', 'O aviso era recente', 'Os moradores retiraram o aviso'],
        ],
        [
            'Na entrevista de emprego, perguntaram a Renata onde ela se via em cinco anos. Ela respondeu que dependeria do que a empresa oferecesse nos dois primeiros. Não foi chamada.',
            'Qual pergunta foi feita a Renata na entrevista?',
            'Onde ela se via em cinco anos',
            ['Qual era sua pretensão salarial', 'Por que havia saído do emprego anterior', 'Quais eram seus defeitos'],
            'A resposta de Renata pode ser entendida como:',
            'Uma inversão da expectativa de que apenas o candidato seja avaliado',
            ['Uma demonstração de despreparo para a vaga', 'Uma recusa a responder à pergunta', 'Uma concordância com o entrevistador'],
        ],
        [
            'Havia dois caixas abertos e uma fila só. Alguém sugeriu dividir a fila em duas, para andar mais rápido. Em dez minutos, uma das filas tinha quatro pessoas e a outra, quinze.',
            'Quantas pessoas havia na fila menor após dez minutos?',
            'Quatro', ['Quinze', 'Dez', 'Duas'],
            'O resultado descrito no trecho mostra que a sugestão:',
            'Piorou a distribuição que a fila única garantia',
            ['Acelerou o atendimento das duas filas', 'Foi ignorada pelas pessoas', 'Reduziu o número de clientes'],
        ],
        [
            'O curso prometia certificado reconhecido. No rodapé do site, em letra miúda, informava que o reconhecimento era concedido pela própria instituição que oferecia o curso.',
            'Onde estava a informação sobre quem concedia o reconhecimento?',
            'No rodapé do site, em letra miúda',
            ['No título da página', 'No contrato assinado', 'No certificado impresso'],
            'A informação do rodapé esvazia a promessa porque:',
            'Quem reconhece o certificado é a própria instituição interessada',
            ['O curso não emite certificado', 'O certificado tem validade curta', 'O site estava desatualizado'],
        ],
        [
            'Durante a enchente, o rapaz atravessou a rua alagada carregando a cadeira de rodas da vizinha e depois voltou para buscá-la. Perguntado sobre o gesto, disse que a cadeira era mais pesada.',
            'O que o rapaz carregou primeiro pela rua alagada?',
            'A cadeira de rodas', ['A vizinha', 'Os móveis da casa', 'Uma corda'],
            'A resposta do rapaz sobre o gesto revela:',
            'Uma explicação prática para algo que os outros veriam como heroísmo',
            ['Arrependimento por ter ajudado', 'Desprezo pela vizinha', 'Insegurança sobre a própria força'],
        ],
        [
            'A escola comprou trinta tablets para a sala de informática. Guardou-os no armário para não estragarem. Ao fim do ano letivo, as baterias haviam inchado e nenhum aluno os havia usado.',
            'Quantos tablets a escola comprou?',
            'Trinta', ['Três', 'Trezentos', 'Nenhum'],
            'O desfecho descrito no trecho decorre diretamente:',
            'Da decisão de proteger os equipamentos em vez de usá-los',
            ['Da má qualidade dos tablets', 'Do excesso de uso pelos alunos', 'Da falta de energia elétrica'],
        ],
        [
            'O time perdeu por cinco a zero. No vestiário, o técnico elogiou a entrega dos jogadores, criticou o gramado e não mencionou nenhuma das cinco jogadas que originaram os gols.',
            'Qual foi o placar da partida?',
            'Cinco a zero', ['Zero a zero', 'Cinco a um', 'Três a zero'],
            'A postura do técnico no vestiário indica que ele:',
            'Evitou tratar das causas específicas da derrota',
            ['Analisou detalhadamente cada gol sofrido', 'Responsabilizou os jogadores pelo resultado', 'Comemorou o desempenho da equipe'],
        ],
        [
            'A receita da avó pedia uma xícara de açúcar. A neta anotou tudo, mediu tudo, seguiu tudo, e o bolo saiu diferente. A avó provou e disse que faltava a mão.',
            'Quanto de açúcar a receita pedia?',
            'Uma xícara', ['Duas xícaras', 'Meia xícara', 'Uma colher'],
            'A expressão "faltava a mão", usada pela avó, refere-se a:',
            'Um conhecimento prático que a receita escrita não transmite',
            ['Um ingrediente esquecido na lista', 'Um erro de medida da neta', 'A falta de força para bater a massa'],
        ],
        [
            'O contrato de estágio previa vinte horas semanais. No terceiro mês, o estagiário cumpria trinta e cinco, respondia por dois setores e assinava documentos no lugar do supervisor.',
            'Quantas horas semanais o contrato previa?',
            'Vinte', ['Trinta e cinco', 'Três', 'Quarenta'],
            'A situação descrita no trecho caracteriza:',
            'O desvirtuamento da finalidade formativa do estágio',
            ['Uma promoção do estagiário', 'O cumprimento correto do contrato', 'Uma redução de jornada'],
        ],
        [
            'A pesquisa mostrava que sessenta por cento dos entrevistados aprovavam a medida. A manchete dizia que a população apoiava a proposta. O texto da reportagem só citava a margem de erro no último parágrafo.',
            'Qual percentual de entrevistados aprovava a medida?',
            'Sessenta por cento', ['Quarenta por cento', 'Cem por cento', 'Seis por cento'],
            'A posição da margem de erro na reportagem indica que:',
            'Um dado necessário à interpretação recebeu pouco destaque',
            ['A pesquisa não tinha margem de erro', 'A manchete reproduzia fielmente o dado', 'O texto era excessivamente técnico'],
        ],
        [
            'O idoso levou quarenta minutos para preencher o formulário digital. Havia oito campos obrigatórios, um código enviado por mensagem e um botão de confirmação que só aparecia depois de rolar a página até o fim.',
            'Quantos campos obrigatórios havia no formulário?',
            'Oito', ['Quarenta', 'Um', 'Dezoito'],
            'O trecho aponta um problema de:',
            'Acessibilidade do serviço digital para o usuário',
            ['Falta de interesse do idoso', 'Erro de programação no envio do código', 'Excesso de informação verdadeira'],
        ],
        [
            'A empresa anunciou home office definitivo. Seis meses depois, convocou reuniões presenciais às segundas, quartas e sextas, e manteve o anúncio no site de vagas.',
            'Em quais dias a empresa passou a convocar reuniões presenciais?',
            'Segundas, quartas e sextas', ['Todos os dias', 'Apenas às segundas', 'Terças e quintas'],
            'A manutenção do anúncio no site de vagas sugere:',
            'Que a comunicação externa não acompanhou a prática interna',
            ['Que o home office continuava integral', 'Que o site havia sido desativado', 'Que os funcionários pediram as reuniões'],
        ],
    ];

    /** [frase, palavra, sentido no contexto, tres sentidos incorretos] */
    private const VOCABULARIO = [
        ['O argumento do autor é bastante frágil e não se sustenta diante dos dados.', 'frágil',
            'Pouco consistente', ['Quebradiço', 'Delicado ao toque', 'Adoecido']],
        ['A empresa adotou uma postura cautelosa diante da crise.', 'cautelosa',
            'Prudente', ['Agressiva', 'Indiferente', 'Apressada']],
        ['O texto aborda a questão de forma superficial.', 'superficial',
            'Sem aprofundamento', ['Referente à superfície do corpo', 'Cuidadosa', 'Extensa']],
        ['Ele foi categórico ao negar a acusação.', 'categórico',
            'Absolutamente firme', ['Hesitante', 'Educado', 'Confuso']],
        ['A medida teve um efeito paliativo sobre o problema.', 'paliativo',
            'Que alivia sem resolver', ['Definitivo', 'Prejudicial', 'Preventivo']],
        ['O relato do funcionário foi corroborado por outras testemunhas.', 'corroborado',
            'Confirmado', ['Contestado', 'Interrompido', 'Registrado']],
        ['A proposta foi rechaçada pela maioria dos presentes.', 'rechaçada',
            'Rejeitada', ['Aprovada', 'Adiada', 'Elogiada']],
        ['Trata-se de um problema recorrente naquele setor.', 'recorrente',
            'Que se repete', ['Inédito', 'Urgente', 'Insignificante']],
        ['O diretor foi lacônico ao comentar o resultado.', 'lacônico',
            'Breve no falar', ['Prolixo', 'Emocionado', 'Agressivo']],
        ['A explicação do professor foi bastante elucidativa.', 'elucidativa',
            'Esclarecedora', ['Confusa', 'Demorada', 'Divertida']],
        ['A empresa agiu de forma leviana ao divulgar o dado.', 'leviana',
            'Irresponsável', ['Cuidadosa', 'Rápida', 'Discreta']],
        ['O texto apresenta uma crítica velada ao governo.', 'velada',
            'Feita de modo indireto', ['Explícita', 'Violenta', 'Elogiosa']],
        ['O candidato demonstrou perspicácia ao interpretar a questão.', 'perspicácia',
            'Argúcia', ['Lentidão', 'Distração', 'Nervosismo']],
        ['A decisão gerou grande celeuma entre os moradores.', 'celeuma',
            'Polêmica', ['Consenso', 'Silêncio', 'Indiferença']],
        ['O escritor tem um estilo prolixo, cheio de rodeios.', 'prolixo',
            'Excessivamente extenso', ['Conciso', 'Objetivo', 'Poético']],
        ['O gesto foi interpretado como uma afronta pelos presentes.', 'afronta',
            'Ofensa', ['Homenagem', 'Brincadeira', 'Convite']],
        ['A situação exigia uma resposta pragmática, e não teórica.', 'pragmática',
            'Voltada à prática', ['Abstrata', 'Emotiva', 'Provisória']],
        ['O aluno foi contumaz nas faltas durante todo o semestre.', 'contumaz',
            'Reincidente', ['Pontual', 'Ocasional', 'Justificado']],
        ['A reforma trouxe benefícios efêmeros para a comunidade.', 'efêmeros',
            'De curta duração', ['Permanentes', 'Amplos', 'Custosos']],
        ['O jornalista foi parcimonioso ao usar adjetivos na reportagem.', 'parcimonioso',
            'Moderado', ['Exagerado', 'Descuidado', 'Criativo']],
        ['A explicação dele foi dúbia e deixou a plateia em dúvida.', 'dúbia',
            'Ambígua', ['Clara', 'Extensa', 'Divertida']],
        ['O time teve uma atuação pífia na primeira etapa.', 'pífia',
            'Muito ruim', ['Excelente', 'Regular', 'Agressiva']],
        ['A postura do gestor foi intransigente durante toda a negociação.', 'intransigente',
            'Inflexível', ['Conciliadora', 'Distraída', 'Generosa']],
        ['O aluno apresentou um argumento falacioso na redação.', 'falacioso',
            'Enganoso', ['Verdadeiro', 'Original', 'Poético']],
        ['O relatório traz dados fidedignos sobre o consumo de energia.', 'fidedignos',
            'Confiáveis', ['Duvidosos', 'Antigos', 'Escassos']],
        ['A medida se mostrou inócua diante da dimensão do problema.', 'inócua',
            'Sem efeito', ['Perigosa', 'Decisiva', 'Cara']],
        ['O acordo foi tácito entre os dois departamentos.', 'tácito',
            'Não declarado explicitamente', ['Assinado em cartório', 'Rompido', 'Divulgado à imprensa']],
        ['A empresa manteve uma conduta irrepreensível durante a auditoria.', 'irrepreensível',
            'Sem falhas', ['Suspeita', 'Confusa', 'Indiferente']],
        ['O texto é permeado por referências históricas.', 'permeado',
            'Atravessado por completo', ['Desprovido', 'Encerrado', 'Iniciado']],
        ['O candidato deu uma resposta evasiva à pergunta do jornalista.', 'evasiva',
            'Que evita o assunto', ['Direta', 'Ofensiva', 'Detalhada']],
        ['Aquele hábito é inerente à profissão que ele escolheu.', 'inerente',
            'Próprio e inseparável', ['Estranho', 'Ocasional', 'Proibido']],
        ['A decisão do juiz foi exarada na tarde de ontem.', 'exarada',
            'Registrada formalmente', ['Anulada', 'Contestada', 'Adiada']],
        ['O bairro passou por um processo de gentrificação nos últimos anos.', 'gentrificação',
            'Valorização que desloca antigos moradores',
            ['Abandono de imóveis', 'Aumento da criminalidade', 'Construção de praças']],
        ['O professor foi enfático ao repetir a orientação três vezes.', 'enfático',
            'Que ressalta com força', ['Hesitante', 'Silencioso', 'Distraído']],
        ['A proposta é exequível dentro do prazo apresentado.', 'exequível',
            'Realizável', ['Impossível', 'Onerosa', 'Ilegal']],
        ['O discurso tinha um tom conciliatório após semanas de atrito.', 'conciliatório',
            'Que busca aproximar as partes', ['Que acirra o conflito', 'Que ignora o assunto', 'Que encerra a discussão']],
        ['O comentário foi feito de forma jocosa durante a reunião.', 'jocosa',
            'Em tom de brincadeira', ['Em tom solene', 'Com agressividade', 'Com tristeza']],
        ['A empresa adotou uma política austera de gastos.', 'austera',
            'Rigorosa e contida', ['Generosa', 'Desorganizada', 'Improvisada']],
        ['Sua argumentação é dotada de rara acuidade.', 'acuidade',
            'Precisão de percepção', ['Rispidez', 'Extensão', 'Simplicidade']],
        ['O projeto foi considerado incipiente pelos avaliadores.', 'incipiente',
            'Em estágio inicial', ['Concluído', 'Excessivo', 'Consolidado']],
        ['O texto trata de um tema espinhoso para a comunidade.', 'espinhoso',
            'Difícil de abordar', ['Trivial', 'Alegre', 'Desconhecido']],
        ['A crítica dele foi contundente e mudou o rumo do debate.', 'contundente',
            'Incisiva', ['Superficial', 'Elogiosa', 'Confusa']],
        ['O relatório aponta uma tendência incontestável de crescimento.', 'incontestável',
            'Que não admite contestação', ['Duvidosa', 'Passageira', 'Prevista']],
        ['A explicação foi redundante e repetiu o que já se sabia.', 'redundante',
            'Repetitiva sem acrescentar', ['Inovadora', 'Breve', 'Técnica']],
    ];

    /** [exemplo, figura de linguagem, tres figuras incorretas] */
    private const FIGURAS = [
        ['Seus olhos são duas estrelas brilhando na noite.', 'Metáfora',
            ['Metonímia', 'Hipérbole', 'Eufemismo']],
        ['Chorei um rio de lágrimas naquela tarde.', 'Hipérbole',
            ['Eufemismo', 'Metonímia', 'Antítese']],
        ['Ele partiu desta para melhor na semana passada.', 'Eufemismo',
            ['Hipérbole', 'Ironia', 'Metáfora']],
        ['Li Machado de Assis durante as férias inteiras.', 'Metonímia',
            ['Metáfora', 'Hipérbole', 'Personificação']],
        ['O vento sussurrava segredos entre as folhas.', 'Personificação',
            ['Metáfora', 'Metonímia', 'Antítese']],
        ['Que trabalho maravilhoso: nem uma linha foi entregue no prazo.', 'Ironia',
            ['Eufemismo', 'Metáfora', 'Pleonasmo']],
        ['Era um silêncio ensurdecedor naquele auditório.', 'Paradoxo',
            ['Metonímia', 'Eufemismo', 'Personificação']],
        ['O amor é fogo que arde sem se ver.', 'Metáfora',
            ['Metonímia', 'Ironia', 'Pleonasmo']],
        ['Subi para cima da escada com muito cuidado.', 'Pleonasmo',
            ['Elipse', 'Hipérbole', 'Antítese']],
        ['Uns riam, outros choravam na mesma sala.', 'Antítese',
            ['Paradoxo', 'Metáfora', 'Eufemismo']],
        ['Na sala, apenas o retrato na parede; na cozinha, o cheiro de café.', 'Elipse',
            ['Pleonasmo', 'Metonímia', 'Hipérbole']],
        ['O tique-taque do relógio marcava a espera.', 'Onomatopeia',
            ['Aliteração', 'Metáfora', 'Personificação']],
        ['O rato roeu a roupa do rei de Roma.', 'Aliteração',
            ['Onomatopeia', 'Assonância', 'Metonímia']],
        ['Ela tem um coração de pedra para essas situações.', 'Metáfora',
            ['Metonímia', 'Personificação', 'Ironia']],
        ['Bebi a taça inteira sem perceber.', 'Metonímia',
            ['Metáfora', 'Hipérbole', 'Elipse']],
        ['Estou morrendo de fome desde as dez da manhã.', 'Hipérbole',
            ['Metonímia', 'Eufemismo', 'Antítese']],
        ['A cidade acordou cedo naquele domingo de eleição.', 'Personificação',
            ['Metonímia', 'Hipérbole', 'Pleonasmo']],
        ['Ele nos deixou na noite de ontem, cercado pela família.', 'Eufemismo',
            ['Ironia', 'Metáfora', 'Antítese']],
        ['Comprei um Picasso na feira de antiguidades.', 'Metonímia',
            ['Metáfora', 'Personificação', 'Hipérbole']],
        ['Que dia agradável: choveu granizo e faltou luz a tarde inteira.', 'Ironia',
            ['Eufemismo', 'Antítese', 'Pleonasmo']],
        ['Havia ali uma escuridão luminosa que ninguém soube explicar.', 'Paradoxo',
            ['Antítese', 'Metáfora', 'Ironia']],
        ['A vida é uma travessia sem mapa.', 'Metáfora',
            ['Metonímia', 'Personificação', 'Hipérbole']],
        ['Entre o riso e o choro, escolheu ficar calado.', 'Antítese',
            ['Paradoxo', 'Eufemismo', 'Elipse']],
        ['Ouvi o zunzum das abelhas na varanda.', 'Onomatopeia',
            ['Aliteração', 'Metáfora', 'Personificação']],
        ['Vi você ontem; ela, hoje pela manhã.', 'Elipse',
            ['Pleonasmo', 'Metonímia', 'Antítese']],
        ['Ele viu com os próprios olhos o acidente acontecer.', 'Pleonasmo',
            ['Elipse', 'Hipérbole', 'Ironia']],
        ['O sol beijava o telhado das casas ao amanhecer.', 'Personificação',
            ['Metonímia', 'Antítese', 'Paradoxo']],
        ['Toda a escola aplaudiu o resultado da olimpíada.', 'Metonímia',
            ['Metáfora', 'Personificação', 'Hipérbole']],
        ['Sem você, meu amor, eu não sou nada; com você, sou tudo.', 'Antítese',
            ['Paradoxo', 'Metonímia', 'Eufemismo']],
        ['Esperei uma eternidade na fila do atendimento.', 'Hipérbole',
            ['Eufemismo', 'Metonímia', 'Elipse']],
    ];

    /** [trecho, tipo textual ou funcao, tres alternativas incorretas] */
    private const TIPOLOGIA = [
        ['Misture a farinha com o açúcar, acrescente os ovos e leve ao forno por trinta minutos.',
            'Texto injuntivo', ['Texto narrativo', 'Texto dissertativo', 'Texto descritivo']],
        ['A casa tinha janelas azuis, telhado de barro e uma varanda estreita voltada para o rio.',
            'Texto descritivo', ['Texto injuntivo', 'Texto dissertativo', 'Texto narrativo']],
        ['Naquela manhã, ele saiu de casa, atravessou a praça e nunca mais voltou.',
            'Texto narrativo', ['Texto descritivo', 'Texto dissertativo', 'Texto injuntivo']],
        ['A ampliação do transporte público reduz o congestionamento e melhora a qualidade do ar nas cidades.',
            'Texto dissertativo-argumentativo',
            ['Texto narrativo', 'Texto descritivo', 'Texto injuntivo']],
        ['Compre já e ganhe trinta por cento de desconto na segunda unidade.',
            'Texto publicitário', ['Texto narrativo', 'Texto científico', 'Texto descritivo']],
        ['O experimento foi conduzido com trinta amostras, sob temperatura controlada de vinte graus.',
            'Texto científico', ['Texto publicitário', 'Texto narrativo', 'Texto injuntivo']],
        ['Que saudade daquele tempo em que a gente não sabia de nada!',
            'Função emotiva', ['Função referencial', 'Função apelativa', 'Função metalinguística']],
        ['Compareça ao setor responsável até sexta-feira e apresente os documentos.',
            'Função apelativa', ['Função emotiva', 'Função poética', 'Função referencial']],
        ['A reunião ocorreu na sede da empresa, às quatorze horas, com oito participantes.',
            'Função referencial', ['Função emotiva', 'Função poética', 'Função apelativa']],
        ['Substantivo é a palavra que dá nome aos seres, objetos e sentimentos.',
            'Função metalinguística', ['Função emotiva', 'Função poética', 'Função fática']],
        ['Alô? Está me ouvindo? Alô?',
            'Função fática', ['Função referencial', 'Função poética', 'Função metalinguística']],
        ['No meio do caminho tinha uma pedra, tinha uma pedra no meio do caminho.',
            'Função poética', ['Função referencial', 'Função apelativa', 'Função metalinguística']],
        ['Aperte o botão vermelho, aguarde o sinal sonoro e retire o comprovante.',
            'Texto injuntivo', ['Texto narrativo', 'Texto dissertativo', 'Texto descritivo']],
        ['Era alto, magro, de barba grisalha e andava sempre com um guarda-chuva preto.',
            'Texto descritivo', ['Texto narrativo', 'Texto injuntivo', 'Texto científico']],
        ['A leitura na infância amplia o vocabulário e favorece o desempenho escolar posterior.',
            'Texto dissertativo-argumentativo',
            ['Texto narrativo', 'Texto descritivo', 'Texto injuntivo']],
        ['Assim que ouviu o barulho, largou a xícara, correu até a porta e encontrou a rua vazia.',
            'Texto narrativo', ['Texto descritivo', 'Texto injuntivo', 'Texto dissertativo']],
        ['Últimas unidades: leve dois e pague um só até domingo.',
            'Texto publicitário', ['Texto científico', 'Texto narrativo', 'Texto descritivo']],
        ['Os resultados indicam correlação significativa entre as duas variáveis analisadas.',
            'Texto científico', ['Texto publicitário', 'Texto narrativo', 'Texto injuntivo']],
        ['Estou tão feliz que nem consigo explicar o que sinto agora.',
            'Função emotiva', ['Função referencial', 'Função metalinguística', 'Função fática']],
        ['Vote consciente. Seu voto decide o futuro da cidade.',
            'Função apelativa', ['Função referencial', 'Função emotiva', 'Função poética']],
        ['O acidente foi registrado no quilômetro quarenta e dois da rodovia, sem vítimas.',
            'Função referencial', ['Função emotiva', 'Função apelativa', 'Função poética']],
        ['Neste dicionário, o verbete traz a etimologia antes da definição.',
            'Função metalinguística', ['Função emotiva', 'Função apelativa', 'Função fática']],
        ['Então, tá? Beleza. Fechou. Até mais.',
            'Função fática', ['Função referencial', 'Função metalinguística', 'Função poética']],
        ['Bomba, boba, bomba, boba: o coração batia sem saber por quê.',
            'Função poética', ['Função referencial', 'Função apelativa', 'Função fática']],
    ];

    /** [frase com conectivo, relação de sentido, tres relações incorretas] */
    private const CONECTIVOS = [
        ['O prazo terminou, portanto a inscrição foi cancelada.', 'Conclusão',
            ['Oposição', 'Condição', 'Concessão']],
        ['Estudou bastante, mas não foi aprovado.', 'Oposição',
            ['Conclusão', 'Causa', 'Finalidade']],
        ['Saiu cedo porque temia o trânsito.', 'Causa',
            ['Consequência', 'Concessão', 'Comparação']],
        ['Correu tanto que ficou sem fôlego.', 'Consequência',
            ['Causa', 'Condição', 'Finalidade']],
        ['Se houver quórum, a votação acontece hoje.', 'Condição',
            ['Concessão', 'Conclusão', 'Tempo']],
        ['Embora estivesse cansado, terminou o relatório.', 'Concessão',
            ['Causa', 'Condição', 'Consequência']],
        ['Guardou o dinheiro a fim de comprar o material.', 'Finalidade',
            ['Causa', 'Consequência', 'Oposição']],
        ['Assim que o sinal tocou, todos se levantaram.', 'Tempo',
            ['Causa', 'Condição', 'Conclusão']],
        ['Ele fala tão bem quanto o irmão mais velho.', 'Comparação',
            ['Consequência', 'Conformidade', 'Oposição']],
        ['O trabalho foi feito conforme as normas da instituição.', 'Conformidade',
            ['Comparação', 'Finalidade', 'Causa']],
        ['Além de chegar atrasado, esqueceu os documentos.', 'Adição',
            ['Oposição', 'Alternativa', 'Conclusão']],
        ['Ou você entrega hoje, ou perde a nota.', 'Alternância',
            ['Adição', 'Conclusão', 'Causa']],
        ['Ela não apenas revisou o texto, como também refez os gráficos.', 'Adição',
            ['Oposição', 'Condição', 'Comparação']],
        ['Apesar da chuva, o evento aconteceu ao ar livre.', 'Concessão',
            ['Causa', 'Conclusão', 'Finalidade']],
        ['Chegou atrasado; consequentemente, perdeu a primeira parte da prova.', 'Consequência',
            ['Condição', 'Concessão', 'Comparação']],
        ['Não compareceu à aula, tampouco justificou a ausência.', 'Adição',
            ['Oposição', 'Conclusão', 'Alternância']],
        ['Reduziram o preço; entretanto, as vendas continuaram baixas.', 'Oposição',
            ['Conclusão', 'Causa', 'Adição']],
        ['Como estava chovendo, adiamos a caminhada.', 'Causa',
            ['Comparação', 'Conformidade', 'Condição']],
        ['Falou de modo que todos entenderam a proposta.', 'Consequência',
            ['Finalidade', 'Conformidade', 'Concessão']],
        ['Caso o prazo seja prorrogado, refaremos o orçamento.', 'Condição',
            ['Causa', 'Concessão', 'Tempo']],
        ['Ainda que discordasse, aceitou a decisão do grupo.', 'Concessão',
            ['Causa', 'Condição', 'Consequência']],
        ['Chegou mais cedo para que pudesse revisar o material.', 'Finalidade',
            ['Causa', 'Consequência', 'Tempo']],
        ['Logo que recebeu a notícia, avisou a família inteira.', 'Tempo',
            ['Causa', 'Condição', 'Conclusão']],
        ['O relatório é tão extenso quanto o do semestre anterior.', 'Comparação',
            ['Consequência', 'Conformidade', 'Adição']],
        ['Segundo o edital, as inscrições encerram na sexta.', 'Conformidade',
            ['Comparação', 'Causa', 'Condição']],
        ['Reviu o texto e, por conseguinte, corrigiu as falhas apontadas.', 'Conclusão',
            ['Oposição', 'Causa', 'Adição']],
        ['Quer chova, quer faça sol, a feira acontece no sábado.', 'Alternância',
            ['Adição', 'Concessão', 'Condição']],
        ['Desistiu do curso, visto que não conseguia conciliar os horários.', 'Causa',
            ['Consequência', 'Finalidade', 'Oposição']],
        ['Ele revisou o cálculo; no entanto, manteve a mesma conclusão.', 'Oposição',
            ['Conclusão', 'Adição', 'Conformidade']],
        ['À medida que os dias passavam, a expectativa aumentava.', 'Proporção',
            ['Comparação', 'Conformidade', 'Concessão']],
    ];

    public static function topico(): string
    {
        return 'interpretacao-de-texto';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::literal(),
            fn () => self::inferencia(),
            fn () => self::vocabulario(),
            fn () => self::figuras(),
            fn () => self::tipologia(),
            fn () => self::conectivos(),
        ], $quantidade);
    }

    private static function literal(): iterable
    {
        foreach (self::TEXTOS as [$trecho, $pergunta, $certa, $erradas]) {
            yield new Item(
                sprintf("Leia o texto a seguir.\n\n\"%s\"\n\n%s", $trecho, $pergunta),
                'facil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function inferencia(): iterable
    {
        foreach (self::TEXTOS as [$trecho, , , , $pergunta, $certa, $erradas]) {
            yield new Item(
                sprintf("Leia o texto a seguir.\n\n\"%s\"\n\n%s", $trecho, $pergunta),
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function vocabulario(): iterable
    {
        foreach (self::VOCABULARIO as [$frase, $palavra, $certa, $erradas]) {
            yield new Item(
                sprintf('Em "%s", a palavra "%s" significa, nesse contexto:', $frase, $palavra),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function figuras(): iterable
    {
        foreach (self::FIGURAS as [$exemplo, $certa, $erradas]) {
            yield new Item(
                sprintf('A figura de linguagem predominante em "%s" é:', $exemplo),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function tipologia(): iterable
    {
        foreach (self::TIPOLOGIA as [$trecho, $certa, $erradas]) {
            yield new Item(
                sprintf('O trecho "%s" caracteriza-se predominantemente como:', $trecho),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function conectivos(): iterable
    {
        foreach (self::CONECTIVOS as [$frase, $certa, $erradas]) {
            yield new Item(
                sprintf('Em "%s", o conectivo destacado estabelece uma relação de:', $frase),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }
}
