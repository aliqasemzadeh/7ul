<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendSmsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    public function test_registration_page_can_be_rendered(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_user_can_register_with_mobile_and_email_and_verify_otp(): void
    {
        Queue::fake();

        Http::fake([
            'https://srscrm.ir/api/sms/send' => Http::response([
                'ok' => true,
                'code' => 'queued',
                'message' => 'queued',
                'data' => [
                    'message_id' => 1,
                    'cost' => 0,
                ],
            ]),
        ]);

        $component = Livewire::test('pages::auth.register')
            ->set('mobile', '09121112233')
            ->set('email', 'new@example.com')
            ->call('sendCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        $user = User::query()->where('mobile', '09121112233')->first();

        $this->assertNotNull($user);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNotNull($user->registration_ip);
        $this->assertNull($user->mobile_verified_at);

        Queue::assertPushed(SendSmsJob::class);

        $otp = $user->oneTimePasswords()->first();
        $this->assertNotNull($otp);

        $component
            ->set('otp', $otp->password)
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->mobile_verified_at);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        Queue::fake();

        User::factory()->create([
            'mobile' => '09120000001',
            'email' => 'taken@example.com',
        ]);

        Livewire::test('pages::auth.register')
            ->set('mobile', '09120000002')
            ->set('email', 'taken@example.com')
            ->call('sendCode')
            ->assertHasErrors(['email']);
    }

    public function test_registration_rejects_conflicting_mobile_email_pair(): void
    {
        Queue::fake();

        User::factory()->create([
            'mobile' => '09120000003',
            'email' => 'owner@example.com',
        ]);

        Livewire::test('pages::auth.register')
            ->set('mobile', '09120000003')
            ->set('email', 'other@example.com')
            ->call('sendCode')
            ->assertHasErrors(['mobile']);
    }
}
