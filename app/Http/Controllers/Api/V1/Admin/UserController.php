<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when(! $request->user()->isDeveloper(), fn ($query) => $query->where('role', '!=', User::ROLE_DEVELOPER))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(min($request->integer('per_page', 20), 100))
            ->withQueryString();

        return UserResource::collection($users)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $user = User::query()->create($this->validatedFrom(StoreUserRequest::class, $request));

        ActivityLog::record('user.created', $user, ['role' => $user->role]);

        return response()->json(['user' => new UserResource($user)], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $this->validatedFrom(UpdateUserRequest::class, $request);
        $actor = $request->user();

        if ($this->isLastAdmin($user) && (! in_array($data['role'], User::adminRoles(), true) || $data['status'] !== User::STATUS_ACTIVE)) {
            throw ValidationException::withMessages([
                'role' => ['The last active administrator cannot be demoted or deactivated.'],
            ]);
        }

        if ($user->is($actor) && $data['status'] !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => ['You cannot deactivate your own account.'],
            ]);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        // A deactivated or demoted user must not keep using existing app tokens.
        if ($user->status !== User::STATUS_ACTIVE) {
            $user->tokens()->delete();
        }

        ActivityLog::record('user.updated', $user, ['changed' => array_keys($data), 'status' => $user->status, 'role' => $user->role]);

        return response()->json(['user' => new UserResource($user->fresh())]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($user->canBeManagedBy($request->user()), 404);

        if ($user->is($request->user())) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if ($this->isLastAdmin($user)) {
            return response()->json(['message' => 'The last administrator account cannot be deleted.'], 422);
        }

        ActivityLog::record('user.deleted', $user, ['email' => $user->email, 'role' => $user->role]);

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User account deleted.']);
    }

    private function isLastAdmin(User $user): bool
    {
        if (! $user->isAdmin() || ! $user->isActive()) {
            return false;
        }

        return User::query()
            ->whereIn('role', User::adminRoles())
            ->where('status', User::STATUS_ACTIVE)
            ->whereKeyNot($user->id)
            ->doesntExist();
    }
}
