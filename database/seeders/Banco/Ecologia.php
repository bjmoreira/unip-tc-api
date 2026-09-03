<?php

namespace Database\Seeders\Banco;

/**
 * Ecologia: gerador curado.
 *
 * Mesma estrutura de Citologia: uma base de pares conceito/definicao conferida
 * uma a uma, explorada em tres direcoes, mais um conjunto de questoes escritas
 * individualmente para os casos que nao cabem nesse formato, como identificar a
 * relacao ecologica a partir de um exemplo concreto.
 */
final class Ecologia extends Gerador
{
    /** [conceito, definicao] */
    private const CONCEITOS = [
        ['população', 'o conjunto de indivíduos da mesma espécie que vivem em uma área'],
        ['comunidade', 'o conjunto de populações de espécies diferentes que convivem em uma área'],
        ['ecossistema', 'o conjunto formado pela comunidade e pelos fatores não vivos do ambiente'],
        ['habitat', 'o lugar onde uma espécie vive'],
        ['nicho ecológico', 'o conjunto de recursos que a espécie usa e o papel que exerce no ambiente'],
        ['bioma', 'o conjunto de ecossistemas com vegetação e clima semelhantes'],
        ['biosfera', 'o conjunto de todas as regiões do planeta onde existe vida'],
        ['o produtor', 'produzir o próprio alimento a partir de energia luminosa ou química'],
        ['o consumidor primário', 'alimentar-se diretamente dos produtores'],
        ['o consumidor secundário', 'alimentar-se dos consumidores primários'],
        ['o decompositor', 'devolver ao ambiente a matéria contida em restos de seres vivos'],
        ['a cadeia alimentar', 'representar a passagem de energia em uma única sequência linear'],
        ['a teia alimentar', 'representar o conjunto de cadeias alimentares interligadas'],
        ['o nível trófico', 'indicar a posição de um ser vivo na sequência alimentar'],
        ['a pirâmide de energia', 'mostrar a redução da energia disponível a cada nível trófico'],
        ['a fotossíntese', 'converter energia luminosa em energia química na forma de matéria orgânica'],
        ['o ciclo do carbono', 'descrever a circulação do carbono entre os seres vivos, a atmosfera e o solo'],
        ['o ciclo do nitrogênio', 'descrever a transformação do nitrogênio atmosférico em compostos assimiláveis'],
        ['o ciclo da água', 'descrever a circulação da água entre a superfície, a atmosfera e os seres vivos'],
        ['a fixação do nitrogênio', 'converter o nitrogênio do ar em amônia por ação de bactérias'],
        ['o mutualismo', 'beneficiar as duas espécies envolvidas, que dependem uma da outra'],
        ['a protocooperação', 'beneficiar as duas espécies envolvidas, sem que a associação seja obrigatória'],
        ['o comensalismo', 'beneficiar uma espécie sem prejudicar nem beneficiar a outra'],
        ['o inquilinismo', 'permitir que uma espécie use a outra como abrigo, sem prejudicá-la'],
        ['o parasitismo', 'beneficiar uma espécie às custas do prejuízo de outra, sem matá-la de imediato'],
        ['o predatismo', 'fazer com que uma espécie capture e mate outra para se alimentar'],
        ['a competição', 'colocar indivíduos em disputa pelo mesmo recurso limitado'],
        ['o amensalismo', 'fazer com que uma espécie iniba o desenvolvimento de outra sem obter benefício'],
        ['a herbivoria', 'fazer com que um animal se alimente de partes de vegetais'],
        ['a sucessão ecológica primária', 'ocupar gradualmente um ambiente que nunca abrigou uma comunidade'],
        ['a sucessão ecológica secundária', 'reocupar um ambiente que já abrigou uma comunidade e sofreu perturbação'],
        ['a comunidade clímax', 'representar o estágio mais estável e diversificado de uma sucessão'],
        ['as espécies pioneiras', 'colonizar primeiro um ambiente e preparar o solo para outras espécies'],
        ['a eutrofização', 'aumentar excessivamente os nutrientes na água e reduzir o oxigênio dissolvido'],
        ['a bioacumulação', 'concentrar substâncias tóxicas no organismo ao longo da vida do indivíduo'],
        ['a magnificação trófica', 'aumentar a concentração de poluentes a cada nível trófico da cadeia'],
        ['a chuva ácida', 'acidificar a precipitação pela reação de óxidos de enxofre e nitrogênio na atmosfera'],
        ['o efeito estufa', 'reter parte do calor irradiado pela superfície terrestre por gases da atmosfera'],
        ['a inversão térmica', 'aprisionar poluentes junto ao solo por uma camada de ar frio sob ar quente'],
        ['a espécie endêmica', 'ocorrer naturalmente apenas em uma região determinada'],
        ['a espécie exótica invasora', 'estabelecer-se fora de sua área natural e prejudicar as espécies nativas'],
        ['a Amazônia', 'formar a maior floresta tropical úmida do mundo, com alta pluviosidade'],
        ['o Cerrado', 'apresentar árvores de troncos retorcidos, solo ácido e vegetação adaptada ao fogo'],
        ['a Caatinga', 'reunir vegetação adaptada à seca, com cactos e plantas que perdem as folhas'],
        ['a Mata Atlântica', 'ocupar originalmente a faixa litorânea brasileira e apresentar alta biodiversidade'],
        ['o Pantanal', 'formar uma planície sujeita a inundações sazonais'],
        ['o Pampa', 'apresentar predomínio de vegetação campestre no extremo sul do Brasil'],
        ['o desmatamento', 'reduzir a cobertura vegetal e fragmentar os ambientes naturais'],
        ['a biodiversidade', 'expressar a variedade de espécies, genes e ecossistemas de uma região'],
        ['a capacidade de suporte', 'indicar o número máximo de indivíduos que um ambiente consegue sustentar'],
        ['a natalidade', 'expressar o número de novos indivíduos que surgem em uma população'],
        ['a mortalidade', 'expressar o número de indivíduos que morrem em uma população'],
        ['a densidade populacional', 'relacionar o número de indivíduos ao espaço que eles ocupam'],
        ['a migração', 'corresponder ao deslocamento de indivíduos entre áreas diferentes'],
        ['os fatores bióticos', 'corresponder aos componentes vivos de um ecossistema'],
        ['os fatores abióticos', 'corresponder aos componentes não vivos, como luz, água e temperatura'],
        ['a cadeia detritívora', 'iniciar-se a partir da matéria orgânica morta em vez dos produtores'],
        ['a resiliência ecológica', 'expressar a capacidade de um ecossistema se recuperar após uma perturbação'],
        ['o desenvolvimento sustentável', 'atender às necessidades atuais sem comprometer as gerações futuras'],
        ['a pegada ecológica', 'medir a área produtiva necessária para sustentar o consumo de uma população'],
    ];

