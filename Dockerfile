# Usa PHP 8.2 con extensiones necesarias
FROM php:8.2-cli

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql zip gd

# Instala Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala Yarn
RUN npm install -g yarn

# Establece directorio de trabajo
WORKDIR /var/www

# Copia todo el proyecto
COPY . .

# Instala dependencias de PHP y JavaScript
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN yarn && yarn build

# Cache de Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Exponer puerto 8000
EXPOSE 8000

# Comando final: ejecutar Laravel en el puerto que Railway expone
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]
