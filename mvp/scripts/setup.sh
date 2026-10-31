#!/bin/bash
# CyberLab Academy - MVP Setup Script
# Run this on a fresh Ubuntu 22.04 VPS

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    log_error "Please run as root (sudo ./setup.sh)"
    exit 1
fi

echo "=========================================="
echo "   CyberLab Academy - MVP Setup"
echo "=========================================="
echo ""

# Update system
log_info "Updating system packages..."
apt update && apt upgrade -y

# Install Docker
log_info "Installing Docker..."
apt install -y apt-transport-https ca-certificates curl software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Install Docker Compose
log_info "Installing Docker Compose..."
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Install Certbot
log_info "Installing Certbot for SSL..."
apt install -y certbot python3-certbot-nginx

# Install monitoring tools
log_info "Installing monitoring tools..."
apt install -y htop iotop ncdu

# Configure firewall
log_info "Configuring firewall..."
apt install -y ufw
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# Configure fail2ban
log_info "Configuring fail2ban..."
apt install -y fail2ban
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
port = http,https
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
EOF
systemctl restart fail2ban

# Create application directory
log_info "Creating application directory..."
mkdir -p /opt/cyberlab
cd /opt/cyberlab

# Clone repository (or copy files)
log_info "Setting up application..."
# git clone https://github.com/cyberlab/academy.git .

# Create environment file
log_info "Creating environment configuration..."
cp .env.example .env

# Generate random passwords
DB_PASSWORD=$(openssl rand -base64 32 | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
JWT_SECRET=$(openssl rand -base64 64 | tr -dc 'a-zA-Z0-9' | fold -w 64 | head -n 1)

# Update .env with generated passwords
sed -i "s/CHANGE_THIS_TO_SECURE_PASSWORD/$DB_PASSWORD/" .env
sed -i "s/CHANGE_THIS_TO_RANDOM_64_CHAR_STRING/$JWT_SECRET/" .env

log_success "Environment configured!"
echo ""
echo "=========================================="
echo "   IMPORTANT: Database Password"
echo "=========================================="
echo ""
echo "   $DB_PASSWORD"
echo ""
echo "   Save this password securely!"
echo "=========================================="
echo ""

# Create backup directory
mkdir -p /backups
mkdir -p /opt/cyberlab/database/backups

# Build and start containers
log_info "Building and starting Docker containers..."
docker-compose build --no-cache
docker-compose up -d

# Wait for services
log_info "Waiting for services to start..."
sleep 15

# Run database migrations
log_info "Running database migrations..."
docker-compose exec backend php artisan migrate --force

# Seed database (optional)
read -p "Do you want to seed the database with sample data? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker-compose exec backend php artisan db:seed
fi

# Create admin user
log_info "Creating admin user..."
docker-compose exec backend php artisan tinker --execute="
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'admin@cyberlab.academy',
    'username' => 'admin',
    'password' => Hash::make('admin123'),
    'is_active' => true,
    'email_verified_at' => now(),
]);
"

# Set permissions
log_info "Setting permissions..."
chown -R www-data:www-data /opt/cyberlab/storage
chmod -R 755 /opt/cyberlab/storage

# Setup SSL (interactive)
read -p "Do you want to setup SSL with Let's Encrypt? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log_info "Setting up SSL..."
    read -p "Enter your domain name (e.g., cyberlab.academy): " DOMAIN
    certbot --nginx -d $DOMAIN -d www.$DOMAIN -d api.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN
fi

# Setup automated backups
log_info "Setting up automated backups..."
cat > /opt/cyberlab/scripts/backup.sh << 'BACKUP_EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups"

# Database backup
docker exec cyberlab-postgres pg_dump -U cyberlab cyberlab | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Cleanup old backups (keep 7 days)
find "$BACKUP_DIR" -type f -mtime +7 -delete

echo "Backup completed: $DATE"
BACKUP_EOF
chmod +x /opt/cyberlab/scripts/backup.sh

# Add cron job for daily backups
(crontab -l 2>/dev/null; echo "0 2 * * * /opt/cyberlab/scripts/backup.sh >> /var/log/backup.log 2>&1") | crontab -

# Setup log rotation
cat > /etc/logrotate.d/cyberlab << 'LOGROTATE_EOF'
/opt/cyberlab/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
LOGROTATE_EOF

# Create systemd service for auto-start
cat > /etc/systemd/system/cyberlab.service << 'SERVICE_EOF'
[Unit]
Description=CyberLab Academy
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/cyberlab
ExecStart=/usr/local/bin/docker-compose up -d
ExecStop=/usr/local/bin/docker-compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
SERVICE_EOF

systemctl enable cyberlab.service

# Final health check
log_info "Running final health check..."
if curl -sf http://localhost:8000/api/health > /dev/null 2>&1; then
    log_success "Backend API is healthy!"
else
    log_warning "Backend API health check failed - may need a moment to start"
fi

echo ""
echo "=========================================="
echo "   Setup Complete!"
echo "=========================================="
echo ""
echo "   Services:"
echo "   - Frontend: https://cyberlab.academy"
echo "   - API: https://api.cyberlab.academy"
echo "   - Admin: https://cyberlab.academy/admin"
echo ""
echo "   Default Admin Login:"
echo "   - Email: admin@cyberlab.academy"
echo "   - Password: admin123"
echo ""
echo "   Commands:"
echo "   - Status: docker-compose ps"
echo "   - Logs: docker-compose logs -f"
echo "   - Restart: docker-compose restart"
echo "   - Stop: docker-compose down"
echo ""
echo "   Backups run daily at 2 AM"
echo ""
echo "=========================================="
