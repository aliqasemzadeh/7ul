<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetUserAsAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_admin_role_when_mobile_argument_is_valid(): void
    {
        $this->artisan('app:set-user-as-admin', ['mobile' => '09121234567'])
            ->expectsOutputToContain('09121234567')
            ->assertSuccessful();

        $user = User::query()->where('mobile', '09121234567')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_it_asks_for_mobile_when_argument_is_missing(): void
    {
        $this->artisan('app:set-user-as-admin')
            ->expectsQuestion(__('app.console.set_admin.ask_mobile'), '09129876543')
            ->assertSuccessful();

        $user = User::query()->where('mobile', '09129876543')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_it_fails_when_mobile_format_is_invalid(): void
    {
        $this->artisan('app:set-user-as-admin', ['mobile' => '12345'])
            ->expectsOutput(__('app.auth.mobile_invalid'))
            ->assertFailed();

        $this->assertNull(User::query()->where('mobile', '12345')->first());
    }

    public function test_it_normalizes_persian_digits_before_assigning_admin(): void
    {
        $this->artisan('app:set-user-as-admin', ['mobile' => '۰۹۱۲۱۱۱۲۲۳۳'])
            ->assertSuccessful();

        $user = User::query()->where('mobile', '09121112233')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
    }
}
