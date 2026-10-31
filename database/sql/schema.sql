-- =====================================================
-- CyberLab Academy - Complete Database Schema
-- PostgreSQL 14+ 
-- Version: 1.0.0
-- =====================================================

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- For fuzzy text search

-- =====================================================
-- 1. ROLES & PERMISSIONS
-- =====================================================

-- Roles table
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE, -- System roles cannot be deleted
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Permissions table
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL, -- e.g., 'courses', 'labs', 'users'
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Role-Permission pivot table
CREATE TABLE role_permissions (
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    PRIMARY KEY (role_id, permission_id)
);

-- =====================================================
-- 2. USERS
-- =====================================================

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP WITH TIME ZONE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE,
    avatar VARCHAR(500),
    bio TEXT,
    phone VARCHAR(20),
    country_code VARCHAR(5),
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    is_active BOOLEAN DEFAULT TRUE,
    is_banned BOOLEAN DEFAULT FALSE,
    ban_reason TEXT,
    last_login_at TIMESTAMP WITH TIME ZONE,
    last_login_ip INET,
    login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP WITH TIME ZONE,
    settings JSONB DEFAULT '{}',
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE -- Soft delete
);

-- User-Role pivot table
CREATE TABLE user_roles (
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    assigned_by UUID REFERENCES users(id),
    PRIMARY KEY (user_id, role_id)
);

-- User sessions for security tracking
CREATE TABLE user_sessions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip_address INET NOT NULL,
    user_agent TEXT,
    payload TEXT,
    last_activity TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================================
-- 3. COURSES & CATEGORIES
-- =====================================================

-- Course categories (hierarchical)
CREATE TABLE course_categories (
    id SERIAL PRIMARY KEY,
    parent_id INTEGER REFERENCES course_categories(id) ON DELETE SET NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7), -- Hex color
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Courses
CREATE TABLE courses (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    category_id INTEGER REFERENCES course_categories(id) ON DELETE SET NULL,
    instructor_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    subtitle VARCHAR(300),
    description TEXT,
    thumbnail VARCHAR(500),
    preview_video_url VARCHAR(500),
    difficulty_level VARCHAR(20) CHECK (difficulty_level IN ('beginner', 'intermediate', 'advanced', 'expert')),
    estimated_hours DECIMAL(5,1),
    price DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'USD',
    is_free BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    enrollment_limit INTEGER, -- NULL = unlimited
    requirements TEXT[],
    learning_outcomes TEXT[],
    tags TEXT[],
    stats JSONB DEFAULT '{"enrollments": 0, "completions": 0, "avg_rating": 0, "total_ratings": 0}',
    settings JSONB DEFAULT '{}',
    published_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Course enrollments
CREATE TABLE course_enrollments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'paused', 'dropped')),
    progress DECIMAL(5,2) DEFAULT 0.00 CHECK (progress >= 0 AND progress <= 100),
    enrolled_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE,
    last_accessed_at TIMESTAMP WITH TIME ZONE,
    certificate_id UUID,
    UNIQUE(user_id, course_id)
);

-- =====================================================
-- 4. MODULES & LESSONS
-- =====================================================

