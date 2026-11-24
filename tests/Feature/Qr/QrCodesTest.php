<?php

namespace Tests\Feature\Qr;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Planter;
use App\Models\User;
use App\Services\PlanterIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_qr_is_the_reference_number_and_never_changes(): void
    {
        $identity = app(PlanterIdentityService::class);
        $planter = $this->planter(['status' => Planter::STATUS_PENDING, 'identification_number' => null]);

        $atRegistration = $identity->qrPayload($planter);
        $this->assertSame($planter->temporary_id, $atRegistration);

        $planter->update(['identification_number' => 'RUB/SUS/KEGA/TEST/00001', 'status' => Planter::STATUS_APPROVED]);
        $this->assertSame($atRegistration, $identity->qrPayload($planter->fresh()));

        $this->certificate($planter);
        $this->assertSame($atRegistration, $identity->qrPayload($planter->fresh()->load('currentCertificate')));
    }

    public function test_each_certificate_has_its_own_unique_qr(): void
    {
        $identity = app(PlanterIdentityService::class);
        $planter = $this->planter();

        $old = $this->certificate($planter, ['status' => Certificate::STATUS_REVOKED, 'is_current' => false]);
        $new = $this->certificate($planter);

        $oldPayload = $identity->certificateQrPayload($old);
        $newPayload = $identity->certificateQrPayload($new);

        $this->assertNotSame($oldPayload, $newPayload);
        $this->assertStringContainsString($old->public_token, $oldPayload);
        $this->assertStringContainsString($new->public_token, $newPayload);
        $this->assertNotSame($identity->qrPayload($planter), $newPayload);
    }

    public function test_public_certificate_qr_image_belongs_to_that_certificate(): void
    {
        $planter = $this->planter();
        $certificate = $this->certificate($planter);

        $response = $this->get('/verify/'.$certificate->public_token.'/qr.png');

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->get('/verify/not-a-real-token/qr.png')->assertNotFound();
    }

    public function test_public_verify_page_tells_valid_from_revoked_and_fake(): void
    {
        $planter = $this->planter();
        $valid = $this->certificate($planter);
        $revoked = $this->certificate($planter, ['status' => Certificate::STATUS_REVOKED, 'is_current' => false]);

        $this->assertTrue(str_contains($this->get('/verify/'.$valid->public_token)->assertOk()->getContent(), 'Valid certificate'));
        $this->assertTrue(str_contains($this->get('/verify/'.$revoked->public_token)->assertOk()->getContent(), 'Revoked'));
        $this->get('/verify/'.fake()->uuid())->assertNotFound();
    }

    public function test_planter_can_get_the_tracking_qr_from_registration(): void
    {
        $planter = $this->planter(['status' => Planter::STATUS_PENDING, 'identification_number' => null]);

        $response = $this->actingAs($planter, 'planter')->get('/profile/qr.png');

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
    }

    public function test_planter_certificate_qr_needs_a_certificate(): void
    {
        $planter = $this->planter();

        $this->actingAs($planter, 'planter')->get('/certificate/qr.png')->assertNotFound();

        $this->certificate($planter);

        $response = $this->actingAs($planter, 'planter')->get('/certificate/qr.png');
        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
    }

    public function test_staff_can_scan_a_tracking_qr_by_reference_number(): void
    {
        $planter = $this->planter(['status' => Planter::STATUS_PENDING, 'identification_number' => null]);
        $auditor = User::factory()->create(['role' => User::ROLE_FIRST_AUDITOR]);

        $this->apiAs($auditor->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/scan/'.$planter->temporary_id)
            ->assertOk()
            ->assertJsonPath('qr_payload', $planter->temporary_id)
            ->assertJsonPath('certificate_qr_payload', null)
            ->assertJsonStructure(['planter', 'audit_timeline', 'permissions', 'audits']);

        $this->assertTrue(
            ActivityLog::query()->where('event', 'planter.scanned')->where('subject_id', $planter->id)->exists()
        );
    }

    public function test_scan_also_accepts_the_sppnr_id_and_ignores_case(): void
    {
        $planter = $this->planter(['identification_number' => 'RUB/SUS/KEGA/TEST/00077']);
        $officer = User::factory()->create(['role' => User::ROLE_FIELD_OFFICER]);
        $token = $officer->createToken('phone', ['staff'])->plainTextToken;

        $this->apiAs($token)
            ->getJson('/api/v1/staff/scan/rub/sus/kega/test/00077')
            ->assertOk()
            ->assertJsonPath('qr_payload', $planter->temporary_id);

        $this->apiAs($token)
            ->getJson('/api/v1/staff/scan/'.strtolower($planter->temporary_id))
            ->assertOk();
    }

    public function test_scan_of_an_unknown_code_is_404_and_planters_cannot_scan(): void
    {
        $planter = $this->planter();
        $officer = User::factory()->create(['role' => User::ROLE_RRISL_OFFICER]);

        $this->getJson('/api/v1/staff/scan/'.$planter->temporary_id)->assertUnauthorized();

        $this->apiAs($officer->createToken('phone', ['staff'])->plainTextToken)
            ->getJson('/api/v1/staff/scan/TMP-0000-000000')
            ->assertNotFound();

        $this->apiAs($planter->createToken('phone', ['planter'])->plainTextToken)
            ->getJson('/api/v1/staff/scan/'.$planter->temporary_id)
            ->assertForbidden();
    }

    public function test_planter_dashboard_returns_both_qr_payloads(): void
    {
        $planter = $this->planter();
        $token = $planter->createToken('phone', ['planter'])->plainTextToken;

        $this->apiAs($token)
            ->getJson('/api/v1/planter/dashboard')
            ->assertOk()
            ->assertJsonPath('qr_payload', $planter->temporary_id)
            ->assertJsonPath('certificate_qr_payload', null);

        $certificate = $this->certificate($planter);

        $this->apiAs($token)
            ->getJson('/api/v1/planter/dashboard')
            ->assertOk()
            ->assertJsonPath('qr_payload', $planter->temporary_id)
            ->assertJsonPath('certificate_qr_payload', $certificate->verifyUrl());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function planter(array $overrides = []): Planter
    {
        return Planter::query()->create(array_merge([
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
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function certificate(Planter $planter, array $overrides = []): Certificate
    {
        static $sequence = 0;
        $sequence++;

        return Certificate::query()->create(array_merge([
            'planter_id' => $planter->id,
            'certificate_number' => 'SPPNR/TEST/'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
            'status' => Certificate::STATUS_ISSUED,
            'outcome' => Certificate::OUTCOME_CERTIFIED,
            'is_current' => true,
            'issued_by' => User::factory()->admin()->create()->id,
            'issued_at' => now(),
        ], $overrides));
    }
}
