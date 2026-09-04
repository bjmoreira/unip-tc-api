FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache \
    && rm -rf /var/lib/apt/lists/*

# O OPcache guarda o bytecode ja compilado. Com validate_timestamps ligado o
# arquivo e reconferido a cada requisicao, mas so recompilado quando muda, o que
# mantem o cache correto durante o desenvolvimento.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# As dependencias sao instaladas na imagem, e nao no diretorio compartilhado com
# o host. Ler os milhares de arquivos de vendor/ atraves do bind mount do
# Windows dominava o tempo de resposta: so o boot do framework levava dezenas de
# segundos. Com vendor em disco nativo do contêiner, esse custo desaparece.
#
# O docker-compose monta um volume nomeado em /var/www/vendor, que o Docker
# preenche a partir desta camada na primeira execucao.
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

EXPOSE 8000
