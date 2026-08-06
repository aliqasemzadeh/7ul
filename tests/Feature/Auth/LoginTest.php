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

    public function test_user_can_login_with_mobile_otp(): void
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
    }

    public function test_login_fails_for_unknown_mobile(): void
    {
        Queue::fake();

        Livewire::test('pages::auth.login')
            ->set('mobile', '09129998877')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);

        Queue::assertNothingPushed();
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
