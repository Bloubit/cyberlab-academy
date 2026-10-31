# CyberLab Academy API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All protected routes require Bearer token:
```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### Register
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "password": "password123",
    "password_confirmation": "password123"
}

Response: 201
{
    "message": "Registration successful",
    "user": {...},
    "token": "1|abc123..."
}
```

### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}

Response: 200
{
    "message": "Login successful",
    "user": {...},
    "token": "1|abc123..."
}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer {token}

Response: 200
{
    "message": "Logged out successfully"
}
```

### Get Current User
```http
GET /auth/me
Authorization: Bearer {token}

Response: 200
{
    "data": {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com",
        ...
    }
}
```

### Update Profile
```http
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe Updated",
    "bio": "Security researcher",
    "timezone": "UTC"
}

Response: 200
{
    "data": {...}
}
```

---

## Course Endpoints

### List Courses
```http
GET /courses
Query: category_id, difficulty, search, is_free, per_page

Response: 200
{
    "data": [...],
    "meta": {...}
}
```

### Get Course
```http
GET /courses/{id}

Response: 200
{
    "data": {
        "id": "uuid",
        "title": "Web Security Basics",
        "modules": [...],
        ...
    }
}
```

### Create Course
```http
POST /courses
Authorization: Bearer {token}
Content-Type: application/json

{
    "category_id": 1,
    "title": "Web Security Basics",
    "description": "Learn web security",
    "difficulty_level": "beginner",
    "price": 0,
    "is_free": true
}

Response: 201
```

### Update Course
```http
PUT /courses/{id}
Authorization: Bearer {token}

Response: 200
```

### Delete Course
```http
DELETE /courses/{id}
Authorization: Bearer {token}

Response: 200
```

### Enroll in Course
```http
POST /courses/{id}/enroll
Authorization: Bearer {token}

Response: 201
{
    "message": "Enrolled successfully",
    "enrollment": {...}
}
```

### Get Course Progress
```http
GET /courses/{id}/progress
Authorization: Bearer {token}

Response: 200
{
    "progress": {
        "enrolled": true,
        "progress": 45.5,
        "total_lessons": 20,
        "completed_lessons": 9
    }
}
```

---

## Module Endpoints

### List Modules
```http
GET /courses/{courseId}/modules

Response: 200
{
    "data": [...]
}
```

### Create Module
```http
POST /courses/{courseId}/modules
Authorization: Bearer {token}

{
    "title": "Module 1: Introduction",
    "description": "Basic concepts"
}

Response: 201
```

### Update Module
```http
PUT /courses/{courseId}/modules/{moduleId}
Authorization: Bearer {token}

Response: 200
```

### Delete Module
```http
DELETE /courses/{courseId}/modules/{moduleId}
Authorization: Bearer {token}

Response: 200
```

---

## Lesson Endpoints

### List Lessons
```http
GET /modules/{moduleId}/lessons

Response: 200
{
    "data": [...]
}
```

### Create Lesson
```http
POST /modules/{moduleId}/lessons
Authorization: Bearer {token}

{
    "title": "Lesson 1",
    "content_type": "text",
    "content": "# Markdown content",
    "duration_minutes": 30,
    "xp_reward": 10
}

Response: 201
```

### Complete Lesson
```http
POST /lessons/{id}/complete
Authorization: Bearer {token}

Response: 200
{
    "message": "Lesson completed successfully"
}
```

---

## Quiz Endpoints

### List Quizzes
```http
GET /quizzes?course_id={id}

Response: 200
{
    "data": [...]
}
```

### Get Quiz with Questions
```http
GET /quizzes/{id}

Response: 200
{
    "data": {
        "id": "uuid",
        "questions": [
            {
                "id": "uuid",
                "question_text": "...",
                "answers": [...]
            }
        ]
    }
}
```

### Create Quiz
```http
POST /quizzes
Authorization: Bearer {token}

{
    "course_id": "uuid",
    "title": "Final Exam",
    "quiz_type": "exam",
    "passing_score": 70,
    "max_attempts": 3
}

Response: 201
```

### Add Question
```http
POST /quizzes/{id}/questions
Authorization: Bearer {token}

{
    "question_type": "multiple_choice",
    "question_text": "What is SQL injection?",
    "points": 10,
    "answers": [
        {"answer_text": "Attack type", "is_correct": true},
        {"answer_text": "Network protocol", "is_correct": false}
    ]
}

