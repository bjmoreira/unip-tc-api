<?php

namespace Database\Seeders\Banco;

/**
 * Ortografia: gerador curado.
 *
 * A base e uma lista de palavras cuja grafia foi conferida uma a uma, cada qual
 * acompanhada das grafias incorretas que de fato circulam. Nao ha invencao de
 * conteudo: a combinatoria apenas varia a forma de perguntar sobre a mesma base
 * verificada.
 *
 * Ficaram de fora, deliberadamente, os casos em que as duas grafias existem com
 * significados diferentes, como sessao e secao ou concerto e conserto. Em uma
 * questao de palavra isolada, sem contexto, nenhuma das duas seria errada, e a
 * questao nao teria gabarito defensavel.
 *
 * A quantidade de questoes deste topico e limitada pelo tamanho da lista, e nao
 * pelo codigo. Ampliar o topico significa ampliar a lista.
 */
final class Ortografia extends Gerador
{
    /** [grafia correta, [tres grafias incorretas]] */
    private const PALAVRAS = [
        ['exceção', ['excessão', 'esceção', 'excessao']],
        ['excesso', ['escesso', 'exceço', 'ecesso']],
        ['ascensão', ['ascenção', 'acensão', 'assensão']],
        ['pretensão', ['pretenção', 'pertensão', 'pertenção']],
        ['obsessão', ['obceção', 'obcessão', 'obseção']],
        ['discussão', ['discução', 'dicussão', 'discusão']],
        ['concessão', ['conceção', 'consessão', 'concenção']],
        ['privilégio', ['previlégio', 'previlegio', 'privelégio']],
        ['beneficente', ['beneficiente', 'benificente', 'benificiente']],
        ['meteorologia', ['metereologia', 'meteriologia', 'metiorologia']],
        ['empecilho', ['impecilho', 'empecilio', 'impesilho']],
        ['cabeleireiro', ['cabelereiro', 'cabeleleiro', 'cabelereito']],
        ['mendigo', ['mendingo', 'mindigo', 'mendingu']],
        ['beneficência', ['beneficiência', 'benificência', 'benificiência']],
        ['rubrica', ['rúbrica', 'rubríca', 'ruprica']],
        ['gratuito', ['gratuíto', 'grautito', 'gratüito']],
        ['quiser', ['quizer', 'quiezer', 'quizér']],
        ['fizesse', ['fizese', 'fisesse', 'fizeçe']],
        ['ansioso', ['anscioso', 'ancioso', 'anssioso']],
        ['excursão', ['excurção', 'escursão', 'excurssão']],
        ['espontâneo', ['expontâneo', 'espontaneo', 'expontaneo']],
        ['asterisco', ['asterístico', 'asteristico', 'esterisco']],
        ['bandeja', ['bandeija', 'bandêja', 'bandegia']],
        ['berinjela', ['beringela', 'berinjéla', 'beringéla']],
        ['cafajeste', ['cafageste', 'cafajéste', 'cafagéste']],
        ['caranguejo', ['carangueijo', 'carangejo', 'caranguêjo']],
        ['disenteria', ['desinteria', 'dizenteria', 'disinteria']],
        ['entretido', ['entretenido', 'entertido', 'entretído']],
        ['frustrado', ['frustado', 'fustrado', 'frusttado']],
        ['lagarto', ['largato', 'lagardo', 'largardo']],
        ['mortadela', ['mortandela', 'mortadéla', 'mortandéla']],
        ['reivindicar', ['reinvindicar', 'reivendicar', 'reinvidicar']],
        ['supérfluo', ['supérfulo', 'superfluo', 'supurfluo']],
        ['umidade', ['humidade', 'úmidade', 'humídade']],
        ['xícara', ['chícara', 'xicara', 'chicara']],
        ['enxergar', ['enchergar', 'inchergar', 'enxerguar']],
        ['enxaqueca', ['enchaqueca', 'inxaqueca', 'enxaquéca']],
        ['exceto', ['exeto', 'esceto', 'excetu']],
        ['jeito', ['geito', 'jêito', 'gêito']],
        ['hesitar', ['ezitar', 'esitar', 'hezitar']],
        ['ojeriza', ['ogeriza', 'ojerisa', 'ogerisa']],
        ['paralisar', ['paralizar', 'paralisár', 'paralizár']],
        ['pesquisar', ['pesquizar', 'pesquisár', 'pesquizár']],
        ['salsicha', ['salchicha', 'salsixa', 'salchixa']],
        ['tigela', ['tijela', 'tigéla', 'tijéla']],
        ['adivinhar', ['advinhar', 'adevinhar', 'aidivinhar']],
        ['aterrissagem', ['aterrizagem', 'aterrisagem', 'aterrissajem']],
        ['cerimônia', ['ceremônia', 'cerimonia', 'ceremonia']],
        ['concluído', ['concluido', 'conclüído', 'conclído']],
        ['consciência', ['conciência', 'consiência', 'conssciência']],
        ['corrimão', ['currimão', 'corrimao', 'corimão']],
        ['cuspir', ['cospir', 'cuspír', 'cusprir']],
        ['despender', ['dispender', 'despendêr', 'dispendêr']],
        ['displicente', ['desplicente', 'displissente', 'desplissente']],
        ['exercício', ['execício', 'exercicio', 'ezercício']],
        ['chuchu', ['xuxu', 'chuxu', 'xuchu']],
        ['piscina', ['pissina', 'pisina', 'pissína']],
        ['seringa', ['ceringa', 'siringa', 'ceringua']],
        ['tábua', ['tabúa', 'tabua', 'táboa']],
        ['irrequieto', ['irriquieto', 'irrequeto', 'irriqueto']],
        ['manteigueira', ['manteguera', 'mantegueira', 'manteiguêra']],
        ['ansiedade', ['anciedade', 'ansiedáde', 'anssiedade']],
        ['catequizar', ['catequisar', 'categuizar', 'catekizar']],
        ['analisar', ['analizar', 'annalisar', 'analisár']],
        ['improvisar', ['improvizar', 'inprovisar', 'improvisár']],
        ['pretérito', ['preterito', 'pretérído', 'pertérito']],
        ['ritmo', ['rítmo', 'ritimo', 'rítimo']],
        ['sobrancelha', ['sombracelha', 'sobranselha', 'sombranselha']],
        ['calcanhar', ['caucanhar', 'carcanhar', 'calcanhá']],
        ['bicarbonato', ['bicabornato', 'bicabonato', 'bicarbornato']],
        ['prostrado', ['prostado', 'prostrádo', 'postrado']],
        ['inclusive', ['inclusíve', 'incluzive', 'inclussive']],
        ['prazerosa', ['praserosa', 'prazeroza', 'praseroza']],
        ['através', ['atravez', 'atrávez', 'atravéz']],
    ];

