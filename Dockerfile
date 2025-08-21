# Imagen base
FROM php:8.2-cli

# Dependencias necesarias
RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql zip gd

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Yarn
RUN npm install -g yarn

# Directorio de trabajo
WORKDIR /var/www

# Copia proyecto
COPY . .

# Instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN yarn && yarn build

# Caches Laravel
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Exponer puerto 8000
EXPOSE 8000

# Comando de inicio
CMD php artisan serve --host=0.0.0.0 --port=$(echo ${PORT} | grep -o '[0-9]*')