Response: 201
```

### Start Quiz Attempt
```http
POST /quizzes/{id}/start
Authorization: Bearer {token}

Response: 201
{
    "attempt": {
        "id": "uuid",
        "started_at": "..."
    }
}
```

### Submit Quiz
```http
POST /quizzes/{id}/submit
Authorization: Bearer {token}

{
    "attempt_id": "uuid",
    "answers": [
        {
            "question_id": "uuid",
            "answer_ids": ["uuid1", "uuid2"]
        }
    ]
}

Response: 200
{
    "attempt": {
        "score": 85.00,
        "is_passed": true
    }
}
```

---

## Lab Endpoints

### List Labs
```http
GET /labs
Query: difficulty, lab_type, category_id, search

Response: 200
{
    "data": [...]
}
```

### Get Lab
```http
GET /labs/{id}

Response: 200
{
    "data": {
        "id": "uuid",
        "title": "SQL Injection Lab",
        "challenges": [...],
        "machines": [...]
    }
}
```

### Create Lab
```http
POST /labs
Authorization: Bearer {token}

{
    "title": "New Lab",
    "difficulty": "medium",
    "lab_type": "challenge",
    "os_type": "linux"
}

Response: 201
```

### Start Lab Instance
```http
POST /labs/{id}/start
Authorization: Bearer {token}

Response: 201
{
    "instance": {
        "id": "uuid",
        "status": "running",
        "expires_at": "..."
    },
    "connection": {
        "ip": "192.168.1.100",
        "port": 22,
        "protocol": "ssh"
    }
}
```

### Stop Lab Instance
```http
POST /labs/{id}/stop
Authorization: Bearer {token}

Response: 200
```

### Submit Flag
```http
POST /labs/{labId}/challenges/{challengeId}/submit
Authorization: Bearer {token}

{
    "flag": "FLAG{sql_1nj3ct10n_f0und}"
}

Response: 200
{
    "message": "Correct flag!",
    "is_correct": true
}
```

---

## Certificate Endpoints

### List Certificates
```http
GET /certificates
Authorization: Bearer {token}

Response: 200
{
    "data": [...]
}
```

### Verify Certificate (Public)
```http
GET /certificates/verify/{number}

Response: 200
{
    "valid": true,
    "certificate": {...},
    "message": "Certificate is valid"
}
```

### Download Certificate
```http
GET /certificates/{id}/download
Authorization: Bearer {token}

Response: 200 (PDF file)
```

---

## Payment Endpoints

### List Plans
```http
GET /plans

Response: 200
{
    "data": [
        {
            "id": 1,
            "name": "Pro",
            "price": 29.99,
            "features": [...]
        }
    ]
}
```

### Subscribe
```http
POST /subscriptions/{planId}/subscribe
Authorization: Bearer {token}

{
    "payment_method": "card",
    "payment_token": "tok_xxx"
}

Response: 201
```

### Cancel Subscription
```http
POST /subscriptions/cancel
Authorization: Bearer {token}

Response: 200
```

### Payment History
```http
GET /payments
Authorization: Bearer {token}

Response: 200
{
    "payments": [...]
}
```

---

## Notification Endpoints

### List Notifications
```http
GET /notifications
Query: unread_only, per_page

Response: 200
{
    "data": [...]
}
```

### Get Unread Count
```http
GET /notifications/unread-count
Authorization: Bearer {token}

Response: 200
{
    "count": 5
}
```

### Mark as Read
```http
POST /notifications/{id}/read
Authorization: Bearer {token}

Response: 200
```

### Mark All as Read
```http
POST /notifications/read-all
Authorization: Bearer {token}

Response: 200
```

---

## Error Responses

### 400 Bad Request
```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### 401 Unauthorized
```json
{
    "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
    "message": "Forbidden"
}
```

### 404 Not Found
```json
{
    "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
    "message": "Validation failed",
    "errors": {...}
}
```

### 429 Too Many Requests
```json
{
    "message": "Too many requests"
}
```

### 500 Server Error
```json
{
    "message": "Internal server error"
}
```

---

## Rate Limits

- **Public routes**: 60 requests per minute
- **Authenticated routes**: 120 requests per minute
- **Login attempts**: 5 per minute

---

## Pagination

All list endpoints support pagination:
```
GET /courses?page=1&per_page=15

{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    }
}
```
