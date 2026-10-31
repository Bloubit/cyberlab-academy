<?php

namespace App\Services;

use App\Models\Lab;
use App\Models\LabInstance;
use App\Models\Challenge;
use App\Models\Submission;
use App\Models\User;

class LabService
{
    /**
     * Create a new lab.
     */
    public function create(array $data, User $creator): Lab
    {
        return Lab::create([
            'course_id' => $data['course_id'] ?? null,
            'title' => $data['title'],
            'slug' => \Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? 'beginner',
            'type' => $data['type'] ?? 'single_machine',
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'xp_reward' => $data['xp_reward'] ?? 100,
            'learning_objectives' => $data['learning_objectives'] ?? null,
            'hints' => $data['hints'] ?? null,
            'network_topology' => $data['network_topology'] ?? null,
            'is_published' => $data['is_published'] ?? false,
        ]);
    }

    /**
     * Update lab.
     */
    public function update(Lab $lab, array $data): Lab
    {
        $lab->update($data);
        return $lab;
    }

    /**
     * Delete lab.
     */
    public function delete(Lab $lab): bool
    {
        return $lab->delete();
    }

    /**
     * Publish lab.
     */
    public function publish(Lab $lab): Lab
    {
        $lab->update(['is_published' => true]);
        return $lab;
    }

    /**
     * Generate connection info from lab machines.
     */
    protected function generateConnectionInfo(Lab $lab): array
    {
        $machines = $lab->machines()->get();
        
        if ($machines->isEmpty()) {
            return [
                'status' => 'running',
                'type' => 'sandbox',
                'message' => 'Lab environment is ready. Use the web interface below.',
            ];
        }

        $connectionDetails = $machines->map(function ($machine) {
            $ports = is_array($machine->ports) ? $machine->ports : [];
            $credentials = is_array($machine->credentials) ? $machine->credentials : [];
            
            return [
                'name' => $machine->name,
                'ip' => $machine->ip_address ?? 'dynamic',
                'os' => $machine->os,
                'ports' => $ports,
                'credentials' => $credentials,
                'protocol' => in_array('22', $ports) ? 'ssh' : (in_array('3389', $ports) ? 'rdp' : 'ssh'),
            ];
        });

        return [
            'status' => 'running',
            'type' => $lab->type ?? 'single_machine',
            'machines' => $connectionDetails,
            'access_url' => $this->generateAccessUrl($lab, $connectionDetails),
        ];
    }

    /**
     * Generate an access URL for the lab environment.
     */
    protected function generateAccessUrl(Lab $lab, $machines): ?string
    {
        $first = $machines->first();
        if (!$first || !$first['ip'] || $first['ip'] === 'dynamic') {
            return null;
        }

        $port = collect($first['ports'])->first();
        if ($first['protocol'] === 'ssh') {
            return $port ? "ssh://{$first['ip']}:{$port}" : "ssh://{$first['ip']}";
        }

        return "http://{$first['ip']}" . ($port ? ":{$port}" : '');
    }

    /**
     * Start lab instance for user.
     */
    public function startInstance(User $user, Lab $lab): LabInstance
    {
        // Check for existing running instance
        $existing = LabInstance::where('user_id', $user->id)
            ->where('lab_id', $lab->id)
            ->where('status', 'running')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Check for existing expired/pending instances and clean them up
        LabInstance::where('user_id', $user->id)
            ->where('lab_id', $lab->id)
            ->whereIn('status', ['pending', 'expired'])
            ->update(['status' => 'stopped', 'stopped_at' => now()]);

        // Load machines to generate connection info
        $lab->load('machines');
        $connectionInfo = $this->generateConnectionInfo($lab);

        // Create instance with 4-hour expiry
        $instance = LabInstance::create([
            'lab_id' => $lab->id,
            'user_id' => $user->id,
            'status' => 'running',
            'started_at' => now(),
            'expires_at' => now()->addHours(4),
            'connection_info' => $connectionInfo,
        ]);

        return $instance;
    }

    /**
     * Stop lab instance.
     */
    public function stopInstance(LabInstance $instance): LabInstance
    {
        $instance->stop();
        return $instance;
    }

    /**
     * Reset lab instance.
     */
    public function resetInstance(LabInstance $instance): LabInstance
    {
        $instance->update([
            'status' => 'running',
            'started_at' => now(),
            'expires_at' => now()->addHours(4),
        ]);

        return $instance;
    }

    /**
     * Submit flag.
     */
    public function submitFlag(User $user, Challenge $challenge, string $flagValue): Submission
    {
        // Check if already solved
        if ($challenge->isSolvedBy($user)) {
            throw new \Exception('Challenge already solved');
        }

        $submission = Submission::submit($user, $challenge, $flagValue);

        if ($submission->is_correct) {
            // Award XP
            \App\Models\XpHistory::award(
                $user,
                $challenge->points,
                'challenge',
                $challenge->id,
                "Solved challenge: {$challenge->title}"
            );

            // Check if all challenges in lab are solved
            $this->checkLabCompletion($user, $challenge->lab);
        }

        return $submission;
    }

    /**
     * Check if user completed all challenges in lab.
     */
    protected function checkLabCompletion(User $user, Lab $lab): void
    {
        $totalChallenges = $lab->challenges()->count();
        $solvedChallenges = Submission::where('user_id', $user->id)
            ->where('is_correct', true)
            ->whereIn('challenge_id', $lab->challenges()->pluck('id'))
            ->distinct('challenge_id')
            ->count();

        if ($totalChallenges === $solvedChallenges) {
            // Lab completed
            $instance = LabInstance::where('user_id', $user->id)
                ->where('lab_id', $lab->id)
                ->where('status', 'running')
                ->first();

            if ($instance) {
                $instance->update(['status' => 'completed']);
            }

            // Award bonus XP
            \App\Models\XpHistory::award(
                $user,
                50,
                'lab_completion',
                $lab->id,
                "Completed lab: {$lab->title}"
            );
        }
    }

    /**
     * Get user's lab instances.
     */
    public function getUserInstances(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return LabInstance::where('user_id', $user->id)
            ->with(['lab'])
            ->get();
    }

    /**
     * Search labs.
     */
    public function search(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Lab::published();

        if (isset($filters['difficulty_level'])) {
            $query->byDifficulty($filters['difficulty_level']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($filters['search']) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($filters['search']) . '%']);
            });
        }

        return $query;
    }
}
