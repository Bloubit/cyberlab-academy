# CyberLab Academy - Local Development

Loyihani localda ishga tushirish uchun qo'llanma.

## Talablar

- Docker Desktop o'rnatilgan bo'lishi kerak
- Kamida 4GB RAM bo'lishi kerak
- 10GB bo'sh disk kerak

## Tezkor Boshlash

### 1-usul: Avtomatik (Tavsiya etiladi)

```bash
# Loyihani clonedan keyin
./start.sh
```

### 2-usul: Qo'lda

```bash
# Contaynerlarni ishga tushirish
docker-compose up -d --build

# Backend sozlash
cp .env backend/.env
docker-compose exec backend composer install
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan storage:link

# Holatni tekshirish
docker-compose ps
```

## Xizmatlar

| Xizmat | URL | Tavsif |
|--------|-----|--------|
| Frontend | http://localhost | Asosiy veb-sayt |
| Backend API | http://localhost/api | REST API |
| Adminer | http://localhost:8080 | Ma'lumotlar bazasi GUI |
| MailHog | http://localhost:8025 | Email test |

## Ma'lumotlar bazasi

- **Host:** postgres
- **Port:** 5432
- **Database:** cyberlab
- **Username:** cyberlab
- **Password:** secret

## Buyruqlar

```bash
# Xizmatlarni boshqarish
docker-compose up -d        # Boshlash
docker-compose down          # To'xtatish
docker-compose restart       # Qayta ishga tushirish
docker-compose ps            # Holat
docker-compose logs -f       # Loglar

# Laravel buyruqlari
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan tinker

# Ma'lumotlar bazasi
docker exec -it cyberlab-postgres psql -U cyberlab cyberlab
```

## Muammolarni hal qilish

### Docker ishlamayapti
```bash
# Docker Desktop ni oching yoki:
sudo systemctl start docker
```

### Port band
```bash
# Portni tekshirish
lsof -i :80
lsof -i :3000
lsof -i :5432

# Portni tozalash
kill -9 <PID>
```

### Ma'lumotlar bazasi xatosi
```bash
# PostgreSQL loglarini tekshirish
docker-compose logs postgres

# Qayta ishga tushirish
docker-compose restart postgres
```

### Backend xatosi
```bash
# Backend loglarini tekshirish
docker-compose logs backend

# Cache tozalash
docker-compose exec backend php artisan cache:clear
```

## Yangi funksiya qo'shish

1. Kodni tahrirlash: `frontend/` yoki `backend/`
2. O'zgarishlarni saqlash
3. Frontend: avtomatik yangilanadi (hot reload)
4. Backend: `docker-compose restart backend`

## Deploy qilish

```bash
# Production uchun
docker-compose -f docker-compose.prod.yml up -d
```

## Qo'shimcha ma'lumot

- [API Hujjatlari](docs/API-DOCUMENTATION.md)
- [Arxitektura](devops/docs/ARCHITECTURE.md)
- [Xavfsizlik](security/SECURITY-AUDIT-REPORT.md)
