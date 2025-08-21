# Imagen base con PHP 8.2
FROM php:8.2-cli

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql zip gd

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala Yarn
RUN npm install -g yarn

# Establece el directorio de trabajo
WORKDIR /var/www

# Copia tu proyecto al contenedor
COPY . .

# Instala dependencias PHP y JS
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN yarn
RUN yarn build

# Compila caches de Laravel
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN mkdir -p /var/log/laravel

# Expone el puerto (Render lo inyecta como $PORT)
EXPOSE 8000

# Comando para ejecutar Laravel en Render
CMD php artisan migrate --force || true && tail -f storage/logs/laravel.log | php artisan serve --host=0.0.0.0 --port=${PORT}

