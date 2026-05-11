#!/bin/bash
# Fix: /app route conflict between Laravel page and Reverb WebSockets

cat > /etc/nginx/sites-available/pairprogramming << 'NGINX'
server {
    listen 80;
    server_name _;

    root /var/www/pairprogramming/public;
    index index.php;

    client_max_body_size 20M;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;

    # WebSockets para Laravel Reverb - SOLO conexiones WebSocket
    location /app {
        # Si es una conexión WebSocket, enviar a Reverb
        if ($http_upgrade = "websocket") {
            proxy_pass http://127.0.0.1:8080;
        }
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;

        # Si NO es WebSocket, pasar a PHP (la página /app de Laravel)
        try_files $uri $uri/ /index.php?$query_string;
    }

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

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
NGINX

nginx -t && systemctl restart nginx && echo "Nginx fixed!"
