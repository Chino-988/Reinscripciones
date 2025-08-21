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

# Corre migraciones durante build
RUN php artisan migrate --force || true

# Asegura permisos para Laravel
RUN mkdir -p storage/logs && chmod -R 777 storage bootstrap/cache

# Expone el puerto esperado por Render
EXPOSE 8080

# Inicia el servidor de Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}