    /** [frase com lacuna, forma correta, tres formas incorretas] */
    private const ESCOLHAS = [
        ['Ele se comportou muito ___ na reunião de ontem.', 'mal', ['mau', 'máu', 'mall']],
        ['Aquele foi um ___ momento para pedir aumento.', 'mau', ['mal', 'máu', 'maul']],
        ['O aluno tem um ___ desempenho em provas orais.', 'mau', ['mal', 'máu', 'mall']],
        ['Ela dormiu ___ por causa do barulho da rua.', 'mal', ['mau', 'máu', 'mao']],
        ['O tempo estava ___ na manhã do embarque.', 'mau', ['mal', 'máu', 'maul']],
        ['O aparelho funcionou ___ desde o primeiro dia.', 'mal', ['mau', 'máu', 'mall']],
        ['Estudei bastante, ___ não consegui a nota que queria.', 'mas', ['mais', 'más', 'maz']],
        ['Preciso de ___ tempo para terminar o relatório.', 'mais', ['mas', 'más', 'maiz']],
        ['Ele trabalha ___ do que todos os colegas.', 'mais', ['mas', 'más', 'maz']],
        ['Tentei avisar, ___ o telefone estava desligado.', 'mas', ['mais', 'más', 'maiz']],
        ['___ dez anos que não visito aquela cidade.', 'Há', ['A', 'À', 'Ha']],
        ['A prova será daqui ___ duas semanas.', 'a', ['há', 'à', 'ha']],
        ['___ muitos motivos para comemorar o resultado.', 'Há', ['A', 'À', 'Ha']],
        ['O prazo termina daqui ___ três dias úteis.', 'a', ['há', 'à', 'ha']],
        ['___ você foi ontem depois da aula?', 'Aonde', ['Onde', 'Aondi', 'A onde']],
        ['___ fica a biblioteca do campus?', 'Onde', ['Aonde', 'Ondi', 'A onde']],
        ['Não sei ___ ele guardou as chaves do carro.', 'onde', ['aonde', 'ondi', 'a onde']],
        ['___ iremos depois da formatura?', 'Aonde', ['Onde', 'Aondi', 'A onde']],
        ['___ você não estudou para a prova?', 'Por que', ['Porque', 'Por quê', 'Porquê']],
        ['Não fui à festa ___ estava resfriado.', 'porque', ['por que', 'por quê', 'porquê']],
        ['Ele saiu mais cedo e ninguém sabe o ___.', 'porquê', ['porque', 'por que', 'por quê']],
        ['Você não vai responder? Por ___?', 'quê', ['que', 'quéu', 'quê,']],
        ['Não sei ___ ele desistiu do curso.', 'por que', ['porque', 'por quê', 'porquê']],
        ['___ chova, o jogo acontece normalmente.', 'Se não', ['Senão', 'Se-não', 'Sinão']],
        ['Estude agora, ___ vai se arrepender depois.', 'senão', ['se não', 'se-não', 'sinão']],
        ['___ houver quórum, a reunião será adiada.', 'Se não', ['Senão', 'Se-não', 'Sinão']],
        ['Ele viajou ___ de descansar por alguns dias.', 'a fim', ['afim', 'á fim', 'a-fim']],
        ['Somos pessoas de gostos ___ em relação a música.', 'afins', ['a fins', 'á fins', 'a-fins']],
        ['Se eu ___ o caminho certo, chegaria mais cedo.', 'soubesse', ['sabesse', 'soubese', 'saubesse']],
        ['Quando eles ___ o trabalho, avisem a coordenação.', 'entregarem', ['entregar', 'entregarão', 'entregarém']],
        ['O professor pediu que nós ___ o exercício em casa.', 'refizéssemos', ['refazêssemos', 'refizessemos', 'refazíamos']],
        ['Se você ___ mais cedo, encontraria a sala aberta.', 'viesse', ['vinhesse', 'vier', 'viesse-se']],
        ['Não ___ dúvidas sobre o resultado do exame.', 'há', ['a', 'à', 'ha']],
        ['Ele se referiu ___ questão que discutimos ontem.', 'à', ['a', 'há', 'á']],
        ['Entreguei o material ___ coordenadora do curso.', 'à', ['a', 'há', 'á']],
        ['Começamos a trabalhar ___ partir das oito horas.', 'a', ['à', 'há', 'á']],
        ['Ela assistiu ___ apresentação do grupo de teatro.', 'à', ['a', 'há', 'á']],
    ];

