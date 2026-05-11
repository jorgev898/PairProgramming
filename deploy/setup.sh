#!/bin/bash
# ============================================================
#  PairProgramming - Setup Completo del Servidor
#  Ejecutar como root en un Droplet Ubuntu 24.04 LTS
#  Uso: bash setup.sh <GITHUB_REPO_URL>
# ============================================================

set -e  # Salir si hay errores

REPO_URL=${1:-""}
APP_DIR="/var/www/pairprogramming"
DB_NAME="pairprogramming"
DB_USER="pairprog_user"
DB_PASS=$(openssl rand -base64 16)

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_step() { echo -e "\n${GREEN}[PASO]${NC} $1\n"; }
print_warn() { echo -e "${YELLOW}[AVISO]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# ============================================================
# Validaciones
# ============================================================
if [ "$EUID" -ne 0 ]; then
    print_error "Ejecuta este script como root: sudo bash setup.sh"
    exit 1
fi

if [ -z "$REPO_URL" ]; then
    print_error "Uso: bash setup.sh <URL_DE_TU_REPO_GITHUB>"
    print_error "Ejemplo: bash setup.sh https://github.com/tu-usuario/PairProgramming.git"
    exit 1
fi

# ============================================================
# 1. Crear Swap (necesario para Droplets de 512MB)
# ============================================================
print_step "1/9 - Creando memoria swap (1GB)..."
if [ ! -f /swapfile ]; then
    fallocate -l 1G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    # Optimizar swap para bajo uso de RAM
    sysctl vm.swappiness=10
    echo 'vm.swappiness=10' >> /etc/sysctl.conf
    print_warn "Swap de 1GB creado exitosamente"
else
    print_warn "Swap ya existe, saltando..."
fi

# ============================================================
# 2. Actualizar sistema e instalar dependencias base
# ============================================================
print_step "2/9 - Actualizando sistema e instalando dependencias..."
apt-get update -y
apt-get upgrade -y
apt-get install -y \
    software-properties-common \
    curl \
    git \
    zip \
    unzip \
    ufw \
    fail2ban

# ============================================================
# 3. Instalar PHP 8.2 y extensiones
# ============================================================
print_step "3/9 - Instalando PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline \
    php8.2-tokenizer

# Optimizar PHP para 512MB RAM
sed -i 's/memory_limit = .*/memory_limit = 128M/' /etc/php/8.2/fpm/php.ini
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 25M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 60/' /etc/php/8.2/fpm/php.ini

systemctl restart php8.2-fpm
systemctl enable php8.2-fpm

# ============================================================
# 4. Instalar Nginx
# ============================================================
print_step "4/9 - Instalando Nginx..."
apt-get install -y nginx
systemctl enable nginx

# ============================================================
# 5. Instalar MySQL 8
# ============================================================
print_step "5/9 - Instalando MySQL..."
apt-get install -y mysql-server

# Crear base de datos y usuario
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

systemctl enable mysql

# ============================================================
# 6. Instalar Node.js 20 LTS y Composer
# ============================================================
print_step "6/9 - Instalando Node.js 20 y Composer..."

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ============================================================
# 7. Clonar y configurar el proyecto
# ============================================================
print_step "7/9 - Clonando y configurando el proyecto..."

mkdir -p ${APP_DIR}
git clone ${REPO_URL} ${APP_DIR}
cd ${APP_DIR}

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias Node y compilar assets
npm install
npm run build

# Configurar .env
if [ -f .env.production ]; then
    cp .env.production .env
else
    cp .env.example .env
fi

# Generar key
php artisan key:generate --force

# Insertar credenciales de BD en .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env

# Ejecutar migraciones
php artisan migrate --force

# Cachear config para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permisos
chown -R www-data:www-data ${APP_DIR}
chmod -R 755 ${APP_DIR}/storage
chmod -R 755 ${APP_DIR}/bootstrap/cache

# ============================================================
# 8. Configurar Nginx
# ============================================================
print_step "8/9 - Configurando Nginx..."

# Copiar config de nginx
if [ -f ${APP_DIR}/deploy/nginx.conf ]; then
    cp ${APP_DIR}/deploy/nginx.conf /etc/nginx/sites-available/pairprogramming
else
    cat > /etc/nginx/sites-available/pairprogramming <<'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/pairprogramming/public;
    index index.php;

    client_max_body_size 20M;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
NGINX
fi

# Activar sitio y desactivar default
ln -sf /etc/nginx/sites-available/pairprogramming /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Verificar config y reiniciar
nginx -t
systemctl restart nginx

# ============================================================
# 9. Configurar Firewall
# ============================================================
print_step "9/9 - Configurando firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw allow 8080  # Para Reverb WebSockets
ufw --force enable

# ============================================================
# RESUMEN
# ============================================================
SERVER_IP=$(curl -s ifconfig.me)

echo ""
echo "============================================================"
echo -e "${GREEN}  ✅ ¡INSTALACIÓN COMPLETADA!${NC}"
echo "============================================================"
echo ""
echo "  🌐 Tu app está en: http://${SERVER_IP}"
echo ""
echo "  📦 Base de Datos:"
echo "     - Nombre:   ${DB_NAME}"
echo "     - Usuario:  ${DB_USER}"
echo "     - Password: ${DB_PASS}"
echo ""
echo "  📁 Archivos en: ${APP_DIR}"
echo ""
echo "  ⚠️  IMPORTANTE - Guarda estas credenciales!"
echo ""
echo "  📝 Próximos pasos:"
echo "     1. Edita tu .env: nano ${APP_DIR}/.env"
echo "        - Cambia APP_URL=http://${SERVER_IP}"
echo "        - Agrega tus API keys de LangGraph si las necesitas"
echo "     2. Para actualizar después: bash ${APP_DIR}/deploy/deploy.sh"
echo ""
echo "============================================================"
