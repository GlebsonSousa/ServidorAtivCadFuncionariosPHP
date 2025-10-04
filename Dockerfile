# 1. Define a imagem base do PHP com servidor Apache
FROM php:8.2-apache

# 2. Instala dependências do sistema necessárias para o Composer e extensões PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Instala as extensões PHP necessárias para o projeto (mysqli e zip)
RUN docker-php-ext-install mysqli zip

# 4. Instala o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Define o diretório de trabalho para onde os arquivos da aplicação serão copiados
WORKDIR /var/www/html

# 6. Copia os arquivos de definição de dependências
#    Isso é feito separadamente para aproveitar o cache do Docker.
#    O 'composer install' só será executado novamente se o composer.json for alterado.
COPY composer.json .

# 7. Instala as dependências do projeto com o Composer
RUN composer install --no-dev --optimize-autoloader

# 8. Copia todos os outros arquivos do seu projeto para o diretório de trabalho
COPY . .

# 9. Define o dono dos arquivos para o usuário do Apache, evitando problemas de permissão
RUN chown -R www-data:www-data /var/www/html