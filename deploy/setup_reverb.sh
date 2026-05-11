#!/bin/bash
# Crear servicio systemd para Laravel Reverb
cat > /etc/systemd/system/reverb.service << 'EOF'
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/pairprogramming
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=8080
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable reverb
systemctl start reverb
echo "Reverb service created and started!"
systemctl status reverb --no-pager
