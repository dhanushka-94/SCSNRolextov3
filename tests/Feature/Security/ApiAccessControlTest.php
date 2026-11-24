<?php

namespace Tests\Feature\Security;

use App\Models\Planter;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_without_a_token_are_rejected(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->getJson('/api/v1/staff/dashboard')->assertUnauthorized();
        $this->getJson('/api/v1/planter/dashboard')->assertUnauthorized();
    }

    public function test_planter_token_cannot_reach_staff_routes(): void
    {
        $planter = Planter::query()->create($this->planterAttributes());

        $this->withToken($planter->createToken('phone', ['planter'])->plainTextToken)
            ->getJson('/api/v1/staff/dashboard')
            ->assertForbidden();
    }

    public function test_staff_token_cannot_reach_planter_routes(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_RRISL_OFFICER]);

        $this->withToken($user->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/planter/dashboard')
            ->assertForbidden();
    }

    public function test_field_officer_cannot_manage_user_accounts(): void
    {
        $officer = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);

        $this->withToken($officer->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_only_the_developer_can_read_or_change_maintenance_mode(): void
    {
        $admin = User::factory()->admin()->create();
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->apiAs($admin->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/admin/maintenance')
            ->assertForbidden();

        $this->apiAs($developer->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/admin/maintenance')
            ->assertOk();
    }

    public function test_only_the_developer_can_read_sms_templates(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->create(['role' => User::ROLE_RRISL_OFFICER]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->apiAs($admin->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/sms-templates')
            ->assertForbidden();

        $this->apiAs($officer->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/sms-templates')
            ->assertForbidden();

        $this->apiAs($developer->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/sms-templates')
            ->assertOk()
            ->assertJsonStructure(['templates']);
    }

    public function test_a_planter_only_sees_their_own_profile(): void
    {
        $alpha = Planter::query()->create($this->planterAttributes(['name' => 'Planter Alpha Test']));
        Planter::query()->create($this->planterAttributes(['name' => 'Planter Beta Test']));

        $this->withToken($alpha->createToken('phone', ['planter'])->plainTextToken)
            ->getJson('/api/v1/planter/profile')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Planter Alpha Test'])
            ->assertJsonMissing(['name' => 'Planter Beta Test']);
    }

    public function test_maintenance_mode_blocks_planters_but_not_administrators(): void
    {
        SystemSetting::write(SystemSetting::MAINTENANCE_MODE, '1');
        $planter = Planter::query()->create($this->planterAttributes());
        $admin = User::factory()->admin()->create();

        $this->apiAs($planter->createToken('phone', ['planter'])->plainTextToken)
            ->getJson('/api/v1/planter/dashboard')
            ->assertStatus(503)
            ->assertJsonPath('maintenance', true);

        $this->apiAs($admin->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/dashboard')
            ->assertOk();
    }

    public function test_api_responses_carry_security_headers(): void
    {
        $this->getJson('/api/v1/meta')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_error_responses_carry_security_headers_and_hide_the_php_version(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeaderMissing('X-Powered-By');

        $this->getJson('/api/v1/planter/profile', ['Accept' => 'application/json'])
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_api_does_not_allow_any_browser_origin_by_default(): void
    {
        $this->withHeaders(['Origin' => 'https://evil.example'])
            ->getJson('/api/v1/reference/options')
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
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
