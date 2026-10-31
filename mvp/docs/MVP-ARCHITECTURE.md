# CyberLab Academy - MVP Architecture ($50/month)

## Executive Summary

This document outlines a cost-effective MVP architecture for CyberLab Academy, designed to run on a budget of under $50/month while maintaining scalability for future growth.

## Budget Breakdown

| Component | Monthly Cost | Provider |
|-----------|--------------|----------|
| VPS Server | $15-20 | Hetzner / DigitalOcean |
| Domain | $1 | Namecheap/Cloudflare |
| SSL Certificate | $0 | Let's Encrypt |
| CDN | $0 | Cloudflare (Free) |
| Email Service | $0 | Mailgun (1000 free) |
| Object Storage | $1-3 | Backblaze B2 / Local |
| Backup Storage | $1-2 | Local + Offsite |
| **Total** | **$17-26/month** | |

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              INTERNET                                    │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        CLOUDFLARE (FREE)                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                     │
│  │     CDN     │  │     WAF     │  │ SSL/TLS     │                     │
│  │   Cache     │  │   Firewall  │  │ Termination │                     │
│  └─────────────┘  └─────────────┘  └─────────────┘                     │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        SINGLE VPS SERVER                                │
│                        (Hetzner CX31 / DO Droplet)                      │
│                        2 vCPU, 4GB RAM, 80GB SSD                       │
│                        Cost: ~$15/month                                 │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                        DOCKER CONTAINERS                         │   │
│  │                                                                   │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │   │
│  │  │   Nginx     │  │   Frontend  │  │   Backend   │              │   │
│  │  │  (Reverse   │  │  (Next.js)  │  │  (Laravel)  │              │   │
│  │  │   Proxy)    │  │  Port 3000  │  │  Port 8000  │              │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘              │   │
│  │                                                                   │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │   │
│  │  │  PostgreSQL │  │    Redis    │  │   Worker    │              │   │
│  │  │  Port 5432  │  │  Port 6379  │  │  (Queue)    │              │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘              │   │
│  │                                                                   │   │
│  │  ┌─────────────┐  ┌─────────────┐                                │   │
│  │  │   Storage   │  │   Backups   │                                │   │
│  │  │  /data      │  │  /backups   │                                │   │
│  │  └─────────────┘  └─────────────┘                                │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

## Server Specifications

### Recommended VPS Options

#### Option 1: Hetzner (Recommended)
- **Plan:** CX31
- **CPU:** 2 vCPU (AMD EPYC)
- **RAM:** 4 GB
- **Storage:** 80 GB SSD
- **Traffic:** 20 TB included
- **Location:** Nuremberg/Falkenstein
- **Cost:** €4.85/month (~$5.30)

#### Option 2: DigitalOcean
- **Plan:** Basic Droplet
- **CPU:** 2 vCPU
- **RAM:** 4 GB
- **Storage:** 80 GB SSD
- **Traffic:** 4 TB included
- **Cost:** $24/month

#### Option 3: Vultr
- **Plan:** Cloud Compute
- **CPU:** 2 vCPU
- **RAM:** 4 GB
- **Storage:** 80 GB SSD
- **Traffic:** 4 TB included
- **Cost:** $24/month

### Resource Allocation

```
Total Resources: 2 vCPU, 4GB RAM, 80GB SSD

Allocation:
├── Nginx: 50MB RAM, minimal CPU
├── Frontend (Next.js): 512MB RAM
├── Backend (Laravel): 1GB RAM
├── PostgreSQL: 1GB RAM
├── Redis: 256MB RAM
├── Worker: 512MB RAM
├── System/OS: 500MB RAM
└── Buffer: ~700MB RAM

Storage Allocation:
├── OS & Docker: 10GB
├── Database: 20GB
├── Application: 10GB
├── User Uploads: 20GB
├── Backups: 15GB
└── Logs: 5GB
```

## Docker Compose Configuration

