-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    is_banned BOOLEAN DEFAULT false,
    last_login_at TIMESTAMP,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create user_xp_summary table
CREATE TABLE IF NOT EXISTS user_xp_summary (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    total_xp INTEGER DEFAULT 0,
    level INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(user_id)
);

-- Create personal_access_tokens table
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id SERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    category_id INTEGER,
    instructor_id INTEGER,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    difficulty_level VARCHAR(50),
    lessons INTEGER DEFAULT 0,
    students INTEGER DEFAULT 0,
    rating DECIMAL(3,1) DEFAULT 0,
    is_published BOOLEAN DEFAULT false,
    is_featured BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create course_categories table
CREATE TABLE IF NOT EXISTS course_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create course_enrollments table
CREATE TABLE IF NOT EXISTS course_enrollments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'active',
    progress INTEGER DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT NOW(),
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(user_id, course_id)
);

-- Insert sample data
INSERT INTO course_categories (name, slug, description) VALUES
('Web Security', 'web-security', 'Web application security courses'),
('Penetration Testing', 'penetration-testing', 'Network and system penetration testing'),
('Malware Analysis', 'malware-analysis', 'Malware analysis and reverse engineering'),
('Programming', 'programming', 'Security programming and scripting'),
('Forensics', 'forensics', 'Digital forensics and investigation'),
('Cloud Security', 'cloud-security', 'Cloud security courses');

-- Insert sample courses
INSERT INTO courses (title, slug, description, difficulty_level, lessons, students, rating, is_published, is_featured) VALUES
('Web Application Security', 'web-application-security', 'Master OWASP Top 10 and web security testing.', 'intermediate', 24, 12500, 4.8, true, true),
('Network Penetration Testing', 'network-pentesting', 'Learn to assess and secure network infrastructure.', 'advanced', 32, 8900, 4.9, true, true),
('Malware Analysis', 'malware-analysis', 'Understand malware behavior and reverse engineering.', 'expert', 28, 5600, 4.7, true, false),
('Python for Security', 'python-security', 'Write security tools and automation scripts.', 'intermediate', 30, 15200, 4.8, true, true),
('Digital Forensics', 'digital-forensics', 'Investigate cyber incidents and analyze evidence.', 'advanced', 35, 7800, 4.6, true, false),
('Cloud Security', 'cloud-security', 'Secure AWS, Azure, and GCP environments.', 'advanced', 28, 9200, 4.7, true, true);
