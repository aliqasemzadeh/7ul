<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendSmsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
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

    public function test_authenticated_users_are_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }
}
