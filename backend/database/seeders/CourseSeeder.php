<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Course Categories
        DB::table('course_categories')->insert([
            ['name' => 'Web Security', 'slug' => 'web-security', 'description' => 'Web application security courses', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Penetration Testing', 'slug' => 'penetration-testing', 'description' => 'Network and system penetration testing', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Malware Analysis', 'slug' => 'malware-analysis', 'description' => 'Malware analysis and reverse engineering', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Programming', 'slug' => 'programming', 'description' => 'Security programming and scripting', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Forensics', 'slug' => 'forensics', 'description' => 'Digital forensics and investigation', 'sort_order' => 5, 'is_active' => true],
            ['name' => 'Cloud Security', 'slug' => 'cloud-security', 'description' => 'Cloud security courses', 'sort_order' => 6, 'is_active' => true],
        ]);

        // Courses
        DB::table('courses')->insert([
            [
                'category_id' => 1,
                'instructor_id' => 2,
                'title' => 'Web Application Security',
                'slug' => 'web-application-security',
                'description' => 'Master OWASP Top 10 and web security testing. Learn to identify and exploit vulnerabilities in web applications.',
                'difficulty_level' => 'intermediate',
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'is_featured' => true,
                'requirements' => json_encode(['Basic knowledge of HTTP', 'Familiarity with web technologies']),
                'learning_outcomes' => json_encode(['Understand OWASP Top 10', 'Perform web application penetration testing', 'Identify and exploit XSS, SQLi, CSRF']),
                'tags' => json_encode(['web', 'owasp', 'xss', 'sqli']),
                'stats' => json_encode(['enrollments' => 12500, 'avg_rating' => 4.8]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'instructor_id' => 2,
                'title' => 'Network Penetration Testing',
                'slug' => 'network-penetration-testing',
                'description' => 'Learn to assess and secure network infrastructure. Master tools like Nmap, Metasploit, and Burp Suite.',
                'difficulty_level' => 'advanced',
                'price' => 49.99,
                'is_free' => false,
                'is_published' => true,
                'is_featured' => true,
                'requirements' => json_encode(['Networking fundamentals', 'Linux basics']),
                'learning_outcomes' => json_encode(['Perform network reconnaissance', 'Exploit network vulnerabilities', 'Conduct post-exploitation']),
                'tags' => json_encode(['network', 'pentest', 'metasploit', 'nmap']),
                'stats' => json_encode(['enrollments' => 8900, 'avg_rating' => 4.9]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 3,
                'instructor_id' => 2,
                'title' => 'Malware Analysis',
                'slug' => 'malware-analysis',
                'description' => 'Understand malware behavior and reverse engineering. Analyze real-world malware samples safely.',
                'difficulty_level' => 'expert',
                'price' => 79.99,
                'is_free' => false,
                'is_published' => true,
                'is_featured' => false,
                'requirements' => json_encode(['Assembly basics', 'C programming', 'Debugging experience']),
                'learning_outcomes' => json_encode(['Perform static and dynamic analysis', 'Reverse engineer binaries', 'Create detection signatures']),
                'tags' => json_encode(['malware', 'reverse-engineering', 'assembly', 'analysis']),
                'stats' => json_encode(['enrollments' => 5600, 'avg_rating' => 4.7]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 4,
                'instructor_id' => 2,
                'title' => 'Python for Security',
                'slug' => 'python-for-security',
                'description' => 'Write security tools and automation scripts. Learn to build scanners, exploit scripts, and analysis tools.',
                'difficulty_level' => 'intermediate',
                'price' => 0,
                'is_free' => true,
                'is_published' => true,
                'is_featured' => true,
                'requirements' => json_encode(['Basic Python knowledge']),
                'learning_outcomes' => json_encode(['Build security tools', 'Automate penetration testing tasks', 'Create network scanners']),
                'tags' => json_encode(['python', 'automation', 'scripting', 'tools']),
                'stats' => json_encode(['enrollments' => 15200, 'avg_rating' => 4.8]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 5,
                'instructor_id' => 2,
                'title' => 'Digital Forensics',
                'slug' => 'digital-forensics',
                'description' => 'Investigate cyber incidents and analyze evidence. Master forensic tools and techniques.',
                'difficulty_level' => 'advanced',
                'price' => 59.99,
                'is_free' => false,
                'is_published' => true,
                'is_featured' => false,
                'requirements' => json_encode(['Operating systems knowledge', 'File system basics']),
                'learning_outcomes' => json_encode(['Conduct forensic analysis', 'Recover deleted data', 'Prepare evidence for court']),
                'tags' => json_encode(['forensics', 'investigation', 'analysis', 'incident-response']),
                'stats' => json_encode(['enrollments' => 7800, 'avg_rating' => 4.6]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 6,
                'instructor_id' => 2,
                'title' => 'Cloud Security',
                'slug' => 'cloud-security',
                'description' => 'Secure AWS, Azure, and GCP environments. Learn cloud security best practices and architectures.',
                'difficulty_level' => 'advanced',
                'price' => 69.99,
                'is_free' => false,
                'is_published' => true,
                'is_featured' => true,
                'requirements' => json_encode(['Cloud computing basics', 'Networking knowledge']),
                'learning_outcomes' => json_encode(['Secure cloud infrastructure', 'Implement IAM policies', 'Monitor cloud environments']),
                'tags' => json_encode(['cloud', 'aws', 'azure', 'gcp', 'security']),
                'stats' => json_encode(['enrollments' => 9200, 'avg_rating' => 4.7]),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Enroll demo student in courses
        DB::table('course_enrollments')->insert([
            ['user_id' => 3, 'course_id' => 1, 'status' => 'active', 'progress' => 45, 'enrolled_at' => now()->subDays(14)],
            ['user_id' => 3, 'course_id' => 2, 'status' => 'active', 'progress' => 25, 'enrolled_at' => now()->subDays(7)],
            ['user_id' => 3, 'course_id' => 4, 'status' => 'active', 'progress' => 60, 'enrolled_at' => now()->subDays(21)],
        ]);

        // Badges
        DB::table('badges')->insert([
            ['name' => 'First Steps', 'slug' => 'first-steps', 'description' => 'Completed your first lesson', 'icon' => '🥇', 'category' => 'milestone', 'rarity' => 'common', 'xp_reward' => 50, 'requirements' => json_encode(['type' => 'lesson_complete', 'count' => 1]), 'is_active' => true],
            ['name' => 'Lab Rat', 'slug' => 'lab-rat', 'description' => 'Completed 10 labs', 'icon' => '🎯', 'category' => 'achievement', 'rarity' => 'uncommon', 'xp_reward' => 200, 'requirements' => json_encode(['type' => 'lab_complete', 'count' => 10]), 'is_active' => true],
            ['name' => 'Speed Demon', 'slug' => 'speed-demon', 'description' => 'Completed a lab in under 10 minutes', 'icon' => '⚡', 'category' => 'achievement', 'rarity' => 'uncommon', 'xp_reward' => 150, 'requirements' => json_encode(['type' => 'lab_speed', 'minutes' => 10]), 'is_active' => true],
            ['name' => 'Course Complete', 'slug' => 'course-complete', 'description' => 'Completed your first course', 'icon' => '🎓', 'category' => 'milestone', 'rarity' => 'common', 'xp_reward' => 100, 'requirements' => json_encode(['type' => 'course_complete', 'count' => 1]), 'is_active' => true],
            ['name' => 'Flag Hunter', 'slug' => 'flag-hunter', 'description' => 'Captured 50 flags', 'icon' => '🏴', 'category' => 'skill', 'rarity' => 'rare', 'xp_reward' => 500, 'requirements' => json_encode(['type' => 'flag_capture', 'count' => 50]), 'is_active' => true],
            ['name' => 'Quiz Master', 'slug' => 'quiz-master', 'description' => 'Scored 100% on a quiz', 'icon' => '🧠', 'category' => 'achievement', 'rarity' => 'rare', 'xp_reward' => 300, 'requirements' => json_encode(['type' => 'quiz_perfect', 'count' => 1]), 'is_active' => true],
        ]);

        // Assign demo student some badges
        DB::table('user_badges')->insert([
            ['user_id' => 3, 'badge_id' => 1, 'earned_at' => now()->subDays(20)],
            ['user_id' => 3, 'badge_id' => 2, 'earned_at' => now()->subDays(10)],
            ['user_id' => 3, 'badge_id' => 3, 'earned_at' => now()->subDays(5)],
        ]);
    }
}
