<?php

namespace Database\Seeders\Banco;

/**
 * Citologia: gerador curado.
 *
 * A base sao pares estrutura/funcao verificados um a um. As tres primeiras
 * familias exploram a mesma base em direcoes diferentes: perguntar a funcao a
 * partir da estrutura, perguntar a estrutura a partir da funcao e pedir a
 * associacao correta entre as duas. Como todos os distratores sao funcoes
 * verdadeiras de outras estruturas, o aluno que sabe metade da materia elimina
 * metade das alternativas, que e o comportamento desejado de um distrator.
 */
final class Citologia extends Gerador
{
    /** [estrutura, funcao] */
    private const ESTRUTURAS = [
        ['a mitocôndria', 'realizar a respiração celular e produzir ATP'],
        ['o ribossomo', 'realizar a síntese de proteínas'],
        ['o complexo golgiense', 'armazenar, empacotar e secretar substâncias'],
        ['o retículo endoplasmático rugoso', 'sintetizar proteínas destinadas à secreção'],
        ['o retículo endoplasmático liso', 'sintetizar lipídios e atuar na desintoxicação celular'],
        ['o lisossomo', 'realizar a digestão intracelular'],
        ['o peroxissomo', 'degradar o peróxido de hidrogênio produzido pela célula'],
        ['o cloroplasto', 'realizar a fotossíntese'],
        ['o vacúolo central', 'armazenar substâncias e manter a turgidez da célula vegetal'],
        ['o centríolo', 'organizar o fuso durante a divisão celular'],
        ['o núcleo', 'abrigar o material genético da célula'],
        ['o nucléolo', 'produzir as subunidades dos ribossomos'],
        ['a membrana plasmática', 'controlar a entrada e a saída de substâncias na célula'],
        ['a parede celular', 'dar sustentação e proteção mecânica à célula vegetal'],
        ['o citoesqueleto', 'sustentar a célula e permitir o transporte interno'],
        ['os cílios e flagelos', 'promover a movimentação da célula'],
        ['a carioteca', 'separar o material genético do citoplasma'],
        ['a cromatina', 'corresponder ao DNA associado a proteínas no núcleo'],
        ['o citoplasma', 'preencher a célula e abrigar as organelas'],
        ['o plasmídeo', 'carregar DNA circular acessório em bactérias'],
        ['o glicocálix', 'revestir externamente a membrana e participar do reconhecimento celular'],
        ['o acrossomo', 'armazenar enzimas que perfuram o envoltório do óvulo'],
        ['o RNA mensageiro', 'levar ao ribossomo a informação copiada do DNA'],
        ['o RNA transportador', 'conduzir aminoácidos até o ribossomo durante a tradução'],
        ['a difusão simples', 'permitir a passagem de substâncias a favor do gradiente, sem gasto de energia'],
        ['a osmose', 'permitir a passagem de água através de uma membrana semipermeável'],
        ['a difusão facilitada', 'transportar substâncias a favor do gradiente com auxílio de proteínas'],
        ['o transporte ativo', 'movimentar substâncias contra o gradiente, com gasto de ATP'],
        ['a fagocitose', 'englobar partículas sólidas por meio de prolongamentos da membrana'],
        ['a pinocitose', 'englobar partículas líquidas por invaginação da membrana'],
        ['a bomba de sódio e potássio', 'manter a diferença de concentração iônica entre os meios interno e externo'],
        ['a prófase', 'condensar os cromossomos e desorganizar a carioteca'],
        ['a metáfase', 'alinhar os cromossomos na placa equatorial da célula'],
        ['a anáfase', 'separar as cromátides-irmãs em direção aos polos'],
        ['a telófase', 'reorganizar os núcleos e descondensar os cromossomos'],
        ['a citocinese', 'dividir o citoplasma ao final da divisão celular'],
        ['a interfase', 'preparar a célula para a divisão, incluindo a duplicação do DNA'],
        ['a transcrição', 'produzir RNA mensageiro a partir de um molde de DNA'],
        ['a tradução', 'produzir uma cadeia de aminoácidos a partir do RNA mensageiro'],
        ['a glicólise', 'quebrar a glicose em piruvato no citosol'],
        ['o ciclo de Krebs', 'oxidar o acetil-CoA na matriz mitocondrial'],
        ['a cadeia respiratória', 'produzir a maior parte do ATP nas cristas mitocondriais'],
        ['a fermentação', 'obter energia a partir da glicose sem utilizar oxigênio'],
        ['o cromossomo', 'organizar o DNA condensado durante a divisão celular'],
        ['o gene', 'corresponder ao trecho de DNA que codifica uma característica'],
        ['a mitose', 'gerar duas células-filhas com o mesmo número de cromossomos da célula-mãe'],
        ['a meiose', 'reduzir à metade o número de cromossomos na formação dos gametas'],
        ['o meio hipertônico', 'provocar a saída de água da célula por osmose'],
        ['o meio hipotônico', 'provocar a entrada de água na célula por osmose'],
        ['a plasmólise', 'corresponder à retração do conteúdo celular pela perda de água'],
        ['o hialoplasma', 'formar a porção fluida do citoplasma'],
        ['o centrossomo', 'reunir os centríolos e organizar os microtúbulos da célula'],
        ['o vacúolo digestivo', 'formar-se pela fusão do fagossomo com o lisossomo'],
        ['a cromátide-irmã', 'corresponder a cada uma das cópias idênticas de um cromossomo duplicado'],
        ['o cariótipo', 'representar o conjunto de cromossomos característico de uma espécie'],
        ['a cariocinese', 'corresponder à divisão do núcleo durante a divisão celular'],
        ['o código genético', 'estabelecer a correspondência entre trincas de bases e aminoácidos'],
        ['o códon', 'corresponder à trinca do RNA mensageiro que especifica um aminoácido'],
        ['o anticódon', 'corresponder à trinca do RNA transportador complementar ao códon'],
        ['a apoptose', 'promover a morte celular programada'],
    ];