    /** [forma acentuada correta, tres formas incorretas, regra] */
    private const ACENTOS = [
        ['saída', ['saida', 'saída-', 'saidá'], 'hiato tônico'],
        ['país', ['pais', 'paíz', 'paiz'], 'hiato tônico'],
        ['você', ['voce', 'vocé', 'você-'], 'oxítona terminada em e'],
        ['também', ['tambem', 'tambêm', 'tãbem'], 'oxítona terminada em em'],
        ['história', ['historia', 'histôria', 'hisctória'], 'paroxítona terminada em ditongo'],
        ['médico', ['medico', 'mêdico', 'medíco'], 'proparoxítona'],
        ['fácil', ['facil', 'fâcil', 'facíl'], 'paroxítona terminada em l'],
        ['órgão', ['orgao', 'orgão', 'órgao'], 'paroxítona terminada em ão'],
        ['júri', ['juri', 'jurí', 'jûri'], 'paroxítona terminada em i'],
        ['álbum', ['album', 'albúm', 'âlbum'], 'paroxítona terminada em um'],
        ['saúde', ['saude', 'saúdi', 'saudé'], 'u tônico após vogal'],
        ['juízo', ['juizo', 'juizó', 'juîzo'], 'i tônico após vogal'],
        ['ônibus', ['onibus', 'onibús', 'ônibús'], 'proparoxítona'],
        ['próprio', ['proprio', 'propio', 'prôprio'], 'paroxítona terminada em ditongo'],
        ['série', ['serie', 'seríe', 'sêrie'], 'paroxítona terminada em ditongo'],
        ['régua', ['regua', 'reguá', 'rêgua'], 'paroxítona terminada em ditongo'],
        ['sótão', ['sotao', 'sotão', 'sótao'], 'paroxítona terminada em ão'],
        ['pêssego', ['pessego', 'pessêgo', 'péssego'], 'proparoxítona'],
        ['câmara', ['camara', 'camára', 'cãmara'], 'proparoxítona'],
        ['tórax', ['torax', 'toráx', 'thórax'], 'paroxítona terminada em x'],
        ['bônus', ['bonus', 'bonús', 'bônús'], 'paroxítona terminada em us'],
        ['prêmio', ['premio', 'prémio', 'premío'], 'paroxítona terminada em ditongo'],
        ['relógio', ['relogio', 'relógeo', 'relojio'], 'paroxítona terminada em ditongo'],
        ['ímã', ['ima', 'imã', 'íma'], 'paroxítona terminada em ã'],
        ['herói', ['heroi', 'heróí', 'heroí'], 'oxítona com ditongo aberto'],
        ['papéis', ['papeis', 'papeís', 'pápeis'], 'oxítona com ditongo aberto'],
        ['índice', ['indice', 'indíce', 'ìndice'], 'proparoxítona'],
        ['açúcar', ['acucar', 'açucar', 'açúcár'], 'paroxítona terminada em r'],
        ['caráter', ['carater', 'carácter', 'caratér'], 'paroxítona terminada em r'],
        ['nível', ['nivel', 'nivél', 'nívél'], 'paroxítona terminada em l'],
    ];

