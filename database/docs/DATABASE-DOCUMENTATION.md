# CyberLab Academy - Database Documentation

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Table Reference](#table-reference)
4. [Relationships](#relationships)
5. [Security](#security)
6. [Performance Optimization](#performance-optimization)
7. [Migration Guide](#migration-guide)

---

## Overview

CyberLab Academy is a comprehensive cybersecurity training platform similar to TryHackMe, HackTheBox Academy, and PortSwigger Academy. The database is designed to support:

- **Multi-role user system** (Students, Instructors, Researchers, Admins)
- **Course management** with modules, lessons, and quizzes
- **Virtual lab environments** with Docker containers and VMs
- **Gamification** (XP, levels, badges, rankings)
- **Certificate verification** with QR codes
- **Payment processing** with subscription plans

### Technology Stack

- **Database**: PostgreSQL 14+
- **Extensions**: uuid-ossp, pgcrypto, pg_trgm
- **ORM**: Laravel Eloquent (Migrations provided)

---

## Architecture

### Entity Relationship Diagram

```
                    ┌─────────────┐
                    │    roles    │
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │role_permissions│
                    └──────┬──────┘
                           │
        ┌──────────────────▼──────────────────┐
        │              users                   │
        └──────────────────┬──────────────────┘
                           │
     ┌─────────────────────┼─────────────────────┬─────────────────────┐
     │                     │                     │                     │
┌────▼────┐          ┌─────▼─────┐         ┌─────▼─────┐         ┌─────▼─────┐
│user_roles│         │sessions   │         │xp_summary │         │notifications│
└─────────┘          └───────────┘         └───────────┘         └───────────┘
                           │
     ┌─────────────────────┼─────────────────────┬─────────────────────┐
     │                     │                     │                     │
┌────▼────┐          ┌─────▼─────┐         ┌─────▼─────┐         ┌─────▼─────┐
│enrollments│        │progress   │         │submissions│         │badges     │
└────┬────┘          └───────────┘         └───────────┘         └───────────┘
     │
┌────▼────┐
│courses  │
└────┬────┘
     │
┌────┼────────────┐
│    │             │
▼    ▼             ▼
┌────┐ ┌────┐ ┌────┐
│mods│ │quiz│ │labs│
└──┬─┘ └────┘ └──┬─┘
   │              │
▼              ▼
┌────┐        ┌────┐
│less│        │mach│
└──┬─┘        └────┘
   │
▼
┌────┐
│quiz│
└──┬─┘
   │
▼
┌────┐
│qstn│
└──┬─┘
   │
▼
┌────┐
│answ│
└────┘
```

---

## Table Reference

### Authentication & Authorization

#### `roles`

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| name | VARCHAR(50) | Role display name |
| slug | VARCHAR(50) | URL-friendly identifier |
| description | TEXT | Role description |
| is_system | BOOLEAN | System roles cannot be deleted |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes**: slug (unique)

---

#### `permissions`

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| name | VARCHAR(100) | Permission display name |
| slug | VARCHAR(100) | URL-friendly identifier |
| module | VARCHAR(50) | Module (courses, labs, users) |
| description | TEXT | Permission description |
| created_at | TIMESTAMP | Creation timestamp |

**Indexes**: slug (unique)

---

#### `role_permissions`

| Column | Type | Description |
|--------|------|-------------|
| role_id | INTEGER (FK) | References roles.id |
| permission_id | INTEGER (FK) | References permissions.id |
| created_at | TIMESTAMP | Creation timestamp |

**Primary Key**: (role_id, permission_id)

---

#### `users`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| email | VARCHAR(255) | Email address |
| email_verified_at | TIMESTAMP | Email verification time |
| password | VARCHAR(255) | Hashed password |
| name | VARCHAR(100) | Full name |
| username | VARCHAR(50) | Unique username |
| avatar | VARCHAR(500) | Profile picture URL |
| bio | TEXT | User biography |
| phone | VARCHAR(20) | Phone number |
| country_code | VARCHAR(5) | Country code |
| timezone | VARCHAR(50) | User timezone |
| language | VARCHAR(10) | Preferred language |
| is_active | BOOLEAN | Account active status |
| is_banned | BOOLEAN | Ban status |
| ban_reason | TEXT | Reason for ban |
| last_login_at | TIMESTAMP | Last login timestamp |
| last_login_ip | INET | Last login IP address |
| login_attempts | INTEGER | Failed login attempts |
| locked_until | TIMESTAMP | Account lock expiration |
| settings | JSONB | User preferences |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes**: email (unique), username (unique), is_active, last_login_at, created_at

---

#### `user_roles`

| Column | Type | Description |
|--------|------|-------------|
| user_id | UUID (FK) | References users.id |
| role_id | INTEGER (FK) | References roles.id |
| assigned_at | TIMESTAMP | Assignment timestamp |
| assigned_by | UUID (FK) | Admin who assigned |

**Primary Key**: (user_id, role_id)

---

#### `user_sessions`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Session identifier |
| user_id | UUID (FK) | References users.id |
| ip_address | INET | Client IP address |
| user_agent | TEXT | Browser user agent |
| payload | TEXT | Session data |
| last_activity | TIMESTAMP | Last activity timestamp |
| created_at | TIMESTAMP | Creation timestamp |

---

### Courses & Learning

#### `course_categories`

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| parent_id | INTEGER (FK) | Self-referencing for hierarchy |
| name | VARCHAR(100) | Category name |
| slug | VARCHAR(100) | URL-friendly identifier |
| description | TEXT | Category description |
| icon | VARCHAR(50) | Icon identifier |
| color | VARCHAR(7) | Hex color code |
| sort_order | INTEGER | Display order |
| is_active | BOOLEAN | Active status |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `courses`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| category_id | INTEGER (FK) | References course_categories.id |
| instructor_id | UUID (FK) | References users.id |
| title | VARCHAR(200) | Course title |
| slug | VARCHAR(200) | URL-friendly identifier |
| subtitle | VARCHAR(300) | Course subtitle |
| description | TEXT | Course description |
| thumbnail | VARCHAR(500) | Thumbnail URL |
| preview_video_url | VARCHAR(500) | Preview video URL |
| difficulty_level | ENUM | beginner/intermediate/advanced/expert |
| estimated_hours | DECIMAL | Estimated completion hours |
| price | DECIMAL | Course price |
| currency | VARCHAR(3) | Currency code (USD) |
| is_free | BOOLEAN | Free course flag |
| is_published | BOOLEAN | Publication status |
| is_featured | BOOLEAN | Featured status |
| enrollment_limit | INTEGER | Max enrollments (NULL=unlimited) |
| requirements | TEXT[] | Course requirements |
| learning_outcomes | TEXT[] | What students will learn |
| tags | TEXT[] | Search tags |
| stats | JSONB | Statistics object |
| settings | JSONB | Course settings |
| published_at | TIMESTAMP | Publication timestamp |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes**: category_id, instructor_id, slug (unique), difficulty_level, is_published, is_featured, price, created_at, full-text search

---

#### `course_enrollments`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| course_id | UUID (FK) | References courses.id |
| status | ENUM | active/completed/paused/dropped |
| progress | DECIMAL | Completion percentage (0-100) |
| enrolled_at | TIMESTAMP | Enrollment timestamp |
| completed_at | TIMESTAMP | Completion timestamp |
| last_accessed_at | TIMESTAMP | Last access timestamp |
| certificate_id | UUID | Certificate reference |

**Unique Constraint**: (user_id, course_id)

---

#### `modules`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| course_id | UUID (FK) | References courses.id |
| title | VARCHAR(200) | Module title |
| description | TEXT | Module description |
| sort_order | INTEGER | Display order |
| is_published | BOOLEAN | Publication status |
| settings | JSONB | Module settings |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `lessons`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| module_id | UUID (FK) | References modules.id |
| title | VARCHAR(200) | Lesson title |
| slug | VARCHAR(200) | URL-friendly identifier |
| content_type | ENUM | text/video/quiz/lab/exam |
| content | TEXT | Markdown content |
| sort_order | INTEGER | Display order |
| is_published | BOOLEAN | Publication status |
| is_free | BOOLEAN | Free preview flag |
| duration_minutes | INTEGER | Estimated duration |
| xp_reward | INTEGER | XP for completion |
| settings | JSONB | Lesson settings |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Unique Constraint**: (module_id, slug)

---

#### `lesson_resources`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lesson_id | UUID (FK) | References lessons.id |
| title | VARCHAR(200) | Resource name |
| file_url | VARCHAR(500) | File URL |
| file_type | VARCHAR(50) | MIME type |
| file_size | INTEGER | File size in bytes |
| sort_order | INTEGER | Display order |
| download_count | INTEGER | Download counter |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `videos`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lesson_id | UUID (FK) | References lessons.id |
| title | VARCHAR(200) | Video title |
| url | VARCHAR(500) | Video URL |
| thumbnail_url | VARCHAR(500) | Thumbnail URL |
| duration_seconds | INTEGER | Video duration |
| hls_url | VARCHAR(500) | HLS streaming URL |
| subtitles | JSONB | Subtitle files array |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `lesson_progress`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| lesson_id | UUID (FK) | References lessons.id |
| status | ENUM | not_started/in_progress/completed |
| progress | DECIMAL | Completion percentage |
| time_spent_seconds | INTEGER | Time spent in seconds |
| last_position_seconds | INTEGER | Video position |
| completed_at | TIMESTAMP | Completion timestamp |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Unique Constraint**: (user_id, lesson_id)

---

### Quizzes & Exams

#### `quizzes`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lesson_id | UUID (FK) | Optional lesson reference |
| course_id | UUID (FK) | References courses.id |
| title | VARCHAR(200) | Quiz title |
| description | TEXT | Quiz description |
| quiz_type | ENUM | practice/graded/exam |
| time_limit_minutes | INTEGER | Time limit |
| passing_score | DECIMAL | Required score (default 60) |
| max_attempts | INTEGER | Maximum attempts |
| shuffle_questions | BOOLEAN | Randomize questions |
| shuffle_answers | BOOLEAN | Randomize answers |
| show_correct_answers | BOOLEAN | Show answers after |
| is_published | BOOLEAN | Publication status |
| settings | JSONB | Quiz settings |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `questions`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| quiz_id | UUID (FK) | References quizzes.id |
| question_type | ENUM | multiple_choice/single_choice/true_false/fill_blank/code/essay |
| question_text | TEXT | Question text |
| question_html | TEXT | HTML formatted question |
| code_snippet | TEXT | Code block |
| code_language | VARCHAR(50) | Programming language |
| explanation | TEXT | Answer explanation |
| points | DECIMAL | Question points |
| sort_order | INTEGER | Display order |
| difficulty | ENUM | easy/medium/hard/expert |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `answers`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| question_id | UUID (FK) | References questions.id |
| answer_text | TEXT | Answer option text |
| is_correct | BOOLEAN | Correct answer flag |
| sort_order | INTEGER | Display order |
| match_pair_id | INTEGER | For matching questions |
| explanation | TEXT | Answer explanation |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `quiz_attempts`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| quiz_id | UUID (FK) | References quizzes.id |
| score | DECIMAL | Score percentage |
| total_points | DECIMAL | Total possible points |
| is_passed | BOOLEAN | Passed status |
| time_taken_seconds | INTEGER | Time taken |
| started_at | TIMESTAMP | Start timestamp |
| completed_at | TIMESTAMP | Completion timestamp |
| answers | JSONB | User answers array |
| ip_address | INET | Client IP |
| user_agent | TEXT | Browser user agent |

---

#### `exam_results`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| quiz_id | UUID (FK) | References quizzes.id |
| score | DECIMAL | Final score |
| total_questions | INTEGER | Question count |
| correct_answers | INTEGER | Correct count |
| time_taken_seconds | INTEGER | Time taken |
| is_passed | BOOLEAN | Passed status |
| certificate_earned | BOOLEAN | Certificate flag |
| attempt_number | INTEGER | Attempt number |
| started_at | TIMESTAMP | Start timestamp |
| completed_at | TIMESTAMP | Completion timestamp |
| reviewed_at | TIMESTAMP | Review timestamp |
| reviewed_by | UUID (FK) | Reviewer reference |
| feedback | TEXT | Review feedback |
| metadata | JSONB | Additional data |

---

### Cyber Labs

#### `labs`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| category_id | INTEGER (FK) | References course_categories.id |
| creator_id | UUID (FK) | References users.id |
| title | VARCHAR(200) | Lab title |
| slug | VARCHAR(200) | URL-friendly identifier |
| description | TEXT | Lab description |
| difficulty | ENUM | easy/medium/hard/insane |
| lab_type | ENUM | challenge/room/box/assessment |
| estimated_time_minutes | INTEGER | Estimated duration |
| points | INTEGER | Points value |
| is_published | BOOLEAN | Publication status |
| is_featured | BOOLEAN | Featured status |
| requires_subscription | BOOLEAN | Subscription required |
| os_type | ENUM | linux/windows/macos/multi |
| tools_required | TEXT[] | Required tools |
| hints | TEXT[] | Hint text array |
| learning_objectives | TEXT[] | Learning goals |
| stats | JSONB | Statistics object |
| settings | JSONB | Lab settings |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

---

#### `machines`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lab_id | UUID (FK) | References labs.id |
| name | VARCHAR(100) | Machine name |
| hostname | VARCHAR(100) | Network hostname |
| ip_address | INET | Public IP |
| internal_ip | INET | Private IP |
| os_type | ENUM | linux/windows/macos |
| os_version | VARCHAR(100) | OS version |
| cpu_cores | INTEGER | CPU cores |
| ram_mb | INTEGER | RAM in MB |
| disk_gb | INTEGER | Disk in GB |
| status | ENUM | running/stopped/building/error/maintenance |
| vm_type | ENUM | vm/container/cloud |
| provider | VARCHAR(50) | Virtualization provider |
| provider_id | VARCHAR(200) | Provider machine ID |
| credentials | JSONB | Encrypted credentials |
| network_config | JSONB | Network settings |
| health_check_url | VARCHAR(500) | Health endpoint |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `lab_instances`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lab_id | UUID (FK) | References labs.id |
| user_id | UUID (FK) | References users.id |
| machine_id | UUID (FK) | References machines.id |
| status | ENUM | pending/starting/running/stopped/expired/error |
| expires_at | TIMESTAMP | Instance expiration |
| started_at | TIMESTAMP | Start timestamp |
| stopped_at | TIMESTAMP | Stop timestamp |
| reset_count | INTEGER | Reset counter |
| last_reset_at | TIMESTAMP | Last reset timestamp |
| connection_info | JSONB | Connection details |
| usage_stats | JSONB | Resource usage |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `challenges`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| lab_id | UUID (FK) | References labs.id |
| title | VARCHAR(200) | Challenge title |
| description | TEXT | Challenge description |
| difficulty | ENUM | easy/medium/hard |
| points | INTEGER | Points value |
| sort_order | INTEGER | Display order |
| challenge_type | ENUM | web/crypto/forensics/reversing/pwn/misc |
| is_active | BOOLEAN | Active status |
| hints | TEXT[] | Hint text array |
| solution | TEXT | Encrypted solution |
| files | JSONB | Attached files |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `flags`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| challenge_id | UUID (FK) | References challenges.id |
| flag_format | VARCHAR(50) | Flag format pattern |
| flag_hash | VARCHAR(256) | Hashed flag value |
| is_case_sensitive | BOOLEAN | Case sensitivity |
| points | INTEGER | Points value |
| sort_order | INTEGER | Display order |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `submissions`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| challenge_id | UUID (FK) | References challenges.id |
| flag_id | UUID (FK) | References flags.id |
| submitted_flag | VARCHAR(500) | Submitted flag |
| is_correct | BOOLEAN | Correctness status |
| ip_address | INET | Client IP |
| user_agent | TEXT | Browser user agent |
| submitted_at | TIMESTAMP | Submission timestamp |
| reviewed_at | TIMESTAMP | Review timestamp |
| reviewed_by | UUID (FK) | Reviewer reference |
| feedback | TEXT | Review feedback |

---

### Gamification

#### `xp_history`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| amount | INTEGER | XP amount (positive/negative) |
| source_type | VARCHAR(50) | Source (course/lab/challenge) |
| source_id | UUID | Source identifier |
| description | TEXT | XP description |
| multiplier | DECIMAL | XP multiplier |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `user_xp_summary`

| Column | Type | Description |
|--------|------|-------------|
| user_id | UUID (PK, FK) | References users.id |
| total_xp | INTEGER | Total XP earned |
| level | INTEGER | Current level |
| xp_to_next_level | INTEGER | XP needed for next level |
| current_streak | INTEGER | Current activity streak |
| longest_streak | INTEGER | Best streak |
| last_activity_at | TIMESTAMP | Last activity |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `levels`

| Column | Type | Description |
|--------|------|-------------|
| level | INTEGER (PK) | Level number |
| title | VARCHAR(50) | Level title |
| min_xp | INTEGER | Minimum XP |
| max_xp | INTEGER | Maximum XP |
| badge_icon | VARCHAR(100) | Level icon |
| rewards | JSONB | Level rewards |

---

#### `rankings`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| ranking_type | VARCHAR(50) | Ranking type |
| period | VARCHAR(20) | Time period |
| user_id | UUID (FK) | References users.id |
| score | INTEGER | Ranking score |
| rank | INTEGER | Position |
| metadata | JSONB | Additional data |
| calculated_at | TIMESTAMP | Calculation timestamp |

**Unique Constraint**: (ranking_type, period, user_id)

---

### Badges

#### `badges`

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| name | VARCHAR(100) | Badge name |
| slug | VARCHAR(100) | URL-friendly identifier |
| description | TEXT | Badge description |
| icon | VARCHAR(500) | Badge icon URL |
| category | VARCHAR(50) | Badge category |
| rarity | ENUM | common/uncommon/rare/epic/legendary |
| xp_reward | INTEGER | XP bonus |
| requirements | JSONB | Earn requirements |
| is_active | BOOLEAN | Active status |
| is_hidden | BOOLEAN | Secret badge flag |
| sort_order | INTEGER | Display order |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `user_badges`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| badge_id | INTEGER (FK) | References badges.id |
| earned_at | TIMESTAMP | Earn timestamp |
| source_type | VARCHAR(50) | Earn source |
| source_id | UUID | Source identifier |
| notified | BOOLEAN | Notification sent |

**Unique Constraint**: (user_id, badge_id)

---

### Certificates

#### `certificates`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| course_id | UUID (FK) | Optional course reference |
| lab_id | UUID (FK) | Optional lab reference |
| certificate_number | VARCHAR(50) | Unique certificate ID |
| certificate_type | ENUM | course_completion/lab_completion/skill_mastery/special |
| title | VARCHAR(200) | Certificate title |
| description | TEXT | Certificate description |
| issued_at | TIMESTAMP | Issue timestamp |
| expires_at | TIMESTAMP | Expiration timestamp |
| pdf_url | VARCHAR(500) | PDF download URL |
| qr_code | VARCHAR(500) | QR code URL |
| verification_url | VARCHAR(500) | Verification URL |
| metadata | JSONB | Additional data |
| revoked | BOOLEAN | Revocation status |
| revoked_at | TIMESTAMP | Revocation timestamp |
| revoked_reason | TEXT | Revocation reason |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `certificate_verifications`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| certificate_id | UUID (FK) | References certificates.id |
| verified_by | UUID (FK) | Optional verifier reference |
| verification_type | ENUM | qr_scan/manual/api/link |
| ip_address | INET | Verifier IP |
| user_agent | TEXT | Verifier user agent |
| verified_at | TIMESTAMP | Verification timestamp |
| result | ENUM | valid/invalid/revoked |

---

### Payments & Subscriptions

#### `plans`

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| name | VARCHAR(100) | Plan name |
| slug | VARCHAR(100) | URL-friendly identifier |
| description | TEXT | Plan description |
| price | DECIMAL | Plan price |
| currency | VARCHAR(3) | Currency code |
| billing_period | ENUM | monthly/quarterly/yearly/lifetime |
| trial_days | INTEGER | Trial period |
| features | JSONB | Features list |
| limits | JSONB | Usage limits |
| is_active | BOOLEAN | Active status |
| is_popular | BOOLEAN | Popular badge |
| sort_order | INTEGER | Display order |
| stripe_price_id | VARCHAR(100) | Stripe price ID |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `subscriptions`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| plan_id | INTEGER (FK) | References plans.id |
| status | ENUM | active/past_due/cancelled/expired/trialing |
| stripe_subscription_id | VARCHAR(100) | Stripe subscription ID |
| stripe_customer_id | VARCHAR(100) | Stripe customer ID |
| trial_start | TIMESTAMP | Trial start |
| trial_end | TIMESTAMP | Trial end |
| current_period_start | TIMESTAMP | Current period start |
| current_period_end | TIMESTAMP | Current period end |
| cancelled_at | TIMESTAMP | Cancellation timestamp |
| ends_at | TIMESTAMP | End timestamp |
| payment_method | JSONB | Payment method details |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

#### `payments`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| subscription_id | UUID (FK) | Optional subscription reference |
| amount | DECIMAL | Payment amount |
| currency | VARCHAR(3) | Currency code |
| status | ENUM | pending/processing/completed/failed/refunded/partially_refunded |
| payment_method | VARCHAR(50) | Payment method |
| stripe_payment_id | VARCHAR(100) | Stripe payment ID |
| stripe_invoice_id | VARCHAR(100) | Stripe invoice ID |
| description | TEXT | Payment description |
| metadata | JSONB | Additional data |
| refunded_at | TIMESTAMP | Refund timestamp |
| refund_reason | TEXT | Refund reason |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

---

### Notifications & Analytics

#### `notifications`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID (PK) | Unique identifier |
| user_id | UUID (FK) | References users.id |
| type | VARCHAR(50) | Notification type |
| title | VARCHAR(200) | Notification title |
| message | TEXT | Notification message |
| data | JSONB | Additional context |
| read_at | TIMESTAMP | Read timestamp |
| action_url | VARCHAR(500) | Action URL |
| priority | ENUM | low/normal/high/urgent |
| channel | ENUM | in_app/email/push/sms |
| sent_at | TIMESTAMP | Send timestamp |
| created_at | TIMESTAMP | Creation timestamp |

---

#### `user_activity_log`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGSERIAL (PK) | Unique identifier |
| user_id | UUID (FK) | Optional user reference |
| action | VARCHAR(100) | Activity action |
| entity_type | VARCHAR(50) | Entity type |
| entity_id | UUID | Entity identifier |
| ip_address | INET | Client IP |
| user_agent | TEXT | Browser user agent |
| metadata | JSONB | Additional data |
| created_at | TIMESTAMP | Creation timestamp |

---

## Relationships

### One-to-Many Relationships

| Parent | Child | Foreign Key |
|--------|-------|-------------|
| users | user_roles | user_id |
| users | course_enrollments | user_id |
| users | lesson_progress | user_id |
| users | quiz_attempts | user_id |
| users | submissions | user_id |
| users | xp_history | user_id |
| users | user_badges | user_id |
| users | certificates | user_id |
| users | payments | user_id |
| users | notifications | user_id |
| roles | role_permissions | role_id |
| permissions | role_permissions | permission_id |
| courses | modules | course_id |
| courses | quizzes | course_id |
| courses | course_enrollments | course_id |
| modules | lessons | module_id |
| lessons | lesson_resources | lesson_id |
| lessons | videos | lesson_id |
| lessons | lesson_progress | lesson_id |
| quizzes | questions | quiz_id |
| questions | answers | question_id |
| labs | machines | lab_id |
| labs | lab_instances | lab_id |
| labs | challenges | lab_id |
| challenges | flags | challenge_id |
| challenges | submissions | challenge_id |
| badges | user_badges | badge_id |
| plans | subscriptions | plan_id |
| subscriptions | payments | subscription_id |

### Many-to-Many Relationships

| Table 1 | Table 2 | Pivot Table |
|---------|---------|-------------|
| users | roles | user_roles |

### Self-Referencing Relationships

| Table | Parent Column | Child Column |
|-------|---------------|--------------|
| course_categories | id | parent_id |

---

## Security

### Row Level Security (RLS)

The following tables have RLS enabled:

- **users**: Users can only access their own data (admins can access all)
- **payments**: Users can only see their own payments
- **subscriptions**: Users can only see their own subscriptions
- **notifications**: Users can only see their own notifications

### Data Protection

1. **Password Hashing**: Use bcrypt via Laravel's Hash facade
2. **Sensitive Data**: Store in JSONB with encryption (credentials, payment methods)
3. **Soft Deletes**: Important data uses soft deletes for recovery
4. **Audit Trail**: user_activity_log tracks all important actions

### Best Practices

- Always use parameterized queries
- Validate input at application layer
- Use UUIDs for public-facing identifiers
- Implement rate limiting on authentication endpoints
- Log all security-relevant events

---

## Performance Optimization

### Indexes

The schema includes comprehensive indexing:

1. **Primary Keys**: All tables have proper primary keys
2. **Foreign Keys**: All foreign keys are indexed
3. **Unique Constraints**: Email, username, slugs are unique
4. **Composite Indexes**: For common query patterns
5. **Full-Text Search**: pg_trgm extension for fuzzy search

### Materialized Views

Consider creating materialized views for:

- Leaderboards (refreshed periodically)
- Course statistics
- Lab statistics
- User rankings

### Query Optimization Tips

1. Use `EXPLAIN ANALYZE` to verify query plans
2. Prefer UUIDs for distributed systems
3. Use JSONB operators for flexible data
4. Consider partitioning for large tables (activity logs)
5. Implement connection pooling (PgBouncer)

---

## Migration Guide

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/0001_01_01_000001_create_roles_and_permissions_tables.php

# Rollback
php artisan migrate:rollback

# Fresh start (WARNING: deletes all data)
php artisan migrate:fresh
```

### Seeding Data

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=RoleSeeder
```

### Production Considerations

1. **Backup before migration**
2. **Test on staging first**
3. **Use transactions for complex migrations**
4. **Monitor performance after migration**
5. **Update application code to match schema changes**

---

## File Structure

```
database/
├── migrations/
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
│   └── schema.sql
└── docs/
    ├── ER-DIAGRAM.md
    └── DATABASE-DOCUMENTATION.md
```

---

## License

This database schema is part of the CyberLab Academy project.