```yaml
version: '3.8'

services:
  # Nginx Reverse Proxy
  nginx:
    image: nginx:alpine
    container_name: cyberlab-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./nginx/ssl:/etc/nginx/ssl
      - ./frontend/public:/usr/share/nginx/html/public
      - storage:/var/www/storage:ro
    depends_on:
      - frontend
      - backend
    networks:
      - cyberlab

  # Frontend (Next.js)
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: cyberlab-frontend
    restart: unless-stopped
    environment:
      - NODE_ENV=production
      - NEXT_PUBLIC_API_URL=https://api.cyberlab.academy/api
    networks:
      - cyberlab

  # Backend (Laravel)
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: cyberlab-backend
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
      - QUEUE_CONNECTION=redis
    volumes:
      - storage:/var/www/storage
      - ./backend/storage/logs:/var/www/storage/logs
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - cyberlab

  # Worker (Queue)
  worker:
    build:
      context: ./backend
      dockerfile: Dockerfile.worker
    container_name: cyberlab-worker
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - QUEUE_CONNECTION=redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - cyberlab

  # PostgreSQL
  postgres:
    image: postgres:16-alpine
    container_name: cyberlab-postgres
    restart: unless-stopped
    environment:
      - POSTGRES_DB=${DB_DATABASE}
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/backups:/backups
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME}"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - cyberlab

  # Redis
  redis:
    image: redis:7-alpine
    container_name: cyberlab-redis
    restart: unless-stopped
    command: redis-server --appendonly yes --maxmemory 256mb --maxmemory-policy allkeys-lru
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - cyberlab

  # Cron Scheduler
  scheduler:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: cyberlab-scheduler
    restart: unless-stopped
    command: >
      sh -c "while true; do php artisan schedule:run --verbose --no-interaction & sleep 60; done"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
    volumes:
      - storage:/var/www/storage
    depends_on:
      postgres:
        condition: service_healthy
    networks:
      - cyberlab

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local
  storage:
    driver: local

networks:
  cyberlab:
    driver: bridge
```

## Nginx Configuration

```nginx
# /etc/nginx/nginx.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Logging format
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    # Performance
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 50M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

    # Upstream servers
    upstream frontend {
        server frontend:3000;
    }

    upstream backend {
        server backend:9000;
    }

    # HTTP Server
    server {
        listen 80;
        server_name cyberlab.academy www.cyberlab.academy api.cyberlab.academy;

        # Redirect to HTTPS
        return 301 https://$server_name$request_uri;
    }

    # HTTPS Server - Main Domain
    server {
        listen 443 ssl http2;
        server_name cyberlab.academy www.cyberlab.academy;

        # SSL Configuration (Let's Encrypt)
        ssl_certificate /etc/nginx/ssl/fullchain.pem;
        ssl_certificate_key /etc/nginx/ssl/privkey.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers HIGH:!aNULL:!MD5;
        ssl_prefer_server_ciphers on;
        ssl_session_cache shared:SSL:10m;
        ssl_session_timeout 10m;

        # Security headers
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header Referrer-Policy "no-referrer-when-downgrade" always;
        add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

        # Frontend
        location / {
            proxy_pass http://frontend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_cache_bypass $http_upgrade;
        }

        # Static files
        location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
            proxy_pass http://frontend;
            expires 30d;
            add_header Cache-Control "public, immutable";
            access_log off;
        }
    }

    # HTTPS Server - API
    server {
        listen 443 ssl http2;
        server_name api.cyberlab.academy;

        # SSL Configuration
        ssl_certificate /etc/nginx/ssl/fullchain.pem;
        ssl_certificate_key /etc/nginx/ssl/privkey.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers HIGH:!aNULL:!MD5;

        # Security headers
        add_header X-Frame-Options "DENY" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

        # API rate limiting
        limit_req zone=api burst=20 nodelay;

        # Backend API
        location / {
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_cache_bypass $http_upgrade;
            proxy_read_timeout 90;
            proxy_connect_timeout 90;
        }

        # Login rate limiting
        location /api/auth/login {
            limit_req zone=login burst=3 nodelay;
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        # Static assets
        location /storage {
            alias /var/www/storage;
            expires 30d;
            add_header Cache-Control "public, immutable";
        }
    }

    # Health check endpoint
    server {
        listen 8080;
        server_name localhost;

        location /nginx-health {
            access_log off;
            return 200 "healthy\n";
            add_header Content-Type text/plain;
        }

        location /status {
            stub_status on;
            access_log off;
        }
    }
}
```

