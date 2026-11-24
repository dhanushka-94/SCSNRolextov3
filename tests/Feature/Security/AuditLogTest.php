<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_is_logged_with_the_actor_and_never_the_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->apiAs($admin->createToken('phone', ['staff'])->plainTextToken)
            ->postJson('/api/v1/admin/users', [
                'name' => 'New Officer',
                'email' => 'new.officer@test.lk',
                'phone' => '0771234567',
                'role' => User::ROLE_FIELD_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => 'NewOfficer2026',
                'password_confirmation' => 'NewOfficer2026',
            ])
            ->assertCreated();

        $log = ActivityLog::query()->where('event', 'user.created')->firstOrFail();

        $this->assertSame($admin->id, $log->causer_id);
        $this->assertSame(User::class, $log->causer_type);
        $this->assertStringNotContainsString('NewOfficer2026', json_encode($log->properties));
    }

    public function test_staff_sign_in_is_logged(): void
    {
        $user = User::factory()->create(['email' => 'signin@test.lk', 'role' => User::ROLE_FIELD_OFFICER]);

        $this->postJson('/api/v1/auth/staff/login', [
            'email' => 'signin@test.lk',
            'password' => 'password',
            'device_name' => 'Test phone',
        ])->assertOk();

        $log = ActivityLog::query()->where('event', 'auth.staff_login')->firstOrFail();

        $this->assertSame($user->id, $log->causer_id);
        $this->assertSame('Test phone', $log->properties['device']);
    }

    public function test_maintenance_changes_are_logged_with_the_developer(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->apiAs($developer->createToken('phone', ['staff'])->plainTextToken)
            ->putJson('/api/v1/admin/maintenance', ['enabled' => true, 'message' => 'Upgrade tonight'])
            ->assertOk();

        $log = ActivityLog::query()->where('event', 'maintenance.updated')->firstOrFail();

        $this->assertSame($developer->id, $log->causer_id);
        $this->assertTrue($log->properties['enabled']);
    }

    public function test_failed_web_admin_sign_in_is_logged(): void
    {
        User::factory()->admin()->create(['email' => 'web.admin@test.lk']);

        $this->post(route('admin.login.store'), [
            'email' => 'web.admin@test.lk',
            'password' => 'wrong-password',
        ]);

        $log = ActivityLog::query()->where('event', 'auth.admin_login_failed')->firstOrFail();

        $this->assertSame('web.admin@test.lk', $log->properties['identifier']);
        $this->assertNull($log->causer_id);
    }

    public function test_successful_web_admin_sign_in_is_logged_with_the_admin(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'web.ok@test.lk']);

        $this->post(route('admin.login.store'), [
            'email' => 'web.ok@test.lk',
            'password' => 'password',
        ])->assertRedirect();

        $log = ActivityLog::query()->where('event', 'auth.admin_login')->firstOrFail();

        $this->assertSame($admin->id, $log->causer_id);
    }

    public function test_deactivating_a_user_from_the_web_signs_them_out_everywhere(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);
        $officer->createToken('phone', ['staff']);
        $officer->createToken('tablet', ['staff']);

        $this->actingAs($admin, 'web')
            ->put(route('admin.users.update', $officer), [
                'name' => $officer->name,
                'email' => $officer->email,
                'phone' => $officer->phone,
                'role' => User::ROLE_FIELD_OFFICER,
                'status' => User::STATUS_INACTIVE,
            ])
            ->assertRedirect();

        $this->assertSame(0, $officer->tokens()->count());
    }
}
