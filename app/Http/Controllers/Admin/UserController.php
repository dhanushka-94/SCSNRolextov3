<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when(! $request->user('web')->isDeveloper(), fn ($query) => $query->where('role', '!=', User::ROLE_DEVELOPER))
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
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $request->only(['q', 'role', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['role' => User::ROLE_RRISL_OFFICER, 'status' => User::STATUS_ACTIVE]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());

        ActivityLog::record('user.created', $user, ['role' => $user->role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account created successfully.');
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($user->canBeManagedBy($request->user('web')), 404);

        return view('users.show', compact('user'));
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($user->canBeManagedBy($request->user('web')), 404);

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($this->isLastAdmin($user) && (! in_array($data['role'], User::adminRoles(), true) || $data['status'] !== User::STATUS_ACTIVE)) {
            return back()->withErrors([
                'role' => 'The last active administrator cannot be demoted or deactivated.',
            ])->withInput();
        }

        if ($user->is($request->user('web')) && $data['status'] !== User::STATUS_ACTIVE) {
            return back()->withErrors([
                'status' => 'You cannot deactivate your own account.',
            ])->withInput();
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

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->canBeManagedBy($request->user('web')), 404);

        if ($user->is($request->user('web'))) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        if ($this->isLastAdmin($user)) {
            return back()->withErrors([
                'user' => 'The last administrator account cannot be deleted.',
            ]);
        }

        ActivityLog::record('user.deleted', $user, ['email' => $user->email, 'role' => $user->role]);

        $user->tokens()->delete();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account deleted successfully.');
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
