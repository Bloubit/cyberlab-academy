<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(
        private readonly QuizService $quizService
    ) {}

    /**
     * List all quizzes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $quizzes = Quiz::with(['questions'])
                ->when($request->course_id, fn($q, $v) => $q->where('course_id', $v))
                ->when($request->module_id, fn($q, $v) => $q->where('module_id', $v))
                ->when($request->type, fn($q, $v) => $q->where('type', $v))
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => QuizResource::collection($quizzes),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quizzes',
            ], 500);
        }
    }

    /**
     * Get single quiz with questions
     */
    public function show($id): JsonResponse
    {
        try {
            $quiz = Quiz::with(['questions'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => (new QuizResource($quiz))->withQuestions(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz',
            ], 500);
        }
    }

    /**
     * Start a quiz attempt
     */
    public function start(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $quiz = Quiz::findOrFail($id);
            $attempt = $this->quizService->startAttempt($user, $quiz);

            return response()->json([
                'success' => true,
                'message' => 'Quiz started',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'quiz' => (new QuizResource($quiz))->withQuestions(),
                    'started_at' => $attempt->started_at,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to start quiz',
            ], 500);
        }
    }

    /**
     * Submit quiz attempt
     */
    public function submit(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $request->validate([
                'attempt_id' => 'required|integer|exists:quiz_attempts,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answer' => 'required',
            ]);

            $quiz = Quiz::findOrFail($id);
            $attempt = QuizAttempt::findOrFail($request->attempt_id);

            // Verify attempt belongs to user
            if ($attempt->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $result = $this->quizService->submitAttempt(
                $attempt,
                $request->input('answers')
            );

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to submit quiz',
            ], 500);
        }
    }

    /**
     * Get quiz results
     */
    public function results(Request $request, $id, $attemptId): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $attempt = QuizAttempt::with(['quiz', 'answers'])
                ->where('id', $attemptId)
                ->where('quiz_id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $this->quizService->getResults($attempt),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch results',
            ], 500);
        }
    }
}