    public static function topico(): string
    {
        return 'ortografia';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::grafiaCorreta(),
            fn () => self::grafiaIncorreta(),
            fn () => self::lacuna(),
            fn () => self::acentuacao(),
        ], $quantidade);
    }

    private static function grafiaCorreta(): iterable
    {
        foreach (self::PALAVRAS as $i => [$certa, $erradas]) {
            yield new Item(
                'Assinale a alternativa em que a palavra está grafada corretamente.',
                $i % 3 === 0 ? 'facil' : 'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function grafiaIncorreta(): iterable
    {
        $total = count(self::PALAVRAS);

        foreach (self::PALAVRAS as $i => [, $erradas]) {
            // A alternativa a assinalar e a grafia errada; as demais sao
            // palavras corretas retiradas de outras entradas da lista.
            $outras = [
                self::PALAVRAS[($i + 1) % $total][0],
                self::PALAVRAS[($i + 2) % $total][0],
                self::PALAVRAS[($i + 3) % $total][0],
            ];

            yield new Item(
                'Assinale a alternativa em que a palavra apresenta erro de grafia.',
                'medio',
                $erradas[0],
                self::distratores($erradas[0], $outras)
            );
        }
    }

    private static function lacuna(): iterable
    {
        foreach (self::ESCOLHAS as [$frase, $certa, $erradas]) {
            yield new Item(
                sprintf('Complete a lacuna corretamente: "%s"', $frase),
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function acentuacao(): iterable
    {
        foreach (self::ACENTOS as [$certa, $erradas, $regra]) {
            yield new Item(
                sprintf(
                    'Assinale a alternativa em que a acentuação está correta, considerando a regra da %s.',
                    $regra
                ),
                'medio',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }
}
