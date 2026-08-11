<?php

namespace Tests\Feature\Auth;

use App\Enums\LoginMethod;
use App\Jobs\SendSmsJob;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.sms', [
            'token' => 'test-token',
            'gateway' => '1000',
            'url' => 'https://srscrm.ir/api/sms/send',
        ]);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_new_mobile_is_registered_and_can_verify_otp(): void
    {
        Queue::fake();

        $component = Livewire::test('pages::auth.login')
            ->set('mobile', '09121112233')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        $user = User::query()->where('mobile', '09121112233')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->registration_ip);

        Queue::assertPushed(SendSmsJob::class);

        $otp = $user->oneTimePasswords()->first();
        $this->assertNotNull($otp);

        $component
            ->set('otp', $otp->password)
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_existing_user_can_login_with_mobile_otp(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'mobile' => '09123334455',
        ]);

        $component = Livewire::test('pages::auth.login')
            ->set('mobile', '09123334455')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        Queue::assertPushed(SendSmsJob::class);

        $otp = $user->oneTimePasswords()->first();
        $this->assertNotNull($otp);

        $component
            ->set('otp', $otp->password)
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::query()->where('mobile', '09123334455')->count());
    }

    public function test_login_fails_with_invalid_otp(): void
    {
        Queue::fake();

        User::factory()->create([
            'mobile' => '09125556677',
        ]);

        Livewire::test('pages::auth.login')
            ->set('mobile', '09125556677')
            ->call('sendCode')
            ->set('otp', '000000')
            ->call('verify')
            ->assertHasErrors(['otp']);

        $this->assertGuest();
    }

    public function test_send_code_rejects_non_iranian_mobile_formats(): void
    {
        Queue::fake();

        Livewire::test('pages::auth.login')
            ->set('mobile', '08121112233')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);

        Livewire::test('pages::auth.login')
            ->set('mobile', '9121112233')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);

        Livewire::test('pages::auth.login')
            ->set('mobile', '+989121112233')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);

        Livewire::test('pages::auth.login')
            ->set('mobile', '0912111223')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);

        Queue::assertNothingPushed();
        $this->assertGuest();
    }

    public function test_persian_digits_are_normalized_before_validation(): void
    {
        Queue::fake();

        Livewire::test('pages::auth.login')
            ->set('mobile', '۰۹۱۲۱۱۱۲۲۳۳')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('mobile', '09121112233')
            ->assertSet('step', 'otp');

        $this->assertNotNull(User::query()->where('mobile', '09121112233')->first());
        Queue::assertPushed(SendSmsJob::class);
    }

    public function test_authenticated_users_are_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_email_otp_login_creates_user_and_authenticates(): void
    {
        Notification::fake();

        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailOtp->value;
        $settings->save();

        $component = Livewire::test('pages::auth.login')
            ->set('email', 'otp@example.com')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        $user = User::query()->where('email', 'otp@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, OneTimePasswordNotification::class);

        $otp = $user->oneTimePasswords()->first();
        $this->assertNotNull($otp);

        $component
            ->set('otp', $otp->password)
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_email_password_login_authenticates_existing_user(): void
    {
        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        $user = User::factory()->emailPasswordUser('user@example.com', 'password123')->create();

        Livewire::test('pages::auth.login')
            ->set('email', 'user@example.com')
            ->set('password', 'password123')
            ->call('loginWithPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_password_login_rejects_invalid_credentials(): void
    {
        $settings = app(AuthSettings::class);
        $settings->login_method = LoginMethod::EmailPassword->value;
        $settings->save();

        User::factory()->emailPasswordUser('user@example.com', 'password123')->create();

        Livewire::test('pages::auth.login')
            ->set('email', 'user@example.com')
            ->set('password', 'wrong-password')
            ->call('loginWithPassword')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