    /** [pergunta, resposta correta, tres respostas incorretas] */
    private const ESPECIFICAS = [
        [
            'Qual das estruturas a seguir está presente na célula vegetal e ausente na célula animal?',
            'Cloroplasto', ['Mitocôndria', 'Complexo golgiense', 'Retículo endoplasmático'],
        ],
        [
            'A parede celular é uma estrutura característica de qual tipo celular?',
            'Célula vegetal', ['Célula muscular humana', 'Neurônio', 'Hemácia'],
        ],
        [
            'Qual estrutura está ausente nas células procarióticas?',
            'Núcleo organizado', ['Ribossomo', 'Membrana plasmática', 'Citoplasma'],
        ],
        [
            'Qual das organelas a seguir NÃO é encontrada em bactérias?',
            'Mitocôndria', ['Ribossomo', 'Parede celular', 'Material genético'],
        ],
        [
            'A presença de carioteca delimitando o material genético caracteriza:',
            'A célula eucariótica', ['A célula procariótica', 'Apenas a célula vegetal', 'Apenas os vírus'],
        ],
        [
            'Bactérias são classificadas como organismos:',
            'Procariontes', ['Eucariontes unicelulares', 'Eucariontes pluricelulares', 'Acelulares'],
        ],
        [
            'A principal molécula energética utilizada pelas células é:',
            'O ATP', ['O DNA', 'A celulose', 'O colesterol'],
        ],
        [
            'O modelo que descreve a membrana plasmática como uma bicamada lipídica com proteínas móveis é chamado de:',
            'Mosaico fluido', ['Dupla hélice', 'Modelo de Watson e Crick', 'Teoria celular'],
        ],
        [
            'A membrana plasmática é composta principalmente por:',
            'Fosfolipídios e proteínas', ['Celulose e amido', 'DNA e RNA', 'Quitina e glicogênio'],
        ],
        [
            'Em uma célula que produz muitas enzimas digestivas para secreção, espera-se encontrar em grande quantidade:',
            'Retículo endoplasmático rugoso', ['Cloroplastos', 'Parede celular', 'Vacúolo central'],
        ],
        [
            'A ausência de lisossomos funcionais comprometeria diretamente:',
            'A digestão intracelular', ['A fotossíntese', 'A sustentação da parede celular', 'A produção de celulose'],
        ],
        [
            'Uma célula com grande número de mitocôndrias provavelmente apresenta:',
            'Alto consumo de energia', ['Baixa atividade metabólica', 'Ausência de núcleo', 'Capacidade de fotossíntese'],
        ],
        [
            'O material genético de uma célula eucariótica encontra-se principalmente:',
            'No núcleo', ['No complexo golgiense', 'Na parede celular', 'No vacúolo'],
        ],
        [
            'A sequência correta das fases da mitose é:',
            'Prófase, metáfase, anáfase e telófase',
            [
                'Metáfase, prófase, telófase e anáfase',
                'Anáfase, prófase, metáfase e telófase',
                'Prófase, anáfase, metáfase e telófase',
            ],
        ],
        [
            'Ao final da mitose de uma célula com 46 cromossomos, cada célula-filha apresenta:',
            '46 cromossomos', ['23 cromossomos', '92 cromossomos', '12 cromossomos'],
        ],
        [
            'Ao final da meiose de uma célula com 46 cromossomos, cada célula resultante apresenta:',
            '23 cromossomos', ['46 cromossomos', '92 cromossomos', '69 cromossomos'],
        ],
        [
            'Uma célula animal colocada em meio hipotônico tende a:',
            'Absorver água e inchar', ['Perder água e murchar', 'Manter o volume inalterado', 'Iniciar a fotossíntese'],
        ],
        [
            'Uma célula vegetal colocada em meio hipertônico sofre:',
            'Plasmólise', ['Turgência', 'Fagocitose', 'Mitose'],
        ],
        [
            'O transporte que ocorre contra o gradiente de concentração e exige gasto de energia é:',
            'O transporte ativo', ['A difusão simples', 'A osmose', 'A difusão facilitada'],
        ],
        [
            'A síntese de proteínas ocorre em qual estrutura celular?',
            'Ribossomo', ['Lisossomo', 'Cloroplasto', 'Vacúolo'],
        ],
        [
            'A etapa da síntese proteica que ocorre no núcleo é:',
            'A transcrição', ['A tradução', 'A glicólise', 'A citocinese'],
        ],
        [
            'A fermentação difere da respiração aeróbica por:',
            'Não utilizar oxigênio', ['Ocorrer apenas em vegetais', 'Produzir mais ATP', 'Ocorrer no núcleo'],
        ],
        [
            'A etapa da respiração celular que ocorre no citosol é:',
            'A glicólise', ['O ciclo de Krebs', 'A cadeia respiratória', 'A fotossíntese'],
        ],
        [
            'A teoria celular afirma que:',
            'Todos os seres vivos são formados por células',
            [
                'Apenas os animais possuem células',
                'As células surgem espontaneamente da matéria bruta',
                'As células vegetais não possuem membrana',
            ],
        ],
        [
            'Os vírus não são considerados células porque:',
            'Não possuem estrutura celular nem metabolismo próprio',
            [
                'São menores que as bactérias',
                'Possuem parede celular rígida',
                'Reproduzem-se apenas por mitose',
            ],
        ],
    ];

