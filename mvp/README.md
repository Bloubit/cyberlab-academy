# CyberLab Academy - MVP

Production-ready cybersecurity education platform on a $50/month budget.

## Quick Start

### 1. Server Setup (Ubuntu 22.04)

```bash
# SSH into your server
ssh root@your-server-ip

# Download and run setup script
curl -fsSL https://raw.githubusercontent.com/cyberlab/academy/main/mvp/scripts/setup.sh -o setup.sh
chmod +x setup.sh
sudo ./setup.sh
```

### 2. Or Manual Setup

```bash
# Clone repository
git clone https://github.com/cyberlab/academy.git
cd academy/mvp

# Copy environment file
cp .env.example .env

# Edit environment (set passwords, domain, etc.)
nano .env

# Build and start
docker-compose up -d

# Run migrations
docker-compose exec backend php artisan migrate

# Create admin user
docker-compose exec backend php artisan tinker
```

### 3. Access Your Platform

- **Frontend:** https://cyberlab.academy
- **API:** https://api.cyberlab.academy
- **Admin:** admin@cyberlab.academy / admin123

---

## Budget Breakdown

| Service | Cost | Provider |
|---------|------|----------|
| VPS (2 vCPU, 4GB) | $5-15/mo | Hetzner/DigitalOcean |
| Domain | $1/mo | Namecheap |
| SSL | $0 | Let's Encrypt |
| CDN | $0 | Cloudflare Free |
| Email | $0 | Mailgun Free |
| **Total** | **$6-16/mo** | |

---

## Architecture

```
Internet → Cloudflare (Free CDN/WAF) → VPS Server
                                            │
                              ┌──────────────┼──────────────┐
                              │              │              │
                         ┌────▼────┐   ┌─────▼─────┐   ┌────▼────┐
                         │  Nginx  │   │  Frontend │   │ Backend │
                         │  :80    │   │  :3000    │   │  :8000  │
                         │  :443   │   │  (Next.js)│   │ (Laravel)│
                         └─────────┘   └───────────┘   └────┬────┘
                                                            │
                              ┌──────────────┬──────────────┤
                              │              │              │
                         ┌────▼────┐   ┌─────▼─────┐   ┌────▼────┐
                         │PostgreSQL│  │   Redis   │   │ Worker  │
                         │  :5432   │   │  :6379    │   │ (Queue) │
                         └──────────┘  └───────────┘   └─────────┘
```

---

## Files Structure

```
mvp/
├── docker-compose.yml      # All services
├── .env.example            # Environment template
├── configs/
│   └── nginx.conf          # Nginx configuration
├── scripts/
│   ├── setup.sh            # Server setup
│   ├── deploy.sh           # Deployment
│   ├── backup.sh           # Backups
│   └── monitor.sh          # Monitoring
└── docs/
    └── MVP-ARCHITECTURE.md # Full documentation
```

---

## Commands Reference

### Service Management

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View status
docker-compose ps

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Database

```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Rollback migrations
docker-compose exec backend php artisan migrate:rollback

# Seed database
docker-compose exec backend php artisan db:seed

# Create admin user
docker-compose exec backend php artisan tinker

# Access PostgreSQL
docker exec -it cyberlab-postgres psql -U cyberlab cyberlab
```

### Laravel

```bash
# Clear cache
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache

# Create storage link
docker-compose exec backend php artisan storage:link

# Queue work
docker-compose exec backend php artisan queue:work
```

### Maintenance

```bash
# Enable maintenance mode
docker-compose exec backend php artisan down

# Disable maintenance mode
docker-compose exec backend php artisan up

# Manual backup
/opt/cyberlab/scripts/backup.sh

# View system resources
./scripts/monitor.sh
```

---

## SSL Setup

### Using Let's Encrypt (Recommended)

```bash
# Install certbot
apt install certbot python3-certbot-nginx

# Get certificate
certbot --nginx -d cyberlab.academy -d www.cyberlab.academy -d api.cyberlab.academy

# Auto-renewal
certbot renew --dry-run
```

### Using Cloudflare

1. Add domain to Cloudflare
2. Change nameservers
3. Enable SSL (Full Strict)
4. Enable "Always Use HTTPS"

---

## Backup & Recovery

### Automated Backups

Backups run daily at 2 AM via cron.

```bash
# Manual backup
/opt/cyberlab/scripts/backup.sh

# Restore from backup
gunzip -c /backups/db_YYYYMMDD.sql.gz | docker exec -i cyberlab-postgres psql -U cyberlab cyberlab
```

### What's Backed Up

- PostgreSQL database (daily)
- User uploads (weekly)
- Configuration files (on change)

---

## Scaling Path

| Phase | Users | Cost | Action |
|-------|-------|------|--------|
| MVP | < 100 | $15/mo | Single VPS |
| Growth | 100-1000 | $50/mo | Upgrade VPS |
| Scale | 1000-10000 | $150/mo | Add servers |
| Enterprise | 10000+ | $500+/mo | Kubernetes |

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs <container>

# Check disk space
df -h

# Check memory
free -h

# Restart Docker
systemctl restart docker
```

### Database connection issues

```bash
# Check PostgreSQL
docker exec cyberlab-postgres pg_isready

# Check credentials
docker exec cyberlab-postgres psql -U cyberlab -c "\l"
```

### SSL not working

```bash
# Check certificates
certbot certificates

# Renew certificates
certbot renew

# Check Nginx config
nginx -t
```

### High memory usage

```bash
# Check container resources
docker stats

# Restart services
docker-compose restart

# Clear Redis cache
docker exec cyberlab-redis redis-cli FLUSHALL
```

---

## Security Checklist

- [ ] Change default passwords
- [ ] Enable UFW firewall
- [ ] Configure fail2ban
- [ ] Use SSH keys
- [ ] Enable HTTPS
- [ ] Set up automated updates
- [ ] Configure log rotation
- [ ] Enable database encryption
- [ ] Regular backups
- [ ] Monitor for suspicious activity

---

## Support

- Documentation: docs/
- GitHub: github.com/cyberlab/academy
- Email: support@cyberlab.academy

---

## License

Proprietary - CyberLab Academy
