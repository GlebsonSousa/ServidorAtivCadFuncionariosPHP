# Usa uma imagem oficial do PHP com o servidor Apache, pronta para uso
FROM php:8.2-apache

# Instala a extensão mysqli
RUN docker-php-ext-install mysqli

# Copia todos os nossos arquivos (.php) para a pasta pública do servidor
COPY . /var/www/html/