    public static function topico(): string
    {
        return 'citologia';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::qualFuncao(),
            fn () => self::qualEstrutura(),
            fn () => self::associacao(),
            fn () => self::especificas(),
        ], $quantidade);
    }

    /** Funcoes de outras entradas, usadas como distratores verdadeiros. */
    private static function outras(int $i, int $campo): array
    {
        $total = count(self::ESTRUTURAS);

        return [
            self::ESTRUTURAS[($i + 7) % $total][$campo],
            self::ESTRUTURAS[($i + 17) % $total][$campo],
            self::ESTRUTURAS[($i + 29) % $total][$campo],
            self::ESTRUTURAS[($i + 3) % $total][$campo],
            self::ESTRUTURAS[($i + 41) % $total][$campo],
        ];
    }

    private static function qualFuncao(): iterable
    {
        foreach (self::ESTRUTURAS as $i => [$estrutura, $funcao]) {
            yield new Item(
                sprintf('Em uma célula, %s tem como função:', $estrutura),
                $i % 4 === 0 ? 'facil' : 'medio',
                ucfirst($funcao),
                self::distratores(ucfirst($funcao), array_map('ucfirst', self::outras($i, 1)))
            );
        }
    }

    private static function qualEstrutura(): iterable
    {
        foreach (self::ESTRUTURAS as $i => [$estrutura, $funcao]) {
            yield new Item(
                sprintf('Qual estrutura ou processo celular tem como função %s?', $funcao),
                'medio',
                ucfirst($estrutura),
                self::distratores(ucfirst($estrutura), array_map('ucfirst', self::outras($i, 0)))
            );
        }
    }

    private static function associacao(): iterable
    {
        foreach (self::ESTRUTURAS as $i => [$estrutura, $funcao]) {
            $certa = sprintf('%s: %s', ucfirst($estrutura), $funcao);

            // Os distratores associam a mesma estrutura a funcoes que sao
            // verdadeiras, porem de outras estruturas.
            $erradas = [];
            foreach (self::outras($i, 1) as $outraFuncao) {
                $erradas[] = sprintf('%s: %s', ucfirst($estrutura), $outraFuncao);
            }

            yield new Item(
                'Assinale a alternativa que associa corretamente a estrutura celular à sua função.',
                'dificil',
                $certa,
                self::distratores($certa, $erradas)
            );
        }
    }

    private static function especificas(): iterable
    {
        foreach (self::ESPECIFICAS as [$pergunta, $certa, $erradas]) {
            yield new Item($pergunta, 'dificil', $certa, self::distratores($certa, $erradas));
        }
    }
}
