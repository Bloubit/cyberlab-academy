<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabInstanceSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if lab instances already exist (idempotent)
        if (DB::table('lab_instances')->exists()) {
            $this->command->info('Lab instances already seeded, skipping.');
            return;
        }

        // ============================================================
        // Lab Instances for Demo Student (user_id = 3)
        // ============================================================

        // Instance 1: SQL Injection Lab - COMPLETED (matches XP history)
        DB::table('lab_instances')->insert([
            'user_id' => 3,
            'lab_id' => 1,
            'status' => 'completed',
            'started_at' => now()->subDays(16),
            'expires_at' => now()->subDays(16)->addHours(4),
            'stopped_at' => now()->subDays(15),
            'connection_info' => json_encode([
                'status' => 'completed',
                'type' => 'single_machine',
                'machines' => [
                    ['name' => 'DVWA Web App', 'ip' => '10.0.1.10', 'os' => 'Linux', 'protocol' => 'ssh', 'ports' => ['80', '3306']],
                ],
            ]),
            'created_at' => now()->subDays(16),
            'updated_at' => now()->subDays(15),
        ]);

        // Instance 2: XSS Challenge - COMPLETED (matches XP history)
        DB::table('lab_instances')->insert([
            'user_id' => 3,
            'lab_id' => 2,
            'status' => 'completed',
            'started_at' => now()->subDays(12),
            'expires_at' => now()->subDays(12)->addHours(4),
            'stopped_at' => now()->subDays(11),
            'connection_info' => json_encode([
                'status' => 'completed',
                'type' => 'single_machine',
                'machines' => [
                    ['name' => 'XSS Practice', 'ip' => '10.0.1.11', 'os' => 'Linux', 'protocol' => 'ssh', 'ports' => ['80', '8080', '443']],
                ],
            ]),
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(11),
        ]);

        // Instance 3: Network Reconnaissance - COMPLETED (matches XP history)
        DB::table('lab_instances')->insert([
            'user_id' => 3,
            'lab_id' => 3,
            'status' => 'completed',
            'started_at' => now()->subDays(7),
            'expires_at' => now()->subDays(7)->addHours(4),
            'stopped_at' => now()->subDays(6),
            'connection_info' => json_encode([
                'status' => 'completed',
                'type' => 'multi_machine',
                'machines' => [
                    ['name' => 'Target Network 1 - Web Server', 'ip' => '10.0.2.10', 'os' => 'Ubuntu 22.04', 'protocol' => 'ssh', 'ports' => ['22', '80', '443', '3306', '8080']],
                    ['name' => 'Target Network 2 - Database Server', 'ip' => '10.0.2.11', 'os' => 'Ubuntu 22.04', 'protocol' => 'ssh', 'ports' => ['22', '5432', '6379', '27017']],
                    ['name' => 'Attacker Kali', 'ip' => '10.0.2.5', 'os' => 'Kali Linux', 'protocol' => 'ssh', 'ports' => ['22']],
                ],
            ]),
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(6),
        ]);

        // Instance 4: Port Scanner - RUNNING (currently active)
        DB::table('lab_instances')->insert([
            'user_id' => 3,
            'lab_id' => 4,
            'status' => 'running',
            'started_at' => now()->subHours(2),
            'expires_at' => now()->addHours(2),
            'connection_info' => json_encode([
                'status' => 'running',
                'type' => 'single_machine',
                'machines' => [
                    ['name' => 'Python Development Env', 'ip' => '10.0.3.10', 'os' => 'Debian 12', 'protocol' => 'ssh', 'ports' => ['22'], 'credentials' => ['developer' => 'python123']],
                ],
                'access_url' => 'ssh://developer@10.0.3.10',
            ]),
            'created_at' => now()->subHours(2),
            'updated_at' => now(),
        ]);

        // Instance 5: SQL Injection Lab - STOPPED (previous attempt)
        DB::table('lab_instances')->insert([
            'user_id' => 3,
            'lab_id' => 1,
            'status' => 'stopped',
            'started_at' => now()->subDays(20),
            'expires_at' => now()->subDays(20)->addHours(2),
            'stopped_at' => now()->subDays(20)->addHours(1),
            'connection_info' => json_encode([
                'status' => 'stopped',
                'type' => 'single_machine',
            ]),
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20)->addHours(1),
        ]);

        // ============================================================
        // Submissions (matching the XP history in GamificationSeeder)
        // ============================================================

        DB::table('submissions')->insert([
            // SQL Injection Lab - challenge 1 (Basic SQLi)
            [
                'user_id' => 3,
                'challenge_id' => 1,
                'flag_id' => 1,
                'submitted_flag' => 'FLAG{sql_injection_master_2024}',
                'is_correct' => true,
                'points_earned' => 50,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(16),
                'created_at' => now()->subDays(16),
                'updated_at' => now()->subDays(16),
            ],
            // SQL Injection Lab - challenge 2 (Union Based)
            [
                'user_id' => 3,
                'challenge_id' => 2,
                'flag_id' => 2,
                'submitted_flag' => 'FLAG{union_select_data_extract}',
                'is_correct' => true,
                'points_earned' => 75,
                'attempt_number' => 2,
                'submitted_at' => now()->subDays(16)->addHours(1),
                'created_at' => now()->subDays(16)->addHours(1),
                'updated_at' => now()->subDays(16)->addHours(1),
            ],
            // SQL Injection Lab - challenge 3 (Blind SQLi)
            [
                'user_id' => 3,
                'challenge_id' => 3,
                'flag_id' => 3,
                'submitted_flag' => 'FLAG{blind_sqli_detected}',
                'is_correct' => true,
                'points_earned' => 100,
                'attempt_number' => 3,
                'submitted_at' => now()->subDays(15),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            // XSS Challenge - challenge 4 (Stored XSS)
            [
                'user_id' => 3,
                'challenge_id' => 4,
                'flag_id' => 4,
                'submitted_flag' => 'FLAG{xss_stored_attack_2024}',
                'is_correct' => true,
                'points_earned' => 50,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(12),
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(12),
            ],
            // XSS Challenge - challenge 5 (Reflected XSS)
            [
                'user_id' => 3,
                'challenge_id' => 5,
                'flag_id' => 5,
                'submitted_flag' => 'FLAG{reflected_xss_cookie_steal}',
                'is_correct' => true,
                'points_earned' => 100,
                'attempt_number' => 2,
                'submitted_at' => now()->subDays(12)->addHours(1),
                'created_at' => now()->subDays(12)->addHours(1),
                'updated_at' => now()->subDays(12)->addHours(1),
            ],
            // XSS Challenge - challenge 6 (DOM XSS)
            [
                'user_id' => 3,
                'challenge_id' => 6,
                'flag_id' => 6,
                'submitted_flag' => 'FLAG{dom_xss_exploited}',
                'is_correct' => true,
                'points_earned' => 75,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(11),
                'created_at' => now()->subDays(11),
                'updated_at' => now()->subDays(11),
            ],
            // Network Recon - challenge 7 (Port Scan)
            [
                'user_id' => 3,
                'challenge_id' => 7,
                'flag_id' => 7,
                'submitted_flag' => 'FLAG{port_scanner_detected}',
                'is_correct' => true,
                'points_earned' => 50,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(7),
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            // Network Recon - challenge 8 (Service Enum)
            [
                'user_id' => 3,
                'challenge_id' => 8,
                'flag_id' => 8,
                'submitted_flag' => 'FLAG{service_enum_complete}',
                'is_correct' => true,
                'points_earned' => 75,
                'attempt_number' => 2,
                'submitted_at' => now()->subDays(7)->addHours(1),
                'created_at' => now()->subDays(7)->addHours(1),
                'updated_at' => now()->subDays(7)->addHours(1),
            ],
            // Network Recon - challenge 9 (OS Fingerprinting)
            [
                'user_id' => 3,
                'challenge_id' => 9,
                'flag_id' => 9,
                'submitted_flag' => 'FLAG{os_detected_linux}',
                'is_correct' => true,
                'points_earned' => 100,
                'attempt_number' => 3,
                'submitted_at' => now()->subDays(7)->addHours(2),
                'created_at' => now()->subDays(7)->addHours(2),
                'updated_at' => now()->subDays(7)->addHours(2),
            ],
            // Network Recon - challenge 10 (Vuln Scan)
            [
                'user_id' => 3,
                'challenge_id' => 10,
                'flag_id' => 10,
                'submitted_flag' => 'FLAG{vuln_scan_complete_2024}',
                'is_correct' => true,
                'points_earned' => 150,
                'attempt_number' => 4,
                'submitted_at' => now()->subDays(6),
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            // Python Scanner - challenge 11 (Basic TCP)
            [
                'user_id' => 3,
                'challenge_id' => 11,
                'flag_id' => 11,
                'submitted_flag' => 'FLAG{python_scanner_basic}',
                'is_correct' => true,
                'points_earned' => 50,
                'attempt_number' => 1,
                'submitted_at' => now()->subHours(2),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            // Some wrong attempts for realism
            [
                'user_id' => 3,
                'challenge_id' => 2,
                'flag_id' => null,
                'submitted_flag' => 'FLAG{wrong_flag}',
                'is_correct' => false,
                'points_earned' => 0,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(16)->addMinutes(30),
                'created_at' => now()->subDays(16)->addMinutes(30),
                'updated_at' => now()->subDays(16)->addMinutes(30),
            ],
            [
                'user_id' => 3,
                'challenge_id' => 5,
                'flag_id' => null,
                'submitted_flag' => 'FLAG{test_xss}',
                'is_correct' => false,
                'points_earned' => 0,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(12)->addMinutes(30),
                'created_at' => now()->subDays(12)->addMinutes(30),
                'updated_at' => now()->subDays(12)->addMinutes(30),
            ],
            [
                'user_id' => 3,
                'challenge_id' => 8,
                'flag_id' => null,
                'submitted_flag' => 'FLAG{wrong_service}',
                'is_correct' => false,
                'points_earned' => 0,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(7)->addMinutes(15),
                'created_at' => now()->subDays(7)->addMinutes(15),
                'updated_at' => now()->subDays(7)->addMinutes(15),
            ],
            [
                'user_id' => 3,
                'challenge_id' => 9,
                'flag_id' => null,
                'submitted_flag' => 'FLAG{windows_detected}',
                'is_correct' => false,
                'points_earned' => 0,
                'attempt_number' => 1,
                'submitted_at' => now()->subDays(7)->addHours(1)->addMinutes(30),
                'created_at' => now()->subDays(7)->addHours(1)->addMinutes(30),
                'updated_at' => now()->subDays(7)->addHours(1)->addMinutes(30),
            ],
            [
                'user_id' => 3,
                'challenge_id' => 10,
                'flag_id' => null,
                'submitted_flag' => 'FLAG{no_vulns_found}',
                'is_correct' => false,
                'points_earned' => 0,
                'attempt_number' => 2,
                'submitted_at' => now()->subDays(7)->addHours(1)->addMinutes(45),
                'created_at' => now()->subDays(7)->addHours(1)->addMinutes(45),
                'updated_at' => now()->subDays(7)->addHours(1)->addMinutes(45),
            ],
        ]);

        // ============================================================
        // Update XP History to match submissions
        // Note: GamificationSeeder already adds some XP history entries.
        // We add additional entries for the submissions to keep them in sync.
        // ============================================================

        // Add XP entries for the lab challenges solved via submissions
        $existingXp = DB::table('xp_history')
            ->where('user_id', 3)
            ->where('source_type', 'challenge')
            ->count();

        if ($existingXp === 0) {
            DB::table('xp_history')->insert([
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'challenge', 'source_id' => 1, 'description' => 'Solved challenge: Basic SQL Injection', 'multiplier' => 1.00, 'created_at' => now()->subDays(16)],
                ['user_id' => 3, 'amount' => 75, 'source_type' => 'challenge', 'source_id' => 2, 'description' => 'Solved challenge: Union Based Injection', 'multiplier' => 1.00, 'created_at' => now()->subDays(16)->addHours(1)],
                ['user_id' => 3, 'amount' => 100, 'source_type' => 'challenge', 'source_id' => 3, 'description' => 'Solved challenge: Blind SQL Injection', 'multiplier' => 1.00, 'created_at' => now()->subDays(15)],
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'challenge', 'source_id' => 4, 'description' => 'Solved challenge: Stored XSS', 'multiplier' => 1.00, 'created_at' => now()->subDays(12)],
                ['user_id' => 3, 'amount' => 100, 'source_type' => 'challenge', 'source_id' => 5, 'description' => 'Solved challenge: Reflected XSS', 'multiplier' => 1.00, 'created_at' => now()->subDays(12)->addHours(1)],
                ['user_id' => 3, 'amount' => 75, 'source_type' => 'challenge', 'source_id' => 6, 'description' => 'Solved challenge: DOM-based XSS', 'multiplier' => 1.00, 'created_at' => now()->subDays(11)],
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'challenge', 'source_id' => 7, 'description' => 'Solved challenge: Port Scan', 'multiplier' => 1.00, 'created_at' => now()->subDays(7)],
                ['user_id' => 3, 'amount' => 75, 'source_type' => 'challenge', 'source_id' => 8, 'description' => 'Solved challenge: Service Enumeration', 'multiplier' => 1.00, 'created_at' => now()->subDays(7)->addHours(1)],
                ['user_id' => 3, 'amount' => 100, 'source_type' => 'challenge', 'source_id' => 9, 'description' => 'Solved challenge: OS Fingerprinting', 'multiplier' => 1.00, 'created_at' => now()->subDays(7)->addHours(2)],
                ['user_id' => 3, 'amount' => 150, 'source_type' => 'challenge', 'source_id' => 10, 'description' => 'Solved challenge: Vulnerability Scan', 'multiplier' => 1.00, 'created_at' => now()->subDays(6)],
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'challenge', 'source_id' => 11, 'description' => 'Solved challenge: Basic TCP Scanner', 'multiplier' => 1.00, 'created_at' => now()->subHours(2)],
                // Lab completion bonuses
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'lab_completion', 'source_id' => 1, 'description' => 'Completed lab: SQL Injection Lab', 'multiplier' => 1.00, 'created_at' => now()->subDays(15)],
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'lab_completion', 'source_id' => 2, 'description' => 'Completed lab: XSS Challenge', 'multiplier' => 1.00, 'created_at' => now()->subDays(11)],
                ['user_id' => 3, 'amount' => 50, 'source_type' => 'lab_completion', 'source_id' => 3, 'description' => 'Completed lab: Network Reconnaissance', 'multiplier' => 1.00, 'created_at' => now()->subDays(6)],
            ]);
        }
        // ============================================================
        // Update User XP Summary to reflect new submissions
        // ============================================================
        // Calculate total XP from all correct submissions
        $totalSubmissionXp = DB::table('submissions')
            ->where('user_id', 3)
            ->where('is_correct', true)
            ->sum('points_earned');

        // Total from GamificationSeeder XP history (lab_complete + lesson + quiz)
        $historyXp = DB::table('xp_history')
            ->where('user_id', 3)
            ->sum('amount');

        // Update the user_xp_summary with combined total
        $totalXp = $totalSubmissionXp + $historyXp;
        DB::table('user_xp_summary')
            ->where('user_id', 3)
            ->update([
                'total_xp' => $totalXp,
                'level' => max(5, (int) floor($totalXp / 1500) + 1),
                'updated_at' => now(),
            ]);

        $this->command->info('Lab instances seeded: 5 instances, 16 submissions, XP summary updated.');
    }
}
