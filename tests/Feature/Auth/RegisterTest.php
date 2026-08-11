<?php

namespace Tests\Feature\Auth;

use App\Enums\LoginMethod;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_redirected_when_password_login_is_disabled(): void
    {
        $this->get(route('register'))->assertRedirect(route('login'));
    }

    public function test_user_can_register_when_email_password_login_is_enabled(): void
    {
        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        Livewire::test('pages::auth.register')
            ->set('email', 'new@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $user = User::query()->where('email', 'new@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->mobile);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        User::factory()->emailPasswordUser('taken@example.com', 'password123')->create();

        Livewire::test('pages::auth.register')
            ->set('email', 'taken@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
