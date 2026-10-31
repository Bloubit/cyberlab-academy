<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuizAttempt;
use App\Models\ExamResult;
use App\Models\User;

class QuizService
{
    /**
     * Create a new quiz.
     */
    public function create(array $data): Quiz
    {
        return Quiz::create([
            'lesson_id' => $data['lesson_id'] ?? null,
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'quiz_type' => $data['quiz_type'] ?? 'practice',
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'passing_score' => $data['passing_score'] ?? 60,
            'max_attempts' => $data['max_attempts'] ?? 3,
            'shuffle_questions' => $data['shuffle_questions'] ?? true,
            'shuffle_answers' => $data['shuffle_answers'] ?? true,
        ]);
    }

    /**
     * Add question to quiz.
     */
    public function addQuestion(Quiz $quiz, array $data): Question
    {
        $maxOrder = $quiz->questions()->max('sort_order') ?? 0;

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'question_html' => $data['question_html'] ?? null,
            'code_snippet' => $data['code_snippet'] ?? null,
            'code_language' => $data['code_language'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 1,
            'sort_order' => $maxOrder + 1,
            'difficulty' => $data['difficulty'] ?? 'medium',
        ]);

        // Add answers
        if (isset($data['answers'])) {
            foreach ($data['answers'] as $index => $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return $question;
    }

    /**
     * Start quiz attempt.
     */
    public function startAttempt(User $user, Quiz $quiz): QuizAttempt
    {
        if (!$quiz->canAttempt($user)) {
            throw new \Exception('Maximum attempts reached');
        }

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $attempt;
    }

    /**
     * Submit quiz attempt.
     */
    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        $quiz = $attempt->quiz;
        $totalPoints = 0;
        $earnedPoints = 0;
        $results = [];

        foreach ($answers as $answer) {
            $question = Question::find($answer['question_id']);
            if (!$question) {
                continue;
            }

            $totalPoints += $question->points;
            $isCorrect = $question->isCorrect($answer['answer_ids'] ?? []);

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            $results[] = [
                'question_id' => $question->id,
                'answer_ids' => $answer['answer_ids'] ?? [],
                'is_correct' => $isCorrect,
                'points_earned' => $isCorrect ? $question->points : 0,
            ];
        }

        $score = $totalPoints > 0
            ? round(($earnedPoints / $totalPoints) * 100, 2)
            : 0;

        $attempt->complete($results, $score);

        // Create exam result
        ExamResult::create([
            'user_id' => $attempt->user_id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $quiz->total_questions,
            'correct_answers' => collect($results)->where('is_correct', true)->count(),
            'time_taken_seconds' => $attempt->time_taken_seconds,
            'is_passed' => $score >= $quiz->passing_score,
            'started_at' => $attempt->started_at,
            'completed_at' => now(),
            'attempt_number' => $quiz->getAttemptsCount($attempt->user),
        ]);

        // Award XP if passed
        if ($score >= $quiz->passing_score) {
            \App\Models\XpHistory::award(
                $attempt->user,
                25,
                'quiz',
                $quiz->id,
                "Passed quiz: {$quiz->title}"
            );
        }

        return $attempt->fresh();
    }

    /**
     * Get quiz with questions.
     */
    public function getQuizWithQuestions(Quiz $quiz): Quiz
    {
        return $quiz->load(['questions.answers']);
    }

    /**
     * Get user's quiz attempts.
     */
    public function getUserAttempts(User $user, Quiz $quiz): \Illuminate\Database\Eloquent\Collection
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get quiz statistics.
     */
    public function getStats(Quiz $quiz): array
    {
        $attempts = $quiz->attempts();
        $completedAttempts = $attempts->whereNotNull('completed_at');

        return [
            'total_attempts' => $attempts->count(),
            'completed_attempts' => $completedAttempts->count(),
            'average_score' => $completedAttempts->avg('score'),
            'pass_rate' => $completedAttempts->where('is_passed', true)->count() / max($completedAttempts->count(), 1) * 100,
            'average_time' => $completedAttempts->avg('time_taken_seconds'),
        ];
    }
}
