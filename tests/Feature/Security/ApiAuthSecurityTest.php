<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Planter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_tokens_stop_working_after_thirty_days(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);

        $token = $user->createToken('phone', ['staff']);
        $this->apiAs($token->plainTextToken)->getJson('/api/v1/auth/me')->assertOk();

        $token->accessToken->forceFill(['created_at' => now()->subDays(31)])->save();

        $this->apiAs($token->plainTextToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_password_change_rejects_weak_passwords(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);
        $token = $user->createToken('phone', ['staff'])->plainTextToken;

        $this->apiAs($token)->postJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->apiAs($token)->postJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_password_change_signs_out_other_devices_only(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);
        $current = $user->createToken('phone', ['staff'])->plainTextToken;
        $user->createToken('tablet', ['staff']);

        $this->apiAs($current)->postJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'NewPassw0rd2026',
            'password_confirmation' => 'NewPassw0rd2026',
        ])->assertOk();

        $this->assertSame(1, $user->tokens()->count());
        $this->apiAs($current)->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_sign_out_other_devices_keeps_only_the_current_token(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);
        $current = $user->createToken('phone', ['staff']);
        $user->createToken('tablet', ['staff']);
        $user->createToken('laptop', ['staff']);

        $this->apiAs($current->plainTextToken)
            ->postJson('/api/v1/auth/logout-others')
            ->assertOk()
            ->assertJsonPath('revoked', 2);

        $this->assertSame([$current->accessToken->id], $user->tokens()->pluck('id')->all());
    }

    public function test_staff_login_locks_after_five_wrong_passwords(): void
    {
        User::factory()->create(['email' => 'lock@test.lk', 'role' => User::ROLE_FIELD_OFFICER]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/staff/login', [
                'email' => 'lock@test.lk',
                'password' => 'wrong-password',
                'device_name' => 'phone',
            ])->assertUnprocessable()->assertJsonValidationErrors('email');
        }

        // Even the correct password is refused while the lock is active.
        $this->postJson('/api/v1/auth/staff/login', [
            'email' => 'lock@test.lk',
            'password' => 'password',
            'device_name' => 'phone',
        ])->assertUnprocessable()->assertJsonPath('errors.email.0', fn (string $message) => str_contains($message, 'Too many login attempts'));
    }

    public function test_failed_planter_login_is_logged_without_the_password(): void
    {
        Planter::query()->create($this->planterAttributes([
            'identification_number' => 'RUB/SUS/KEGA/TEST/00001',
        ]));

        $this->postJson('/api/v1/auth/planter/login', [
            'login' => 'RUB/SUS/KEGA/TEST/00001',
            'password' => 'not-the-password',
            'device_name' => 'phone',
        ])->assertUnprocessable();

        $log = ActivityLog::query()->where('event', 'auth.planter_login_failed')->firstOrFail();

        $this->assertSame('RUB/SUS/KEGA/TEST/00001', $log->properties['identifier']);
        $this->assertStringNotContainsString('not-the-password', json_encode($log->properties));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function planterAttributes(array $overrides = []): array
    {
        return array_merge([
            'temporary_id' => Planter::generateTemporaryId(),
            'identification_number' => 'RUB/SUS/KEGA/TEST/'.fake()->unique()->numerify('#####'),
            'name' => 'Test Planter '.fake()->unique()->numerify('###'),
            'nic' => fake()->unique()->numerify('9#########V'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0771234567',
            'district' => 'Kegalle',
            'address' => 'Test road',
            'password' => 'Planter-Pass-2026',
            'status' => Planter::STATUS_APPROVED,
            'approved_at' => now(),
            'audit_status' => Planter::AUDIT_NOT_STARTED,
        ], $overrides);
    }
}
