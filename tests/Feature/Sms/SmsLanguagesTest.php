<?php

namespace Tests\Feature\Sms;

use App\Models\User;
use App\Services\NotifySmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsLanguagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_template_has_english_sinhala_and_tamil_text(): void
    {
        $sms = app(NotifySmsService::class);

        foreach ($sms->templates() as $key => $template) {
            foreach (NotifySmsService::LANGUAGES as $lang) {
                $body = $sms->localised($template, 'body', $lang);
                $name = $sms->localised($template, 'name', $lang);

                $this->assertNotNull($body, "{$key} has no {$lang} body.");
                $this->assertNotNull($name, "{$key} has no {$lang} name.");
                $this->assertLessThanOrEqual(320, mb_strlen($body), "{$key} {$lang} body is over 320 characters.");

                foreach ($template['placeholders'] as $placeholder) {
                    $this->assertStringContainsString('{'.$placeholder.'}', $body, "{$key} {$lang} body is missing {{$placeholder}}.");
                }
            }
        }
    }

    public function test_sample_messages_render_fully_in_each_language(): void
    {
        $sms = app(NotifySmsService::class);

        foreach (array_keys($sms->templates()) as $key) {
            foreach (NotifySmsService::LANGUAGES as $lang) {
                $message = $sms->renderSample($key, $lang);

                $this->assertNotNull($message, "{$key} {$lang} sample did not render.");
                $this->assertDoesNotMatchRegularExpression('/\{[a-z_]+\}/', $message, "{$key} {$lang} sample still has a placeholder.");
                $this->assertLessThanOrEqual(320, mb_strlen($message));
            }
        }

        $this->assertStringContainsString('ප්‍රතිඵලය', $sms->renderSample('first_audit_result', 'si'));
        $this->assertStringContainsString('தணிக்கை', $sms->renderSample('first_audit_result', 'ta'));
    }

    public function test_sending_all_languages_reports_each_message(): void
    {
        // Gateway is off in tests, so nothing leaves the machine; the result still lists every message.
        config(['services.notify.enabled' => false]);
        $count = count(app(NotifySmsService::class)->templates());

        $result = app(NotifySmsService::class)->sendAllSamples('0714245192', NotifySmsService::LANGUAGES);

        $this->assertCount(0, $result['sent']);
        $this->assertCount($count * 3, $result['failed']);
        $this->assertContains('si:certificate_issued', $result['failed']);
        $this->assertContains('ta:registration_submitted', $result['failed']);
        // English keeps the plain key so existing API clients see the same values.
        $this->assertContains('certificate_issued', $result['failed']);
    }

    public function test_sinhala_and_tamil_messages_are_sent_as_unicode(): void
    {
        Http::fake([
            'app.notify.lk/*' => Http::response(['status' => 'success', 'data' => 'Sent'], 200),
        ]);
        config([
            'services.notify.enabled' => true,
            'services.notify.user_id' => 'test-user',
            'services.notify.api_key' => 'test-key',
        ]);

        $sms = app(NotifySmsService::class);

        $this->assertTrue($sms->needsUnicode($sms->renderSample('registration_approved', 'si')));
        $this->assertTrue($sms->needsUnicode($sms->renderSample('registration_approved', 'ta')));
        $this->assertFalse($sms->needsUnicode($sms->renderSample('registration_approved', 'en')));

        $this->assertTrue($sms->send('0714245192', $sms->renderSample('registration_approved', 'si')));

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($request->url(), '/send')
                && ($data['type'] ?? null) === 'unicode'
                && ($data['to'] ?? null) === '94714245192'
                && str_contains($data['message'] ?? '', 'අනුමත');
        });
    }

    public function test_settings_page_lists_every_template_in_all_languages(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $html = $this->actingAs($developer, 'web')
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->getContent();

        $this->assertTrue(str_contains($html, 'SMS message templates'));
        $this->assertTrue(str_contains($html, 'Send all samples'));
        $this->assertTrue(str_contains($html, 'name="language"'));
        $this->assertFalse(str_contains($html, 'Send test messages'));
        $this->assertFalse(str_contains($html, '>SMS templates<'));

        foreach (app(NotifySmsService::class)->templates() as $key => $template) {
            $this->assertTrue(str_contains($html, 'id="sms-'.$key.'"'), "{$key} is missing from settings.");
            // Plain string checks: a failed assertSee() would print the whole page as the diff.
            $this->assertTrue(str_contains($html, $template['name_si']), "{$key} Sinhala name missing.");
            $this->assertTrue(str_contains($html, $template['name_ta']), "{$key} Tamil name missing.");
            $this->assertTrue(str_contains($html, htmlspecialchars($template['body_ta'], ENT_QUOTES)), "{$key} Tamil body missing.");
        }
    }

    public function test_the_old_sms_templates_page_opens_settings(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer, 'web')
            ->get('/admin/sms-templates')
            ->assertRedirect(route('admin.settings.index'));
    }

    public function test_sample_sms_is_sent_from_the_settings_page(): void
    {
        Http::fake();
        config(['services.notify.enabled' => false]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $response = $this->actingAs($developer, 'web')
            ->from(route('admin.settings.index'))
            ->post(route('admin.settings.sms-samples'), [
                'phone' => '0714245192',
                'language' => 'si',
                'template' => 'registration_approved',
            ]);

        $response->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('error');
    }

    public function test_only_the_developer_sees_the_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }
}
