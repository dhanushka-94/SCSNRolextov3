<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_super_admin_with_a_prompted_password(): void
    {
        $this->artisan('sppnr:create-admin', ['email' => 'Owner@Example.lk', '--name' => 'Site Owner'])
            ->expectsQuestion('Password (At least 10 characters, with upper and lower case letters and a number.)', 'Strong-Pass-2026')
            ->expectsQuestion('Confirm password', 'Strong-Pass-2026')
            ->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.lk')->firstOrFail();

        $this->assertSame(User::ROLE_DEVELOPER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertTrue(password_verify('Strong-Pass-2026', $user->password));
    }

    public function test_it_rejects_a_weak_password(): void
    {
        $this->artisan('sppnr:create-admin', ['email' => 'weak@example.lk', '--name' => 'Weak'])
            ->expectsQuestion('Password (At least 10 characters, with upper and lower case letters and a number.)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.lk']);
    }

    public function test_it_will_not_overwrite_an_existing_account_without_the_reset_flag(): void
    {
        User::factory()->create(['email' => 'taken@example.lk']);

        $this->artisan('sppnr:create-admin', ['email' => 'taken@example.lk', '--name' => 'Taken'])
            ->assertFailed();
    }

    public function test_the_demo_seeder_is_blocked_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('blocked in production');

        (new DatabaseSeeder)->run();
    }
}
