FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip nodejs npm libpq-dev

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar archivos
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Puerto
EXPOSE 10000

# Arrancar
CMD php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000