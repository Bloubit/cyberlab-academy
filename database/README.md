# CyberLab Academy - Database

## Overview

Complete PostgreSQL database architecture for a cybersecurity training platform.

## Quick Start

### 1. PostgreSQL Setup

```bash
# Create database
createdb cyberlab_academy

# Connect to database
psql -U postgres -d cyberlab_academy
```

### 2. Run SQL Schema

```bash
# Option 1: Direct SQL execution
psql -U postgres -d cyberlab_academy -f database/sql/schema.sql

# Option 2: Via Laravel migrations
php artisan migrate
```

### 3. Verify Installation

```bash
# Check tables
\dt

# Check counts
SELECT 'roles' as table_name, COUNT(*) FROM roles
UNION ALL
SELECT 'permissions', COUNT(*)
UNION ALL
SELECT 'levels', COUNT(*)
UNION ALL
SELECT 'badges', COUNT(*)
UNION ALL
SELECT 'plans', COUNT(*);
```

## File Structure

```
database/
├── migrations/                    # Laravel migration files
│   ├── 0001_01_01_000001_create_roles_and_permissions_tables.php
│   ├── 0001_01_01_000002_create_users_tables.php
│   ├── 0001_01_01_000003_create_courses_tables.php
│   ├── 0001_01_01_000004_create_modules_and_lessons_tables.php
│   ├── 0001_01_01_000005_create_quizzes_tables.php
│   ├── 0001_01_01_000006_create_labs_tables.php
│   ├── 0001_01_01_000007_create_gamification_tables.php
│   ├── 0001_01_01_000008_create_badges_tables.php
│   ├── 0001_01_01_000009_create_certificates_tables.php
│   ├── 0001_01_01_000010_create_payments_tables.php
│   └── 0001_01_01_000011_create_notifications_and_analytics_tables.php
├── sql/
│   └── schema.sql                 # Complete SQL schema
└── docs/
    ├── ER-DIAGRAM.md              # Entity relationship diagram
    └── DATABASE-DOCUMENTATION.md  # Full documentation
```

## Tables Summary

### Authentication & Authorization
- **roles** - User roles (Student, Instructor, Admin)
- **permissions** - System permissions
- **role_permissions** - Role-permission mapping
- **users** - User accounts
- **user_roles** - User-role mapping
- **user_sessions** - Active sessions

### Courses & Learning
- **course_categories** - Course categories (hierarchical)
- **courses** - Course definitions
- **course_enrollments** - User enrollments
- **modules** - Course modules/sections
- **lessons** - Lesson content
- **lesson_resources** - Lesson attachments
- **videos** - Video resources
- **lesson_progress** - Learning progress

### Quizzes & Exams
- **quizzes** - Quiz definitions
- **questions** - Quiz questions
- **answers** - Answer options
- **quiz_attempts** - User attempts
- **exam_results** - Exam outcomes

### Cyber Labs
- **labs** - Lab definitions
- **machines** - Virtual machines
- **lab_instances** - User instances
- **challenges** - Lab challenges
- **flags** - Hidden flags
- **submissions** - Flag submissions

### Gamification
- **xp_history** - XP transactions
- **user_xp_summary** - XP totals
- **levels** - Level definitions
- **rankings** - Global rankings

### Badges
- **badges** - Badge definitions
- **user_badges** - User badges

### Certificates
- **certificates** - Certificate definitions
- **certificate_verifications** - Verification logs

### Payments & Subscriptions
- **plans** - Subscription plans
- **subscriptions** - User subscriptions
- **payments** - Payment transactions

### Notifications & Analytics
- **notifications** - User notifications
- **user_activity_log** - Activity tracking
- **course_analytics** - Course metrics
- **lab_analytics** - Lab metrics

## Key Features

### UUID Primary Keys
All user-facing tables use UUIDs for:
- Distributed system support
- Security (non-sequential IDs)
- No information leakage

### JSONB Columns
Flexible data storage for:
- User settings
- Course metadata
- Lab configurations
- Statistics

### Soft Deletes
Important data uses `deleted_at` for:
- Data recovery
- Audit trails
- Referential integrity

### Automatic Timestamps
All tables have:
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Triggers
- Auto-update `updated_at` columns
- Update user XP summary on XP changes
- Update course statistics on enrollments

## Performance

### Indexes
Comprehensive indexing for:
- Primary keys (all tables)
- Foreign keys (all relationships)
- Unique constraints (email, username, slugs)
- Common query patterns
- Full-text search (pg_trgm)

### Views
- **v_user_dashboard** - User dashboard stats
- **v_course_leaderboard** - Course rankings
- **v_lab_statistics** - Lab performance

## Security

### Row Level Security (RLS)
- Users can only access their own data
- Admins have full access
- Sensitive tables are protected

### Data Protection
- Passwords hashed with bcrypt
- Sensitive data encrypted
- Soft deletes for recovery
- Activity logging for audit

## Migration Guide

### Development
```bash
# Fresh start
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback
```

### Production
```bash
# Backup database first
pg_dump -U postgres cyberlab_academy > backup.sql

# Run migrations
php artisan migrate --force
```

## Support

For issues or questions, refer to:
- DATABASE-DOCUMENTATION.md - Full documentation
- ER-DIAGRAM.md - Visual diagrams
- schema.sql - Complete SQL schema
