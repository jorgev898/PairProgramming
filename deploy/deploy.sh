#!/bin/bash
# ============================================================
#  PairProgramming - Script de actualización (re-deploy)
#  Ejecutar cuando hagas cambios en tu código
#  Uso: bash deploy.sh
# ============================================================

set -e

APP_DIR="/var/www/pairprogramming"
GREEN='\033[0;32m'
NC='\033[0m'

print_step() { echo -e "${GREEN}[DEPLOY]${NC} $1"; }

cd ${APP_DIR}

# Activar modo mantenimiento
print_step "Activando modo mantenimiento..."
php artisan down --retry=30 || true

# Bajar últimos cambios
print_step "Bajando cambios de GitHub..."
git pull origin Tostada

# Instalar dependencias PHP (si cambiaron)
print_step "Actualizando dependencias PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias Node y recompilar assets (si cambiaron)
print_step "Recompilando assets..."
npm install
npm run build

# Ejecutar migraciones nuevas
print_step "Ejecutando migraciones..."
php artisan migrate --force

# Limpiar y re-cachear
print_step "Limpiando caché..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permisos
chown -R www-data:www-data ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache

# Reiniciar servicios
print_step "Reiniciando servicios..."
systemctl restart php8.2-fpm
systemctl restart nginx

# Desactivar modo mantenimiento
print_step "Desactivando modo mantenimiento..."
php artisan up

echo ""
echo -e "${GREEN}✅ Deploy completado exitosamente!${NC}"
echo ""
