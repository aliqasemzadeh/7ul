<?php

namespace Tests\Feature\Auth;

use App\Enums\LoginMethod;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;
use Tests\TestCase;

class ForgetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forget_password_page_is_redirected_when_password_login_is_disabled(): void
    {
        $this->get(route('password.request'))->assertRedirect(route('login'));
    }

    public function test_user_can_reset_password_with_email_otp(): void
    {
        Notification::fake();

        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        $user = User::factory()->emailPasswordUser('reset@example.com', 'old-password')->create();

        $component = Livewire::test('pages::auth.forget-password')
            ->set('email', 'reset@example.com')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'reset');

        Notification::assertSentTo($user, OneTimePasswordNotification::class);

        $otp = $user->oneTimePasswords()->first();
        $this->assertNotNull($otp);

        $component
            ->set('otp', $otp->password)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_forget_password_rejects_unknown_email(): void
    {
        Notification::fake();

        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        Livewire::test('pages::auth.forget-password')
            ->set('email', 'missing@example.com')
            ->call('sendCode')
            ->assertHasErrors(['email']);

        Notification::assertNothingSent();
    }
}
