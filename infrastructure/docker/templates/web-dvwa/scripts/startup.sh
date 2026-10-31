#!/bin/bash

# Lab startup script
set -e

echo "=========================================="
echo "   CyberLab Academy - Lab Starting..."
echo "=========================================="

# Start SSH service
service ssh start

# Start Apache
service apache2 start

# Start MySQL
service mysql start

# Initialize database
echo "[+] Initializing DVWA database..."
mysql -u dvwa -pp@ssw0rd dvwa < /var/www/html/SQL/dvwa.sql 2>/dev/null || true

# Set permissions
chown -R www-data:www-data /var/www/html

# Generate random flag if not exists
if [ ! -f /opt/flags/flag.txt ]; then
    echo "FLAG{$(cat /proc/sys/kernel/random/uuid)}" > /opt/flags/flag.txt
    chmod 444 /opt/flags/flag.txt
fi

echo "=========================================="
echo "   Lab Started Successfully!"
echo "=========================================="
echo "   SSH: lab@$(hostname -i) port 22"
echo "   Web: http://$(hostname -i)/DVWA/"
echo "   Default credentials: admin/password"
echo "=========================================="

# Keep container running
tail -f /var/log/apache2/access.log /var/log/apache2/error.log
