<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * List all users with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Filters
            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $lower = mb_strtolower($search);
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $lower . '%'])
                      ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $lower . '%'])
                      ->orWhereRaw('LOWER(username) LIKE ?', ['%' . $lower . '%']);
                });
            }

            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->has('is_banned')) {
                $query->where('is_banned', filter_var($request->is_banned, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->role) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('slug', $request->role);
                });
            }

            $sortField = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $perPage = min((int) ($request->per_page ?? 15), 100);

            $users = $query->orderBy($sortField, $sortOrder)
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($users),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single user with details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with(['xpSummary', 'badges'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new UserResource($user),
                    'stats' => $this->userService->getStats($user),
                    'roles' => $user->getRoles(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $user = User::findOrFail($id);

            if ($user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot ban an admin user',
                ], 403);
            }

            $this->userService->ban($user, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'User banned successfully',
                'data' => new UserResource($user->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to ban user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unban a user.
     */
    public function unban(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $this->userService->unban($user);

            return response()->json([
                'success' => true,
                'message' => 'User unbanned successfully',
                'data' => new UserResource($user->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unban user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user (admin override).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'is_active' => 'sometimes|boolean',
                'bio' => 'nullable|string|max:500',
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => new UserResource($user->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'role' => 'required|string|exists:roles,slug',
            ]);

            $user = User::findOrFail($id);
            $user->assignRole($request->role);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => [
                    'roles' => $user->getRoles(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available roles.
     */
    public function roles(): JsonResponse
    {
        try {
            $roles = DB::table('roles')->get(['id', 'name', 'slug', 'description']);

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: ' . $e->getMessage(),
            ], 500);
        }
    }
}