## Backend Dockerfile (Optimized)

```dockerfile
# Dockerfile
FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first (for caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Production stage
FROM base AS production

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# OPcache configuration
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/opcache.ini

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:9000/api/health || exit 1

EXPOSE 9000

CMD ["php-fpm"]
```

## Frontend Dockerfile (Optimized)

```dockerfile
# Stage 1: Dependencies
FROM node:20-alpine AS deps
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --only=production

# Stage 2: Build
FROM node:20-alpine AS builder
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY . .

ENV NEXT_TELEMETRY_DISABLED=1
RUN npm run build

# Stage 3: Production
FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

CMD ["node", "server.js"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:3000/api/health || exit 1
```

## Backup Strategy

### Automated Backups Script

```bash
#!/bin/bash
# backup.sh - Automated backup script

set -e

# Configuration
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Database backup
echo "Starting database backup..."
docker exec cyberlab-postgres pg_dump -U cyberlab cyberlab | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Application backup
echo "Backing up application data..."
tar -czf "$BACKUP_DIR/app_$DATE.tar.gz" -C /var/www storage/

# Remove old backups
find "$BACKUP_DIR" -type f -mtime +$RETENTION_DAYS -delete

echo "Backup completed: $DATE"
```

### Cron Job Setup

```bash
# Add to crontab
# Daily backup at 2 AM
0 2 * * * /opt/cyberlab/scripts/backup.sh >> /var/log/backup.log 2>&1

# Weekly full backup
0 3 * * 0 /opt/cyberlab/scripts/full-backup.sh >> /var/log/backup.log 2>&1
```

## SSL/TLS Setup

### Let's Encrypt with Certbot

```bash
# Install certbot
apt install certbot python3-certbot-nginx

# Obtain certificate
certbot --nginx -d cyberlab.academy -d www.cyberlab.academy -d api.cyberlab.academy

# Auto-renewal (add to crontab)
0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring (Lightweight)

### Simple Health Check Script

```bash
#!/bin/bash
# healthcheck.sh

# Check services
check_service() {
    local service=$1
    local url=$2
    
    if curl -sf "$url" > /dev/null 2>&1; then
        echo "✓ $service is healthy"
        return 0
    else
        echo "✗ $service is down"
        return 1
    fi
}

echo "=== CyberLab Health Check ==="
echo "Time: $(date)"
echo ""

check_service "Frontend" "http://localhost:3000/api/health"
check_service "Backend API" "http://localhost:8000/api/health"
check_service "Nginx" "http://localhost:8080/nginx-health"

# Check database
if docker exec cyberlab-postgres pg_isready -U cyberlab > /dev/null 2>&1; then
    echo "✓ PostgreSQL is healthy"
else
    echo "✗ PostgreSQL is down"
fi

# Check Redis
if docker exec cyberlab-redis redis-cli ping > /dev/null 2>&1; then
    echo "✓ Redis is healthy"
else
    echo "✗ Redis is down"
fi

# Check disk usage
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 80 ]; then
    echo "⚠ Warning: Disk usage is ${DISK_USAGE}%"
fi

# Check memory usage
MEMORY_USAGE=$(free | awk '/Mem:/ {printf "%.0f", $3/$2 * 100}')
if [ "$MEMORY_USAGE" -gt 80 ]; then
    echo "⚠ Warning: Memory usage is ${MEMORY_USAGE}%"
fi
```

### Simple Monitoring Dashboard

```bash
#!/bin/bash
# monitor.sh - Real-time monitoring

