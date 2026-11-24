<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\PlanterResource;
use App\Http\Resources\Api\UserResource;
use App\Models\ActivityLog;
use App\Models\Planter;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    private const MAX_ATTEMPTS = 5;

    public function planterLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $login = trim($data['login']);
        $key = $this->throttleKey('planter', $login, $request);
        $this->ensureNotThrottled($key, 'login');

        $field = str_contains($login, '/')
            ? 'identification_number'
            : (filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'identification_number');

        $planter = Planter::query()->where($field, $login)->first();

        if (! $planter) {
            $this->fail($key, 'login', 'These credentials do not match our records.', 'auth.planter_login_failed', $login);
        }

        if ($planter->isPending()) {
            throw ValidationException::withMessages([
                'login' => 'Your registration is waiting for approval. After approval you can create a password and sign in.',
            ]);
        }

        if ($planter->isRejected()) {
            throw ValidationException::withMessages([
                'login' => 'This registration was rejected. Please contact SPPNR administration.',
            ]);
        }

        if (! $planter->hasPassword()) {
            throw ValidationException::withMessages([
                'login' => 'Your registration is approved and active. Create a password before signing in.',
            ]);
        }

        if (! Hash::check($data['password'], $planter->password)) {
            $this->fail($key, 'login', 'These credentials do not match our records.', 'auth.planter_login_failed', $login);
        }

        RateLimiter::clear($key);
        $planter->forceFill(['last_login_at' => now()])->save();

        $token = $planter->createToken($data['device_name'], ['planter'])->plainTextToken;
        ActivityLog::record('auth.planter_login', $planter, ['device' => $data['device_name']], $planter);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'account_type' => 'planter',
            'user' => new PlanterResource($planter),
        ]);
    }

    public function staffLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:190'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $key = $this->throttleKey('staff', $data['email'], $request);
        $this->ensureNotThrottled($key, 'email');

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->fail($key, 'email', 'These credentials do not match our records.', 'auth.staff_login_failed', $data['email']);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Please contact an administrator.',
            ]);
        }

        if (! $user->isAdmin() && SystemSetting::maintenanceEnabled()) {
            return response()->json([
                'message' => 'The system is under maintenance. Only administrators can sign in right now.',
                'maintenance' => true,
            ], 503, ['Retry-After' => 3600]);
        }

        RateLimiter::clear($key);
        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken($data['device_name'], ['staff'])->plainTextToken;
        ActivityLog::record('auth.staff_login', $user, ['device' => $data['device_name']], $user);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'account_type' => 'staff',
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $account = $request->user();

        return $account instanceof Planter
            ? response()->json(['account_type' => 'planter', 'user' => new PlanterResource($account)])
            : response()->json(['account_type' => 'staff', 'user' => new UserResource($account)]);
    }

    public function logout(Request $request): JsonResponse
    {
        ActivityLog::record('auth.logout', $request->user());
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    public function logoutOthers(Request $request): JsonResponse
    {
        $account = $request->user();
        $current = $account->currentAccessToken()->id;

        $revoked = $account->tokens()->where('id', '!=', $current)->delete();

        ActivityLog::record('auth.logout_others', $account, ['revoked_tokens' => $revoked]);

        return response()->json([
            'message' => 'Other devices have been signed out.',
            'revoked' => $revoked,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ]);

        $account = $request->user();

        if (! Hash::check($data['current_password'], $account->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $account->update(['password' => $data['password']]);

        // Sign out every other device; keep this one signed in.
        $revoked = $account->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        ActivityLog::record('auth.password_changed', $account, ['revoked_tokens' => $revoked]);

        return response()->json(['message' => 'Password changed. Other devices have been signed out.']);
    }

    private function throttleKey(string $type, string $identifier, Request $request): string
    {
        return Str::transliterate('api-'.$type.'|'.Str::lower(trim($identifier)).'|'.$request->ip());
    }

    private function ensureNotThrottled(string $key, string $field): void
    {
        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        throw ValidationException::withMessages([
            $field => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.',
        ]);
    }

    private function fail(string $key, string $field, string $message, string $event, string $identifier): never
    {
        RateLimiter::hit($key);

        ActivityLog::record($event, null, ['identifier' => Str::limit($identifier, 190, '')]);

        throw ValidationException::withMessages([$field => $message]);
    }
}
