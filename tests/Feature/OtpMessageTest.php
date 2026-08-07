<?php

namespace Tests\Feature;

use App\Actions\Auth\SendMobileOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendSmsJob;
use Tests\TestCase;

class OtpMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_message_contains_required_cancellation_phrase(): void
    {
        Queue::fake();
        app()->setLocale('fa');

        $user = User::factory()->create([
            'mobile' => '09123456789'
        ]);

        $action = new SendMobileOtp();
        $action->handle($user);

        Queue::assertPushed(SendSmsJob::class, function (SendSmsJob $job) {
            return str_contains($job->message, 'لغو11') || str_contains($job->message, 'لغو 11');
        });
    }

    public function test_english_otp_message_contains_required_cancellation_phrase(): void
    {
        Queue::fake();
        app()->setLocale('en');

        $user = User::factory()->create([
            'mobile' => '09123456789'
        ]);

        $action = new SendMobileOtp();
        $action->handle($user);

        Queue::assertPushed(SendSmsJob::class, function (SendSmsJob $job) {
            return str_contains($job->message, 'لغو11') || str_contains($job->message, 'لغو 11');
        });
    }
}
