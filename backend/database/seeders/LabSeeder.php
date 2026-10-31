<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if labs already exist (idempotent)
        if (DB::table('labs')->exists()) {
            $this->command->info('Labs already seeded, skipping.');
            return;
        }

        // Labs
        DB::table('labs')->insert([
            [
                'course_id' => 1,
                'title' => 'SQL Injection Lab',
                'slug' => 'sql-injection-lab',
                'description' => 'Practice SQL injection attacks against a vulnerable web application. Learn to bypass authentication and extract data from databases.',
                'difficulty_level' => 'intermediate',
                'type' => 'single_machine',
                'duration_minutes' => 60,
                'xp_reward' => 200,
                'learning_objectives' => "Identify SQL injection vulnerabilities\nExploit SQLi to bypass authentication\nExtract data using UNION-based injection\nUse SQLMap for automated exploitation",
                'hints' => "Try single quotes to break the query\nUse UNION SELECT to combine results\nCheck information_schema tables",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 1,
                'title' => 'XSS Challenge',
                'slug' => 'xss-challenge',
                'description' => 'Master Cross-Site Scripting attacks through realistic scenarios. Learn stored, reflected, and DOM-based XSS techniques.',
                'difficulty_level' => 'intermediate',
                'type' => 'single_machine',
                'duration_minutes' => 45,
                'xp_reward' => 150,
                'learning_objectives' => "Understand stored, reflected, and DOM-based XSS\nCraft XSS payloads\nBypass WAF and input filters\nSteal cookies and session data",
                'hints' => "Try <script>alert(1)</script>\nCheck if < and > are filtered\nUse onerror or onload events",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 2,
                'title' => 'Network Reconnaissance',
                'slug' => 'network-recon',
                'description' => 'Perform network scanning and enumeration using various tools. Master the art of network discovery.',
                'difficulty_level' => 'beginner',
                'type' => 'multi_machine',
                'duration_minutes' => 90,
                'xp_reward' => 250,
                'learning_objectives' => "Master Nmap scanning techniques\nPerform service enumeration\nIdentify OS and version fingerprints\nDocument network topology",
                'hints' => "Start with a ping sweep\nUse -sV for version detection\nTry -A for aggressive scanning",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 4,
                'title' => 'Port Scanner in Python',
                'slug' => 'python-port-scanner',
                'description' => 'Build your own port scanner using Python sockets. Learn TCP connection handling and multi-threading.',
                'difficulty_level' => 'beginner',
                'type' => 'single_machine',
                'duration_minutes' => 45,
                'xp_reward' => 100,
                'learning_objectives' => "Build a functional port scanner\nUnderstand TCP three-way handshake\nImplement multi-threaded scanning\nParse and display scan results",
                'hints' => "Use socket.connect_ex() for non-blocking checks\nSet a timeout to avoid hanging\nUse threading.Thread for parallel scans",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 1,
                'title' => 'Cryptography Challenge',
                'slug' => 'cryptography-challenge',
                'description' => 'Test your cryptography skills. Decrypt various ciphertexts and understand modern encryption algorithms.',
                'difficulty_level' => 'expert',
                'type' => 'ctf',
                'duration_minutes' => 120,
                'xp_reward' => 500,
                'learning_objectives' => "Identify encryption algorithms\nBreak classical ciphers\nUnderstand symmetric vs asymmetric crypto\nPerform frequency analysis",
                'hints' => "Check letter frequency for classical ciphers\nLook for padding patterns in block ciphers\nRSA challenges often involve factorisation",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 2,
                'title' => 'Phishing Simulation',
                'slug' => 'phishing-simulation',
                'description' => 'Simulate realistic phishing attacks. Learn to craft convincing phishing emails and understand social engineering techniques.',
                'difficulty_level' => 'intermediate',
                'type' => 'sandbox',
                'duration_minutes' => 75,
                'xp_reward' => 300,
                'learning_objectives' => "Create phishing email templates\nSet up a phishing landing page\nBypass email security filters\nCollect and analyze victim data",
                'hints' => "Use legitimate-looking domains\nClone real login pages for realism\nAdd urgency in email subject lines",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 3,
                'title' => 'Buffer Overflow Lab',
                'slug' => 'buffer-overflow-lab',
                'description' => 'Exploit stack buffer overflows on a vulnerable binary. Learn about memory corruption and shellcode injection.',
                'difficulty_level' => 'advanced',
                'type' => 'single_machine',
                'duration_minutes' => 90,
                'xp_reward' => 400,
                'learning_objectives' => "Understand stack memory layout\nIdentify buffer overflow vulnerabilities\nCraft and inject shellcode\nBypass DEP and ASLR mitigations",
                'hints' => "Use pattern_create to find EIP offset\nCheck for bad characters before shellcode\nUse msfvenom to generate payload",
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Machines
        DB::table('machines')->insert([
            ['lab_id' => 1, 'name' => 'DVWA Web App', 'image' => 'vulnerables/web-dvwa:latest', 'os' => 'Linux', 'ip_address' => '10.0.1.10', 'ports' => json_encode(['80', '3306']), 'credentials' => json_encode(['admin' => 'password', 'root' => 'toor']), 'cpu_cores' => 1, 'ram_mb' => 1024, 'disk_gb' => 10, 'services' => json_encode(['Apache 2.4', 'MySQL 8.0', 'PHP 8.1'])],
            ['lab_id' => 2, 'name' => 'XSS Practice', 'image' => 'webgoat/goatandwolf:latest', 'os' => 'Linux', 'ip_address' => '10.0.1.11', 'ports' => json_encode(['80', '8080', '443']), 'credentials' => json_encode(['admin' => 'admin123', 'user' => 'user123']), 'cpu_cores' => 1, 'ram_mb' => 1024, 'disk_gb' => 10, 'services' => json_encode(['Apache 2.4', 'Node.js 18', 'MySQL 5.7'])],
            ['lab_id' => 3, 'name' => 'Target Network 1 - Web Server', 'image' => 'cyberlab/metasploitable:latest', 'os' => 'Ubuntu 22.04', 'ip_address' => '10.0.2.10', 'ports' => json_encode(['22', '80', '443', '3306', '8080']), 'credentials' => json_encode(['root' => 'toor', 'msfadmin' => 'msfadmin']), 'cpu_cores' => 2, 'ram_mb' => 2048, 'disk_gb' => 20, 'services' => json_encode(['Apache 2.4', 'MySQL 8.0', 'OpenSSH 8.9', 'Tomcat 9'])],
            ['lab_id' => 3, 'name' => 'Target Network 2 - Database Server', 'image' => 'cyberlab/metasploitable:latest', 'os' => 'Ubuntu 22.04', 'ip_address' => '10.0.2.11', 'ports' => json_encode(['22', '5432', '6379', '27017']), 'credentials' => json_encode(['root' => 'toor', 'postgres' => 'postgres']), 'cpu_cores' => 2, 'ram_mb' => 2048, 'disk_gb' => 20, 'services' => json_encode(['PostgreSQL 15', 'Redis 7', 'MongoDB 6', 'OpenSSH 8.9'])],
            ['lab_id' => 3, 'name' => 'Attacker Kali', 'image' => 'kalilinux/kali-rolling:latest', 'os' => 'Kali Linux', 'ip_address' => '10.0.2.5', 'ports' => json_encode(['22']), 'credentials' => json_encode(['root' => 'kali', 'user' => 'user']), 'cpu_cores' => 2, 'ram_mb' => 4096, 'disk_gb' => 40, 'services' => json_encode(['OpenSSH 8.9', 'Nmap', 'Metasploit'])],
            ['lab_id' => 4, 'name' => 'Python Development Env', 'image' => 'python:3.11-slim', 'os' => 'Debian 12', 'ip_address' => '10.0.3.10', 'ports' => json_encode(['22']), 'credentials' => json_encode(['root' => 'root', 'developer' => 'python123']), 'cpu_cores' => 1, 'ram_mb' => 512, 'disk_gb' => 5, 'services' => json_encode(['OpenSSH 8.9', 'Python 3.11'])],
            ['lab_id' => 5, 'name' => 'Crypto Server', 'image' => 'cyberlab/crypto-lab:latest', 'os' => 'Alpine Linux', 'ip_address' => '10.0.4.10', 'ports' => json_encode(['22', '443', '8443']), 'credentials' => json_encode(['crypto' => 'crypto123']), 'cpu_cores' => 1, 'ram_mb' => 1024, 'disk_gb' => 10, 'services' => json_encode(['OpenSSL 3.0', 'Python 3.11', 'GnuPG 2.4'])],
            ['lab_id' => 6, 'name' => 'Phishing Server', 'image' => 'cyberlab/phishing-sim:latest', 'os' => 'Ubuntu 22.04', 'ip_address' => '10.0.5.10', 'ports' => json_encode(['22', '80', '443', '25', '587']), 'credentials' => json_encode(['root' => 'phishing123', 'admin' => 'admin123']), 'cpu_cores' => 2, 'ram_mb' => 2048, 'disk_gb' => 20, 'services' => json_encode(['Nginx 1.24', 'Postfix 3.6', 'MySQL 8.0', 'PHP 8.1'])],
            ['lab_id' => 7, 'name' => 'BOF Target', 'image' => 'cyberlab/bof-practice:latest', 'os' => 'Ubuntu 20.04', 'ip_address' => '10.0.6.10', 'ports' => json_encode(['22', '1337', '4444']), 'credentials' => json_encode(['user' => 'user123', 'root' => 'toor']), 'cpu_cores' => 1, 'ram_mb' => 1024, 'disk_gb' => 10, 'services' => json_encode(['OpenSSH 8.4', 'Custom BOF Service (port 1337)', 'Netcat'])],
        ]);

        // Challenges
        DB::table('challenges')->insert([
            // Lab 1: SQL Injection
            ['lab_id' => 1, 'title' => 'Basic SQL Injection', 'description' => 'Bypass login authentication using SQL injection', 'hint' => 'Try injecting into the username field', 'points' => 50, 'sort_order' => 1],
            ['lab_id' => 1, 'title' => 'Union Based Injection', 'description' => 'Extract data from database using UNION SQL injection', 'hint' => 'Determine the number of columns first', 'points' => 75, 'sort_order' => 2],
            ['lab_id' => 1, 'title' => 'Blind SQL Injection', 'description' => 'Extract data without direct output using boolean-based blind injection', 'hint' => 'Use SUBSTRING and comparison operators', 'points' => 100, 'sort_order' => 3],
            // Lab 2: XSS
            ['lab_id' => 2, 'title' => 'Stored XSS', 'description' => 'Inject persistent JavaScript into the application', 'hint' => 'Try the comment form with <script> tags', 'points' => 50, 'sort_order' => 1],
            ['lab_id' => 2, 'title' => 'Reflected XSS', 'description' => 'Exploit reflected XSS to steal cookies', 'hint' => 'Check URL parameters for injection points', 'points' => 100, 'sort_order' => 2],
            ['lab_id' => 2, 'title' => 'DOM-based XSS', 'description' => 'Exploit client-side XSS without server reflection', 'hint' => 'Look at JavaScript that processes location.hash', 'points' => 75, 'sort_order' => 3],
            // Lab 3: Network Recon
            ['lab_id' => 3, 'title' => 'Port Scan', 'description' => 'Find all open ports on the target machines', 'hint' => 'Use nmap with different scan types', 'points' => 50, 'sort_order' => 1],
            ['lab_id' => 3, 'title' => 'Service Enumeration', 'description' => 'Identify services and versions running on discovered ports', 'hint' => 'Use nmap -sV for version detection', 'points' => 75, 'sort_order' => 2],
            ['lab_id' => 3, 'title' => 'OS Fingerprinting', 'description' => 'Determine operating systems of target machines', 'hint' => 'Use nmap -O for OS detection', 'points' => 100, 'sort_order' => 3],
            ['lab_id' => 3, 'title' => 'Vulnerability Scan', 'description' => 'Identify known vulnerabilities on target services', 'hint' => 'Use nmap scripts (--script vuln)', 'points' => 150, 'sort_order' => 4],
            // Lab 4: Python Scanner
            ['lab_id' => 4, 'title' => 'Basic TCP Scanner', 'description' => 'Create a simple TCP port scanner in Python', 'hint' => 'Use socket.connect_ex() for non-blocking checks', 'points' => 50, 'sort_order' => 1],
            ['lab_id' => 4, 'title' => 'Multi-threaded Scanner', 'description' => 'Optimize your scanner with threading', 'hint' => 'Use threading.Thread for parallel scans', 'points' => 100, 'sort_order' => 2],
            ['lab_id' => 4, 'title' => 'Banner Grabbing', 'description' => 'Add banner grabbing capability to your scanner', 'hint' => 'Use socket.recv() to read service banners', 'points' => 75, 'sort_order' => 3],
            // Lab 5: Crypto Challenge
            ['lab_id' => 5, 'title' => 'Caesar Cipher', 'description' => 'Decrypt a Caesar cipher with unknown shift', 'hint' => 'Perform frequency analysis on the ciphertext', 'points' => 50, 'sort_order' => 1],
            ['lab_id' => 5, 'title' => 'Vigenère Cipher', 'description' => 'Break a Vigenère cipher using Kasiski examination', 'hint' => 'Find the key length using repeated patterns', 'points' => 100, 'sort_order' => 2],
            ['lab_id' => 5, 'title' => 'RSA Decryption', 'description' => 'Decrypt RSA given n, e, and ciphertext', 'hint' => 'Try to factor n using online tools', 'points' => 200, 'sort_order' => 3],
            ['lab_id' => 5, 'title' => 'Hash Collision', 'description' => 'Find two different inputs with the same MD5 hash', 'hint' => 'Research MD5 collision generation tools', 'points' => 150, 'sort_order' => 4],
            // Lab 6: Phishing Simulation
            ['lab_id' => 6, 'title' => 'Craft Phishing Email', 'description' => 'Create a convincing phishing email targeting employees', 'hint' => 'Use urgency and authority in the email body', 'points' => 75, 'sort_order' => 1],
            ['lab_id' => 6, 'title' => 'Clone Login Page', 'description' => 'Create a fake login page that captures credentials', 'hint' => 'Use legitimate-looking domain names', 'points' => 100, 'sort_order' => 2],
            ['lab_id' => 6, 'title' => 'Bypass SPF/DKIM', 'description' => 'Configure email to bypass SPF and DKIM checks', 'hint' => 'Research email authentication bypass techniques', 'points' => 125, 'sort_order' => 3],
            // Lab 7: Buffer Overflow
            ['lab_id' => 7, 'title' => 'Find EIP Offset', 'description' => 'Identify the EIP offset in the vulnerable binary', 'hint' => 'Use pattern_create and pattern_offset', 'points' => 75, 'sort_order' => 1],
            ['lab_id' => 7, 'title' => 'Control EIP', 'description' => 'Overwrite EIP to control program execution', 'hint' => 'Find a JMP ESP instruction in the binary', 'points' => 125, 'sort_order' => 2],
            ['lab_id' => 7, 'title' => 'Shellcode Injection', 'description' => 'Inject and execute shellcode to get a shell', 'hint' => 'Use msfvenom to generate staged payload', 'points' => 200, 'sort_order' => 3],
        ]);

        // Flags
        DB::table('flags')->insert([
            // Lab 1: SQL Injection (challenges 1-3)
            ['challenge_id' => 1, 'flag_hash' => Hash::make('FLAG{sql_injection_master_2024}'), 'flag_type' => 'static'],
            ['challenge_id' => 2, 'flag_hash' => Hash::make('FLAG{union_select_data_extract}'), 'flag_type' => 'static'],
            ['challenge_id' => 3, 'flag_hash' => Hash::make('FLAG{blind_sqli_detected}'), 'flag_type' => 'static'],
            // Lab 2: XSS (challenges 4-6)
            ['challenge_id' => 4, 'flag_hash' => Hash::make('FLAG{xss_stored_attack_2024}'), 'flag_type' => 'static'],
            ['challenge_id' => 5, 'flag_hash' => Hash::make('FLAG{reflected_xss_cookie_steal}'), 'flag_type' => 'static'],
            ['challenge_id' => 6, 'flag_hash' => Hash::make('FLAG{dom_xss_exploited}'), 'flag_type' => 'static'],
            // Lab 3: Network Recon (challenges 7-10)
            ['challenge_id' => 7, 'flag_hash' => Hash::make('FLAG{port_scanner_detected}'), 'flag_type' => 'static'],
            ['challenge_id' => 8, 'flag_hash' => Hash::make('FLAG{service_enum_complete}'), 'flag_type' => 'static'],
            ['challenge_id' => 9, 'flag_hash' => Hash::make('FLAG{os_detected_linux}'), 'flag_type' => 'static'],
            ['challenge_id' => 10, 'flag_hash' => Hash::make('FLAG{vuln_scan_complete_2024}'), 'flag_type' => 'static'],
            // Lab 4: Python Scanner (challenges 11-13)
            ['challenge_id' => 11, 'flag_hash' => Hash::make('FLAG{python_scanner_basic}'), 'flag_type' => 'static'],
            ['challenge_id' => 12, 'flag_hash' => Hash::make('FLAG{multi_thread_scanner_2024}'), 'flag_type' => 'static'],
            ['challenge_id' => 13, 'flag_hash' => Hash::make('FLAG{banner_grabber_ready}'), 'flag_type' => 'static'],
            // Lab 5: Crypto Challenge (challenges 14-17)
            ['challenge_id' => 14, 'flag_hash' => Hash::make('FLAG{caesar_decrypted_2024}'), 'flag_type' => 'static'],
            ['challenge_id' => 15, 'flag_hash' => Hash::make('FLAG{vigenere_broken}'), 'flag_type' => 'static'],
            ['challenge_id' => 16, 'flag_hash' => Hash::make('FLAG{rsa_key_cracked}'), 'flag_type' => 'static'],
            ['challenge_id' => 17, 'flag_hash' => Hash::make('FLAG{md5_collision_found}'), 'flag_type' => 'static'],
            // Lab 6: Phishing Simulation (challenges 18-20)
            ['challenge_id' => 18, 'flag_hash' => Hash::make('FLAG{phishing_email_crafted}'), 'flag_type' => 'static'],
            ['challenge_id' => 19, 'flag_hash' => Hash::make('FLAG{login_page_cloned}'), 'flag_type' => 'static'],
            ['challenge_id' => 20, 'flag_hash' => Hash::make('FLAG{spf_bypass_success}'), 'flag_type' => 'static'],
            // Lab 7: Buffer Overflow (challenges 21-23)
            ['challenge_id' => 21, 'flag_hash' => Hash::make('FLAG{eip_offset_found}'), 'flag_type' => 'static'],
            ['challenge_id' => 22, 'flag_hash' => Hash::make('FLAG{eip_controlled}'), 'flag_type' => 'static'],
            ['challenge_id' => 23, 'flag_hash' => Hash::make('FLAG{shellcode_executed}'), 'flag_type' => 'static'],
        ]);

        $this->command->info('Labs seeded: 7 labs, 9 machines, 23 challenges, 23 flags.');
    }
}