    /** [pergunta, resposta correta, tres respostas incorretas] */
    private const ESPECIFICAS = [
        [
            'A associação entre as abelhas e as flores, em que o inseto obtém néctar e a planta é polinizada, é um exemplo de:',
            'Protocooperação', ['Parasitismo', 'Competição', 'Amensalismo'],
        ],
        [
            'A relação entre o cupim e os protozoários que digerem a celulose em seu intestino, indispensável para ambos, é um exemplo de:',
            'Mutualismo', ['Comensalismo', 'Predatismo', 'Herbivoria'],
        ],
        [
            'A rêmora, que se fixa ao tubarão para aproveitar restos de alimento sem prejudicá-lo, é um exemplo de:',
            'Comensalismo', ['Parasitismo', 'Mutualismo', 'Competição'],
        ],
        [
            'O carrapato que se alimenta do sangue de um cão caracteriza uma relação de:',
            'Parasitismo', ['Predatismo', 'Comensalismo', 'Mutualismo'],
        ],
        [
            'A onça que caça a capivara caracteriza uma relação de:',
            'Predatismo', ['Parasitismo', 'Amensalismo', 'Inquilinismo'],
        ],
        [
            'As orquídeas que crescem sobre os troncos das árvores, sem retirar delas nutrientes, exemplificam:',
            'Inquilinismo', ['Parasitismo', 'Predatismo', 'Competição'],
        ],
        [
            'Duas espécies de plantas que disputam luz e água no mesmo terreno estabelecem entre si uma relação de:',
            'Competição', ['Mutualismo', 'Comensalismo', 'Inquilinismo'],
        ],
        [
            'Em uma cadeia alimentar formada por capim, gafanhoto, sapo e cobra, o gafanhoto ocupa a posição de:',
            'Consumidor primário', ['Produtor', 'Consumidor secundário', 'Decompositor'],
        ],
        [
            'Em uma cadeia alimentar formada por algas, camarões, peixes e aves, as algas são classificadas como:',
            'Produtores', ['Consumidores primários', 'Decompositores', 'Consumidores terciários'],
        ],
        [
            'Fungos e bactérias que atuam sobre restos de organismos mortos são classificados como:',
            'Decompositores', ['Produtores', 'Consumidores primários', 'Consumidores secundários'],
        ],
        [
            'A energia disponível em uma cadeia alimentar, ao passar de um nível trófico para o seguinte:',
            'Diminui, pois parte é dissipada na forma de calor',
            [
                'Aumenta, pois os predadores são maiores',
                'Permanece constante ao longo de toda a cadeia',
                'Retorna integralmente aos produtores',
            ],
        ],
        [
            'Um poluente não degradável tende a atingir as maiores concentrações em qual nível da cadeia alimentar?',
            'Nos consumidores de nível mais alto',
            ['Nos produtores', 'Nos decompositores', 'Distribui-se igualmente em todos os níveis'],
        ],
        [
            'O despejo de esgoto rico em nutrientes em um lago provoca proliferação de algas e morte de peixes. Esse processo é chamado de:',
            'Eutrofização', ['Inversão térmica', 'Sucessão primária', 'Bioacumulação'],
        ],
        [
            'O aumento da concentração de gás carbônico na atmosfera está diretamente associado a:',
            'Intensificação do efeito estufa',
            ['Destruição da camada de ozônio', 'Formação de chuva ácida', 'Ocorrência de inversão térmica'],
        ],
        [
            'A destruição da camada de ozônio aumenta a incidência de qual tipo de radiação na superfície terrestre?',
            'Radiação ultravioleta', ['Radiação infravermelha', 'Ondas de rádio', 'Radiação visível'],
        ],
        [
            'A colonização de uma rocha nua por liquens, seguida pelo surgimento de musgos e depois de arbustos, caracteriza:',
            'Sucessão ecológica primária',
            ['Sucessão ecológica secundária', 'Eutrofização', 'Comunidade clímax'],
        ],
        [
            'Uma área de floresta que se regenera após um incêndio passa por:',
            'Sucessão ecológica secundária',
            ['Sucessão ecológica primária', 'Extinção em massa', 'Inversão térmica'],
        ],
        [
            'No processo de sucessão ecológica, a biodiversidade da comunidade tende a:',
            'Aumentar até o estágio de clímax',
            ['Diminuir continuamente', 'Permanecer constante', 'Zerar após as espécies pioneiras'],
        ],
        [
            'A introdução do mexilhão-dourado em rios brasileiros, onde ele se multiplica e desloca espécies nativas, é um caso de:',
            'Espécie exótica invasora',
            ['Espécie endêmica', 'Espécie em extinção', 'Espécie decompositora'],
        ],
        [
            'Uma espécie que ocorre naturalmente apenas em uma região do planeta é classificada como:',
            'Endêmica', ['Exótica', 'Invasora', 'Cosmopolita'],
        ],
        [
            'O bioma brasileiro caracterizado por vegetação adaptada à escassez de água, com cactáceas e plantas caducifólias, é:',
            'A Caatinga', ['A Amazônia', 'O Pantanal', 'A Mata Atlântica'],
        ],
        [
            'O bioma brasileiro marcado por árvores de troncos retorcidos, cascas grossas e solo ácido é:',
            'O Cerrado', ['A Caatinga', 'O Pampa', 'A Amazônia'],
        ],
        [
            'A maior planície alagável do mundo, sujeita a cheias sazonais, encontra-se no bioma:',
            'Pantanal', ['Cerrado', 'Caatinga', 'Pampa'],
        ],
        [
            'As bactérias fixadoras que vivem em nódulos nas raízes de leguminosas participam diretamente do:',
            'Ciclo do nitrogênio', ['Ciclo do carbono', 'Ciclo da água', 'Ciclo do fósforo'],
        ],
        [
            'A retirada de gás carbônico da atmosfera pelos vegetais ocorre principalmente por meio da:',
            'Fotossíntese', ['Respiração celular', 'Decomposição', 'Transpiração'],
        ],
        [
            'O crescimento de uma população em um ambiente com recursos limitados tende a estabilizar-se quando atinge:',
            'A capacidade de suporte do ambiente',
            ['A taxa de natalidade máxima', 'O ponto de extinção', 'O clímax da sucessão'],
        ],
    ];