-- Course modules (sections)
CREATE TABLE modules (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    is_published BOOLEAN DEFAULT TRUE,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Lessons
CREATE TABLE lessons (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    module_id UUID NOT NULL REFERENCES modules(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    content_type VARCHAR(20) NOT NULL CHECK (content_type IN ('text', 'video', 'quiz', 'lab', 'exam')),
    content TEXT, -- Markdown content
    sort_order INTEGER DEFAULT 0,
    is_published BOOLEAN DEFAULT TRUE,
    is_free BOOLEAN DEFAULT FALSE,
    duration_minutes INTEGER,
    xp_reward INTEGER DEFAULT 10,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(module_id, slug)
);

-- Lesson resources (attachments)
CREATE TABLE lesson_resources (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lesson_id UUID NOT NULL REFERENCES lessons(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INTEGER,
    sort_order INTEGER DEFAULT 0,
    download_count INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Video resources
CREATE TABLE videos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lesson_id UUID NOT NULL REFERENCES lessons(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500),
    duration_seconds INTEGER,
    hls_url VARCHAR(500), -- For streaming
    subtitles JSONB DEFAULT '[]', -- [{lang: 'en', url: '...'}]
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Lesson progress tracking
CREATE TABLE lesson_progress (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    lesson_id UUID NOT NULL REFERENCES lessons(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'not_started' CHECK (status IN ('not_started', 'in_progress', 'completed')),
    progress DECIMAL(5,2) DEFAULT 0.00,
    time_spent_seconds INTEGER DEFAULT 0,
    last_position_seconds INTEGER DEFAULT 0, -- Video position
    completed_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(user_id, lesson_id)
);

-- =====================================================
-- 5. QUIZZES & EXAMS
-- =====================================================

-- Quizzes
CREATE TABLE quizzes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lesson_id UUID REFERENCES lessons(id) ON DELETE SET NULL,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    quiz_type VARCHAR(20) DEFAULT 'practice' CHECK (quiz_type IN ('practice', 'graded', 'exam')),
    time_limit_minutes INTEGER,
    passing_score DECIMAL(5,2) DEFAULT 60.00,
    max_attempts INTEGER DEFAULT 3,
    shuffle_questions BOOLEAN DEFAULT TRUE,
    shuffle_answers BOOLEAN DEFAULT TRUE,
    show_correct_answers BOOLEAN DEFAULT TRUE,
    is_published BOOLEAN DEFAULT TRUE,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Quiz questions
CREATE TABLE questions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    quiz_id UUID NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    question_type VARCHAR(20) NOT NULL CHECK (question_type IN ('multiple_choice', 'single_choice', 'true_false', 'fill_blank', 'code', 'essay')),
    question_text TEXT NOT NULL,
    question_html TEXT,
    code_snippet TEXT,
    code_language VARCHAR(50),
    explanation TEXT,
    points DECIMAL(5,2) DEFAULT 1.00,
    sort_order INTEGER DEFAULT 0,
    difficulty VARCHAR(20) DEFAULT 'medium' CHECK (difficulty IN ('easy', 'medium', 'hard', 'expert')),
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Answer options
CREATE TABLE answers (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    question_id UUID NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    match_pair_id INTEGER, -- For matching questions
    explanation TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Quiz attempts
CREATE TABLE quiz_attempts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    quiz_id UUID NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    score DECIMAL(5,2),
    total_points DECIMAL(5,2),
    is_passed BOOLEAN DEFAULT FALSE,
    time_taken_seconds INTEGER,
    started_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE,
    answers JSONB DEFAULT '[]', -- [{question_id, answer_id, is_correct}]
    ip_address INET,
    user_agent TEXT
);

-- Exam results
CREATE TABLE exam_results (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    quiz_id UUID NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    score DECIMAL(5,2) NOT NULL,
    total_questions INTEGER NOT NULL,
    correct_answers INTEGER NOT NULL,
    time_taken_seconds INTEGER,
    is_passed BOOLEAN NOT NULL,
    certificate_earned BOOLEAN DEFAULT FALSE,
    attempt_number INTEGER DEFAULT 1,
    started_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE,
    reviewed_at TIMESTAMP WITH TIME ZONE,
    reviewed_by UUID REFERENCES users(id),
    feedback TEXT,
    metadata JSONB DEFAULT '{}'
);

-- =====================================================
-- 6. CYBER LABS
-- =====================================================

-- Labs (challenges/rooms)
CREATE TABLE labs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    category_id INTEGER REFERENCES course_categories(id) ON DELETE SET NULL,
    creator_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    difficulty VARCHAR(20) NOT NULL CHECK (difficulty IN ('easy', 'medium', 'hard', 'insane')),
    lab_type VARCHAR(30) NOT NULL CHECK (lab_type IN ('challenge', 'room', 'box', 'assessment')),
    estimated_time_minutes INTEGER,
    points INTEGER DEFAULT 100,
    is_published BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    requires_subscription BOOLEAN DEFAULT FALSE,
    os_type VARCHAR(20) CHECK (os_type IN ('linux', 'windows', 'macos', 'multi')),
    tools_required TEXT[],
    hints TEXT[],
    learning_objectives TEXT[],
    stats JSONB DEFAULT '{"solves": 0, "attempts": 0, "avg_time": 0}',
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Lab machines (VMs)
CREATE TABLE machines (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lab_id UUID NOT NULL REFERENCES labs(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    hostname VARCHAR(100),
    ip_address INET,
    internal_ip INET,
    os_type VARCHAR(20) NOT NULL,
    os_version VARCHAR(100),
    cpu_cores INTEGER DEFAULT 2,
    ram_mb INTEGER DEFAULT 2048,
    disk_gb INTEGER DEFAULT 20,
    status VARCHAR(20) DEFAULT 'stopped' CHECK (status IN ('running', 'stopped', 'building', 'error', 'maintenance')),
    vm_type VARCHAR(20) CHECK (vm_type IN ('vm', 'container', 'cloud')),
    provider VARCHAR(50), -- e.g., 'qemu', 'docker', 'aws'
    provider_id VARCHAR(200),
    credentials JSONB, -- Encrypted: {username, password, ssh_key}
    network_config JSONB DEFAULT '{}',
    health_check_url VARCHAR(500),
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Lab instances (user-specific)
CREATE TABLE lab_instances (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lab_id UUID NOT NULL REFERENCES labs(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    machine_id UUID REFERENCES machines(id) ON DELETE SET NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'starting', 'running', 'stopped', 'expired', 'error')),
    expires_at TIMESTAMP WITH TIME ZONE,
    started_at TIMESTAMP WITH TIME ZONE,
    stopped_at TIMESTAMP WITH TIME ZONE,
    reset_count INTEGER DEFAULT 0,
    last_reset_at TIMESTAMP WITH TIME ZONE,
    connection_info JSONB, -- {ip, port, protocol}
    usage_stats JSONB DEFAULT '{"cpu": 0, "memory": 0, "disk": 0}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================================
-- 7. CHALLENGES & FLAGS
-- =====================================================

-- Challenges within labs
CREATE TABLE challenges (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lab_id UUID NOT NULL REFERENCES labs(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    difficulty VARCHAR(20) CHECK (difficulty IN ('easy', 'medium', 'hard')),
    points INTEGER DEFAULT 100,
    sort_order INTEGER DEFAULT 0,
    challenge_type VARCHAR(30) CHECK (challenge_type IN ('web', 'crypto', 'forensics', 'reversing', 'pwn', 'misc')),
    is_active BOOLEAN DEFAULT TRUE,
    hints TEXT[],
    solution TEXT, -- Encrypted
    files JSONB DEFAULT '[]', -- [{name, url, size}]
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Flags (hidden strings to find)
CREATE TABLE flags (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    challenge_id UUID NOT NULL REFERENCES challenges(id) ON DELETE CASCADE,
    flag_format VARCHAR(50), -- e.g., 'FLAG{...}', 'HTB{...}'
    flag_hash VARCHAR(256) NOT NULL, -- Hashed flag value
    is_case_sensitive BOOLEAN DEFAULT FALSE,
    points INTEGER DEFAULT 100,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- User flag submissions
CREATE TABLE submissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    challenge_id UUID NOT NULL REFERENCES challenges(id) ON DELETE CASCADE,
    flag_id UUID REFERENCES flags(id) ON DELETE SET NULL,
    submitted_flag VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    ip_address INET,
    user_agent TEXT,
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    reviewed_at TIMESTAMP WITH TIME ZONE,
    reviewed_by UUID REFERENCES users(id),
    feedback TEXT
);

-- =====================================================
-- 8. GAMIFICATION (XP, LEVELS, RANKINGS)
-- =====================================================

-- XP history
CREATE TABLE xp_history (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount INTEGER NOT NULL, -- Positive = earned, Negative = deducted
    source_type VARCHAR(50) NOT NULL, -- 'course', 'lab', 'challenge', 'quiz', 'badge', 'streak'
    source_id UUID, -- ID of the source
    description TEXT,
    multiplier DECIMAL(3,2) DEFAULT 1.00, -- For bonus XP
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- User XP summary (materialized for performance)
CREATE TABLE user_xp_summary (
    user_id UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    total_xp INTEGER DEFAULT 0,
    level INTEGER DEFAULT 1,
    xp_to_next_level INTEGER DEFAULT 100,
    current_streak INTEGER DEFAULT 0,
    longest_streak INTEGER DEFAULT 0,
    last_activity_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Levels configuration
CREATE TABLE levels (
    level INTEGER PRIMARY KEY,
    title VARCHAR(50) NOT NULL,
    min_xp INTEGER NOT NULL,
    max_xp INTEGER NOT NULL,
    badge_icon VARCHAR(100),
    rewards JSONB DEFAULT '{}'
);

-- Global rankings
CREATE TABLE rankings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ranking_type VARCHAR(50) NOT NULL, -- 'global', 'course', 'lab', 'monthly'
    period VARCHAR(20), -- 'daily', 'weekly', 'monthly', 'all_time'
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    score INTEGER NOT NULL,
    rank INTEGER NOT NULL,
    metadata JSONB DEFAULT '{}',
    calculated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(ranking_type, period, user_id)
);

-- =====================================================
-- 9. BADGES
-- =====================================================

-- Badge definitions
CREATE TABLE badges (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(500),
    category VARCHAR(50), -- 'achievement', 'skill', 'milestone', 'special'
    rarity VARCHAR(20) DEFAULT 'common' CHECK (rarity IN ('common', 'uncommon', 'rare', 'epic', 'legendary')),
    xp_reward INTEGER DEFAULT 0,
    requirements JSONB, -- {type: 'labs_completed', count: 10}
    is_active BOOLEAN DEFAULT TRUE,
    is_hidden BOOLEAN DEFAULT FALSE, -- Secret badges
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- User badges
CREATE TABLE user_badges (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    badge_id INTEGER NOT NULL REFERENCES badges(id) ON DELETE CASCADE,
    earned_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    source_type VARCHAR(50),
    source_id UUID,
    notified BOOLEAN DEFAULT FALSE,
    UNIQUE(user_id, badge_id)
);

-- =====================================================
-- 10. CERTIFICATES
-- =====================================================

-- Certificates
CREATE TABLE certificates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id UUID REFERENCES courses(id) ON DELETE SET NULL,
    lab_id UUID REFERENCES labs(id) ON DELETE SET NULL,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    certificate_type VARCHAR(30) NOT NULL CHECK (certificate_type IN ('course_completion', 'lab_completion', 'skill_mastery', 'special')),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    issued_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    expires_at TIMESTAMP WITH TIME ZONE,
    pdf_url VARCHAR(500),
    qr_code VARCHAR(500),
    verification_url VARCHAR(500),
    metadata JSONB DEFAULT '{}',
    revoked BOOLEAN DEFAULT FALSE,
    revoked_at TIMESTAMP WITH TIME ZONE,
    revoked_reason TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Certificate verifications
CREATE TABLE certificate_verifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    certificate_id UUID NOT NULL REFERENCES certificates(id) ON DELETE CASCADE,
    verified_by UUID REFERENCES users(id),
    verification_type VARCHAR(30) CHECK (verification_type IN ('qr_scan', 'manual', 'api', 'link')),
    ip_address INET,
    user_agent TEXT,
    verified_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    result VARCHAR(20) CHECK (result IN ('valid', 'invalid', 'revoked'))
);

-- =====================================================
-- 11. PAYMENTS & SUBSCRIPTIONS
-- =====================================================

-- Subscription plans
CREATE TABLE plans (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    billing_period VARCHAR(20) NOT NULL CHECK (billing_period IN ('monthly', 'quarterly', 'yearly', 'lifetime')),
    trial_days INTEGER DEFAULT 0,
    features JSONB DEFAULT '[]',
    limits JSONB DEFAULT '{}', -- {labs_per_day, courses, etc.}
    is_active BOOLEAN DEFAULT TRUE,
    is_popular BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    stripe_price_id VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- User subscriptions
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan_id INTEGER NOT NULL REFERENCES plans(id) ON DELETE RESTRICT,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'past_due', 'cancelled', 'expired', 'trialing')),
    stripe_subscription_id VARCHAR(100),
    stripe_customer_id VARCHAR(100),
    trial_start TIMESTAMP WITH TIME ZONE,
    trial_end TIMESTAMP WITH TIME ZONE,
    current_period_start TIMESTAMP WITH TIME ZONE,
    current_period_end TIMESTAMP WITH TIME ZONE,
    cancelled_at TIMESTAMP WITH TIME ZONE,
    ends_at TIMESTAMP WITH TIME ZONE,
    payment_method JSONB,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Payment transactions
CREATE TABLE payments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    subscription_id UUID REFERENCES subscriptions(id) ON DELETE SET NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded')),
    payment_method VARCHAR(50), -- 'card', 'paypal', 'crypto'
    stripe_payment_id VARCHAR(100),
    stripe_invoice_id VARCHAR(100),
    description TEXT,
    metadata JSONB DEFAULT '{}',
    refunded_at TIMESTAMP WITH TIME ZONE,
    refund_reason TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================================
-- 12. NOTIFICATIONS
-- =====================================================

CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'badge_earned', 'course_update', 'lab_reminder', etc.
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data JSONB DEFAULT '{}', -- Additional context
    read_at TIMESTAMP WITH TIME ZONE,
    action_url VARCHAR(500),
    priority VARCHAR(20) DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    channel VARCHAR(20) DEFAULT 'in_app' CHECK (channel IN ('in_app', 'email', 'push', 'sms')),
    sent_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================================
-- 13. ANALYTICS & TRACKING
-- =====================================================

-- User activity log
CREATE TABLE user_activity_log (
    id BIGSERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id UUID,
    ip_address INET,
    user_agent TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Course analytics
CREATE TABLE course_analytics (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    views INTEGER DEFAULT 0,
    enrollments INTEGER DEFAULT 0,
    completions INTEGER DEFAULT 0,
    avg_time_minutes DECIMAL(10,2),
    drop_off_rate DECIMAL(5,2),
    UNIQUE(course_id, date)
);

-- Lab analytics
CREATE TABLE lab_analytics (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    lab_id UUID NOT NULL REFERENCES labs(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    starts INTEGER DEFAULT 0,
    completions INTEGER DEFAULT 0,
    avg_time_minutes DECIMAL(10,2),
    success_rate DECIMAL(5,2),
    UNIQUE(lab_id, date)
);

-- =====================================================
-- INDEXES
-- =====================================================

-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_last_login ON users(last_login_at);
CREATE INDEX idx_users_created ON users(created_at);

-- Courses
CREATE INDEX idx_courses_category ON courses(category_id);
CREATE INDEX idx_courses_instructor ON courses(instructor_id);
CREATE INDEX idx_courses_slug ON courses(slug);
CREATE INDEX idx_courses_difficulty ON courses(difficulty_level);
CREATE INDEX idx_courses_published ON courses(is_published);
CREATE INDEX idx_courses_featured ON courses(is_featured);
CREATE INDEX idx_courses_price ON courses(price);
CREATE INDEX idx_courses_created ON courses(created_at);
CREATE INDEX idx_courses_search ON courses USING gin(to_tsvector('english', title || ' ' || COALESCE(subtitle, '')));

-- Modules & Lessons
CREATE INDEX idx_modules_course ON modules(course_id);
CREATE INDEX idx_lessons_module ON lessons(module_id);
CREATE INDEX idx_lessons_type ON lessons(content_type);

-- Labs
CREATE INDEX idx_labs_category ON labs(category_id);
CREATE INDEX idx_labs_creator ON labs(creator_id);
CREATE INDEX idx_labs_slug ON labs(slug);
CREATE INDEX idx_labs_difficulty ON labs(difficulty);
CREATE INDEX idx_labs_type ON labs(lab_type);
CREATE INDEX idx_labs_published ON labs(is_published);

-- Machines
CREATE INDEX idx_machines_lab ON machines(lab_id);
CREATE INDEX idx_machines_status ON machines(status);

-- Lab instances
CREATE INDEX idx_lab_instances_user ON lab_instances(user_id);
CREATE INDEX idx_lab_instances_lab ON lab_instances(lab_id);
CREATE INDEX idx_lab_instances_status ON lab_instances(status);

-- Challenges
CREATE INDEX idx_challenges_lab ON challenges(lab_id);
CREATE INDEX idx_challenges_type ON challenges(challenge_type);

-- Submissions
CREATE INDEX idx_submissions_user ON submissions(user_id);
CREATE INDEX idx_submissions_challenge ON submissions(challenge_id);
CREATE INDEX idx_submissions_correct ON submissions(is_correct);

-- XP
CREATE INDEX idx_xp_history_user ON xp_history(user_id);
CREATE INDEX idx_xp_history_source ON xp_history(source_type, source_id);
CREATE INDEX idx_xp_history_created ON xp_history(created_at);

-- Rankings
CREATE INDEX idx_rankings_type_period ON rankings(ranking_type, period);
CREATE INDEX idx_rankings_score ON rankings(score DESC);

-- Badges
CREATE INDEX idx_user_badges_user ON user_badges(user_id);
CREATE INDEX idx_user_badges_badge ON user_badges(badge_id);

-- Certificates
CREATE INDEX idx_certificates_user ON certificates(user_id);
CREATE INDEX idx_certificates_number ON certificates(certificate_number);

-- Payments
CREATE INDEX idx_payments_user ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_created ON payments(created_at);

-- Subscriptions
CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);

-- Notifications
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(read_at);
CREATE INDEX idx_notifications_type ON notifications(type);

-- Activity log
CREATE INDEX idx_activity_user ON user_activity_log(user_id);
CREATE INDEX idx_activity_action ON user_activity_log(action);
CREATE INDEX idx_activity_entity ON user_activity_log(entity_type, entity_id);
CREATE INDEX idx_activity_created ON user_activity_log(created_at);

-- =====================================================
-- FUNCTIONS & TRIGGERS
-- =====================================================

-- Auto-update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply to all tables
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_roles_updated_at BEFORE UPDATE ON roles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_courses_updated_at BEFORE UPDATE ON courses FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_modules_updated_at BEFORE UPDATE ON modules FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_lessons_updated_at BEFORE UPDATE ON lessons FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_labs_updated_at BEFORE UPDATE ON labs FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_machines_updated_at BEFORE UPDATE ON machines FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_lab_instances_updated_at BEFORE UPDATE ON lab_instances FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_challenges_updated_at BEFORE UPDATE ON challenges FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_badges_updated_at BEFORE UPDATE ON badges FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_certificates_updated_at BEFORE UPDATE ON certificates FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_plans_updated_at BEFORE UPDATE ON plans FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_subscriptions_updated_at BEFORE UPDATE ON subscriptions FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_payments_updated_at BEFORE UPDATE ON payments FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Update user XP summary function
CREATE OR REPLACE FUNCTION update_user_xp()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO user_xp_summary (user_id, total_xp, last_activity_at, updated_at)
    VALUES (NEW.user_id, NEW.amount, NOW(), NOW())
    ON CONFLICT (user_id) DO UPDATE SET
        total_xp = user_xp_summary.total_xp + NEW.amount,
        level = (SELECT level FROM levels WHERE min_xp <= user_xp_summary.total_xp + NEW.amount ORDER BY level DESC LIMIT 1),
        last_activity_at = NOW(),
        updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER trigger_update_user_xp
AFTER INSERT ON xp_history
FOR EACH ROW EXECUTE FUNCTION update_user_xp();

-- Update course stats function
CREATE OR REPLACE FUNCTION update_course_stats()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE courses SET
        stats = jsonb_set(
            stats,
            '{enrollments}',
            (SELECT COUNT(*)::text::jsonb FROM course_enrollments WHERE course_id = NEW.course_id)
        )
    WHERE id = NEW.course_id;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER trigger_update_course_enrollments
AFTER INSERT OR DELETE ON course_enrollments
FOR EACH ROW EXECUTE FUNCTION update_course_stats();

-- =====================================================
-- SEED DATA
-- =====================================================

-- Default roles
INSERT INTO roles (name, slug, description, is_system) VALUES
('Student', 'student', 'Regular student account', TRUE),
('Instructor', 'instructor', 'Course instructor', TRUE),
('Researcher', 'researcher', 'Security researcher', TRUE),
('Admin', 'admin', 'System administrator', TRUE),
('Super Admin', 'super-admin', 'Full system access', TRUE);

-- Default permissions
INSERT INTO permissions (name, slug, module, description) VALUES
-- Courses
('View Courses', 'courses.view', 'courses', 'View published courses'),
('Enroll in Courses', 'courses.enroll', 'courses', 'Enroll in courses'),
('Create Courses', 'courses.create', 'courses', 'Create new courses'),
('Edit Courses', 'courses.edit', 'courses', 'Edit course content'),
('Delete Courses', 'courses.delete', 'courses', 'Delete courses'),
-- Labs
('View Labs', 'labs.view', 'labs', 'View published labs'),
('Start Lab Instances', 'labs.start', 'labs', 'Start lab instances'),
('Create Labs', 'labs.create', 'labs', 'Create new labs'),
('Edit Labs', 'labs.edit', 'labs', 'Edit lab content'),
-- Users
('View Users', 'users.view', 'users', 'View user profiles'),
('Edit Users', 'users.edit', 'users', 'Edit user information'),
('Ban Users', 'users.ban', 'users', 'Ban user accounts'),
-- Admin
('Manage Roles', 'admin.roles', 'admin', 'Manage user roles'),
('View Analytics', 'admin.analytics', 'admin', 'View platform analytics'),
('Manage Plans', 'admin.plans', 'admin', 'Manage subscription plans');

-- Level configurations
INSERT INTO levels (level, title, min_xp, max_xp, badge_icon) VALUES
(1, 'Newbie', 0, 100, '🌱'),
(2, 'Beginner', 100, 300, '📚'),
(3, 'Learner', 300, 600, '🎓'),
(4, 'Practitioner', 600, 1000, '💻'),
(5, 'Specialist', 1000, 1500, '🔧'),
(6, 'Expert', 1500, 2500, '⭐'),
(7, 'Master', 2500, 4000, '🏆'),
(8, 'Guru', 4000, 6000, '🎯'),
(9, 'Legend', 6000, 10000, '👑'),
(10, 'Mythical', 10000, 999999, '🐉');

-- Default badges
INSERT INTO badges (name, slug, description, category, rarity, xp_reward) VALUES
('First Steps', 'first-steps', 'Complete your first lesson', 'milestone', 'common', 50),
('Lab Rat', 'lab-rat', 'Complete 10 labs', 'achievement', 'uncommon', 200),
('Bug Hunter', 'bug-hunter', 'Find 25 flags', 'skill', 'rare', 500),
('Speed Demon', 'speed-demon', 'Complete a lab in under 10 minutes', 'achievement', 'uncommon', 150),
('Streak Master', 'streak-master', 'Maintain a 30-day streak', 'milestone', 'epic', 1000),
('Course Conqueror', 'course-conqueror', 'Complete 5 courses', 'achievement', 'rare', 750),
('Code Breaker', 'code-breaker', 'Solve 50 crypto challenges', 'skill', 'epic', 800),
('Network Ninja', 'network-ninja', 'Master network security', 'skill', 'legendary', 1500);

-- Default plans
INSERT INTO plans (name, slug, description, price, billing_period, features, limits) VALUES
('Free', 'free', 'Basic access to free content', 0.00, 'monthly', '["Limited courses", "5 lab hours/month"]', '{"labs_per_day": 1, "courses": 5}'),
('Pro', 'pro', 'Full access to all content', 29.99, 'monthly', '["All courses", "Unlimited labs", "Certificates", "Priority support"]', '{"labs_per_day": -1, "courses": -1}'),
('Team', 'team', 'For teams and organizations', 99.99, 'monthly', '["Everything in Pro", "Team management", "Custom labs", "API access"]', '{"members": 10, "labs_per_day": -1, "courses": -1}');

-- =====================================================
-- VIEWS
-- =====================================================

-- User dashboard view
CREATE VIEW v_user_dashboard AS
SELECT 
    u.id,
    u.name,
    u.avatar,
    COALESCE(uxp.total_xp, 0) as total_xp,
    COALESCE(uxp.level, 1) as level,
    COALESCE(uxp.current_streak, 0) as current_streak,
    (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.id AND status = 'active') as active_courses,
    (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.id AND status = 'completed') as completed_courses,
    (SELECT COUNT(*) FROM lab_instances WHERE user_id = u.id) as total_labs,
    (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badges_count,
    (SELECT COUNT(*) FROM certificates WHERE user_id = u.id AND revoked = FALSE) as certificates_count
FROM users u
LEFT JOIN user_xp_summary uxp ON u.id = uxp.user_id;

-- Course leaderboard view
CREATE VIEW v_course_leaderboard AS
SELECT 
    ce.user_id,
    u.name,
    u.avatar,
    ce.course_id,
    c.title as course_title,
    ce.progress,
    ce.completed_at,
    ROW_NUMBER() OVER (PARTITION BY ce.course_id ORDER BY ce.completed_at, ce.progress DESC) as rank
FROM course_enrollments ce
JOIN users u ON ce.user_id = u.id
JOIN courses c ON ce.course_id = c.id
WHERE ce.status = 'completed';

-- Lab statistics view
CREATE VIEW v_lab_statistics AS
SELECT 
    l.id,
    l.title,
    l.difficulty,
    COUNT(DISTINCT li.id) as total_attempts,
    COUNT(DISTINCT CASE WHEN li.status = 'running' THEN li.id END) as active_instances,
    AVG(CASE WHEN li.status = 'expired' THEN EXTRACT(EPOCH FROM (li.stopped_at - li.started_at))/60 END) as avg_time_minutes,
    COUNT(DISTINCT s.user_id) FILTER (WHERE s.is_correct) as total_solves,
    ROUND(COUNT(DISTINCT s.user_id) FILTER (WHERE s.is_correct)::numeric / NULLIF(COUNT(DISTINCT li.user_id), 0) * 100, 2) as success_rate
FROM labs l
LEFT JOIN lab_instances li ON l.id = li.lab_id
LEFT JOIN challenges ch ON l.id = ch.lab_id
LEFT JOIN submissions s ON ch.id = s.challenge_id
GROUP BY l.id, l.title, l.difficulty;

-- =====================================================
-- ROW LEVEL SECURITY (RLS)
-- =====================================================

-- Enable RLS on sensitive tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE subscriptions ENABLE ROW LEVEL SECURITY;
ALTER TABLE notifications ENABLE ROW LEVEL SECURITY;

-- Users can only see their own data (unless admin)
CREATE POLICY users_isolation ON users
    FOR ALL
    USING (
        auth.uid() = id OR 
        EXISTS (SELECT 1 FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = auth.uid() AND r.slug IN ('admin', 'super-admin'))
    );

-- Payments isolation
CREATE POLICY payments_isolation ON payments
    FOR ALL
    USING (
        user_id = auth.uid() OR
        EXISTS (SELECT 1 FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = auth.uid() AND r.slug IN ('admin', 'super-admin'))
    );

-- =====================================================
-- END OF SCHEMA
-- =====================================================
