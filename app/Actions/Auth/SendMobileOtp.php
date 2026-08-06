<?php

namespace App\Actions\Auth;

use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use App\Models\User;

class SendMobileOtp
{
    public function handle(User $user): SmsLog
    {
        $oneTimePassword = $user->createOneTimePassword();

        $message = __('app.auth.otp_message', [
            'code' => $oneTimePassword->password,
        ]);

        $log = SmsLog::query()->create([
            'user_id' => $user->id,
            'to' => $user->mobile,
            'message' => $message,
            'gateway' => config('services.sms.gateway'),
            'status' => 'pending',
        ]);

        SendSmsJob::dispatch(
            to: $user->mobile,
            message: $message,
            userId: $user->id,
            smsLogId: $log->id,
        );

        return $log;
    }
}
