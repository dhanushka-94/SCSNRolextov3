<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_developer_can_open_the_api_documentation(): void
    {
        $admin = User::factory()->admin()->create();
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($admin, 'web')
            ->get(route('admin.settings.api-docs'))
            ->assertForbidden();

        $page = $this->actingAs($developer, 'web')
            ->get(route('admin.settings.api-docs'))
            ->assertOk();

        // Plain string checks: a failed assertSee() would print the whole page as the diff.
        $html = $page->getContent();
        $this->assertTrue(str_contains($html, 'auth/logout-others'), 'Docs are missing the sign-out-other-devices endpoint.');
        $this->assertTrue(str_contains($html, 'At least 10 characters'), 'Docs are missing the new password rule.');
    }

    public function test_only_the_developer_can_open_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($admin, 'web')->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($developer, 'web')->get(route('admin.settings.index'))->assertOk();
    }
}
