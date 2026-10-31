# CyberLab Academy - Backend API

Production-ready Laravel 12 API for cybersecurity training platform.

## Tech Stack

- **PHP 8.5**
- **Laravel 12**
- **PostgreSQL 16**
- **Redis 7**
- **Docker**

## Quick Start

### 1. Clone and Install

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Docker Setup

```bash
docker-compose up -d
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan passport:install
```

### 3. Local Development

```bash
# Start all services
docker-compose up -d

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start queue worker
php artisan queue:work

# Start scheduler
php artisan schedule:work
```

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/    # API Controllers
│   │   ├── Middleware/             # Custom Middleware
│   │   ├── Requests/              # Form Requests
│   │   └── Resources/             # API Resources
│   ├── Models/                    # Eloquent Models
│   ├── Services/                  # Business Logic
│   ├── Repositories/              # Data Access
│   ├── Policies/                  # Authorization
│   ├── Events/                    # Event Classes
│   └── Jobs/                      # Queue Jobs
├── database/
│   ├── migrations/                # Database Migrations
│   └── seeders/                   # Database Seeders
├── routes/
│   └── api.php                    # API Routes
├── docker/                        # Docker Configuration
├── docs/                          # Documentation
└── tests/                         # Tests
```

## API Endpoints

### Authentication
- `POST /api/v1/auth/register` - Register
- `POST /api/v1/auth/login` - Login
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/me` - Get current user
- `PUT /api/v1/auth/profile` - Update profile

### Courses
- `GET /api/v1/courses` - List courses
- `POST /api/v1/courses` - Create course
- `GET /api/v1/courses/{id}` - Get course
- `PUT /api/v1/courses/{id}` - Update course
- `DELETE /api/v1/courses/{id}` - Delete course
- `POST /api/v1/courses/{id}/enroll` - Enroll in course
- `GET /api/v1/courses/{id}/progress` - Get progress

### Labs
- `GET /api/v1/labs` - List labs
- `POST /api/v1/labs` - Create lab
- `GET /api/v1/labs/{id}` - Get lab
- `POST /api/v1/labs/{id}/start` - Start lab
- `POST /api/v1/labs/{id}/stop` - Stop lab
- `POST /api/v1/labs/{id}/challenges/{id}/submit` - Submit flag

### Quizzes
- `GET /api/v1/quizzes` - List quizzes
- `POST /api/v1/quizzes` - Create quiz
- `GET /api/v1/quizzes/{id}` - Get quiz with questions
- `POST /api/v1/quizzes/{id}/start` - Start attempt
- `POST /api/v1/quizzes/{id}/submit` - Submit attempt

### Certificates
- `GET /api/v1/certificates` - List certificates
- `GET /api/v1/certificates/verify/{number}` - Verify certificate
- `GET /api/v1/certificates/{id}/download` - Download PDF

### Payments
- `GET /api/v1/plans` - List plans
- `POST /api/v1/subscriptions/{id}/subscribe` - Subscribe
- `POST /api/v1/subscriptions/cancel` - Cancel subscription

### Notifications
- `GET /api/v1/notifications` - List notifications
- `GET /api/v1/notifications/unread-count` - Get unread count
- `POST /api/v1/notifications/{id}/read` - Mark as read

## Architecture

### Service Layer
Business logic is encapsulated in Service classes:
- `AuthService` - Authentication & authorization
- `CourseService` - Course management
- `LabService` - Lab management
- `QuizService` - Quiz system
- `CertificateService` - Certificate generation
- `PaymentService` - Payment processing
- `NotificationService` - Notifications
- `UserService` - User management

### Repository Pattern
Data access is abstracted through Repository classes for clean separation.

### Form Requests
Validation is handled through dedicated Form Request classes.

### API Resources
JSON responses are formatted using API Resource classes.

## Security Features

- Sanctum token authentication
- Role-based access control (RBAC)
- Form request validation
- Rate limiting
- CORS configuration
- Activity logging
- Audit trail

## Queue Jobs

- Send emails
- Generate certificates
- Process payments
- Update rankings
- Sync analytics

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

## Documentation

- [API Documentation](docs/API-DOCUMENTATION.md)
- [Database Schema](../database/docs/DATABASE-DOCUMENTATION.md)

## License

Proprietary - CyberLab Academy