watch -n 5 '
echo "=== System Resources ==="
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk "{print \$2}")%"
echo "Memory: $(free -h | awk "/Mem:/ {print \$3 \" / \" \$2}")"
echo "Disk: $(df -h / | awk "NR==2 {print \$3 \" / \" \$2 \" (\" \$5 \")\"}")"
echo ""
echo "=== Docker Containers ==="
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""
echo "=== Recent Logs ==="
docker logs --tail 5 cyberlab-backend
'
```

## Deployment Script

```bash
#!/bin/bash
# deploy.sh - MVP deployment script

set -e

# Configuration
APP_DIR="/opt/cyberlab"
BACKUP_DIR="/backups"

echo "=========================================="
echo "   CyberLab Academy - MVP Deployment"
echo "=========================================="

# Update code
echo "Updating code..."
cd $APP_DIR
git pull origin main

# Build images
echo "Building Docker images..."
docker-compose build --no-cache

# Run migrations
echo "Running database migrations..."
docker-compose exec backend php artisan migrate --force

# Clear cache
echo "Clearing cache..."
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache

# Restart services
echo "Restarting services..."
docker-compose down
docker-compose up -d

# Wait for services
echo "Waiting for services to start..."
sleep 10

# Health check
echo "Running health check..."
if curl -sf http://localhost:8000/api/health > /dev/null; then
    echo "✓ Deployment successful!"
else
    echo "✗ Health check failed!"
    exit 1
fi

echo "=========================================="
echo "   Deployment Complete!"
echo "=========================================="
```

## Environment Variables

```bash
# .env file
APP_NAME="CyberLab Academy"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cyberlab.academy

# Database
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=cyberlab
DB_USERNAME=cyberlab
DB_PASSWORD=your_secure_password_here

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Application
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@cyberlab.academy"
MAIL_FROM_NAME="CyberLab Academy"

# Storage
FILESYSTEM_DISK=local

# JWT
JWT_SECRET=your_jwt_secret_here
JWT_TTL=1440
```

## Scaling Path

### Phase 1: MVP ($20-30/month)
- Single VPS (2 vCPU, 4GB RAM)
- All services on one server
- Basic monitoring
- Manual backups

### Phase 2: Growth ($50-100/month)
- Upgrade VPS (4 vCPU, 8GB RAM)
- Add managed PostgreSQL
- Add Redis Cloud
- Implement CDN

### Phase 3: Scale ($100-300/month)
- Multiple servers
- Load balancer
- Managed Kubernetes
- Full monitoring stack

### Phase 4: Enterprise ($300+/month)
- Multi-region deployment
- Auto-scaling
- Enterprise support
- Compliance certifications

## Cost Optimization Tips

1. **Use Hetzner** - Best price/performance ratio
2. **Cloudflare Free** - CDN, WAF, SSL all free
3. **Mailgun Free Tier** - 1000 emails/month free
4. **Let's Encrypt** - Free SSL certificates
5. **Local Storage** - Store files on VPS disk
6. **Optimize Images** - Compress before upload
7. **Enable Caching** - Redis + Nginx caching
8. **Use Alpine Images** - Smaller Docker images
9. **Schedule Backups** - Off-peak hours
10. **Monitor Usage** - Right-size resources

## Security Checklist

- [ ] Enable UFW firewall
- [ ] Configure fail2ban
- [ ] Use SSH keys only
- [ ] Enable HTTPS everywhere
- [ ] Set up automated updates
- [ ] Configure log rotation
- [ ] Enable database encryption
- [ ] Use environment variables
- [ ] Regular security audits
- [ ] Backup verification

## Quick Start Commands

```bash
# Initial setup
git clone https://github.com/cyberlab/academy.git
cd academy/mvp
cp .env.example .env
# Edit .env with your settings

# Start services
docker-compose up -d

# Run migrations
docker-compose exec backend php artisan migrate

# Create admin user
docker-compose exec backend php artisan tinker
# User::create(['name'=>'Admin','email'=>'admin@cyberlab.academy','password'=>Hash::make('password'),'is_active'=>true])

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## Support

- Documentation: docs/
- Issues: GitHub Issues
- Email: support@cyberlab.academy

## License

Proprietary - CyberLab Academy