    public static function topico(): string
    {
        return 'ecologia';
    }

    public static function gerar(int $quantidade): array
    {
        return self::intercalar([
            fn () => self::qualDefinicao(),
            fn () => self::qualConceito(),
            fn () => self::associacao(),
            fn () => self::especificas(),
        ], $quantidade);
    }

    private static function outras(int $i, int $campo): array
    {
        $total = count(self::CONCEITOS);

        return [
            self::CONCEITOS[($i + 11) % $total][$campo],
            self::CONCEITOS[($i + 23) % $total][$campo],
            self::CONCEITOS[($i + 37) % $total][$campo],
            self::CONCEITOS[($i + 5) % $total][$campo],
            self::CONCEITOS[($i + 43) % $total][$campo],
        ];
    }

    private static function qualDefinicao(): iterable
    {
        foreach (self::CONCEITOS as $i => [$conceito, $definicao]) {
            yield new Item(
                sprintf('Em ecologia, %s pode ser definido como:', $conceito),
                $i % 4 === 0 ? 'facil' : 'medio',
                ucfirst($definicao),
                self::distratores(ucfirst($definicao), array_map('ucfirst', self::outras($i, 1)))
            );
        }
    }

    private static function qualConceito(): iterable
    {
        foreach (self::CONCEITOS as $i => [$conceito, $definicao]) {
            yield new Item(
                sprintf('Qual conceito ecológico corresponde a %s?', $definicao),
                'medio',
                ucfirst($conceito),
                self::distratores(ucfirst($conceito), array_map('ucfirst', self::outras($i, 0)))
            );
        }
    }

    private static function associacao(): iterable
    {
        foreach (self::CONCEITOS as $i => [$conceito, $definicao]) {
            $certa = sprintf('%s: %s', ucfirst($conceito), $definicao);

            $erradas = [];
            foreach (self::outras($i, 1) as $outra) {
                $erradas[] = sprintf('%s: %s', ucfirst($conceito), $outra);
            }

            yield new Item(
                'Assinale a alternativa que associa corretamente o conceito ecológico à sua definição.',
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
