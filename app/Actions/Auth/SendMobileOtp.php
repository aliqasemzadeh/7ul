<?php

namespace App\Actions\Auth;

use App\Jobs\SendSmsJob;
use App\Models\User;

class SendMobileOtp
{
    public function handle(User $user): void
    {
        $oneTimePassword = $user->createOneTimePassword();

        $message = __('app.auth.otp_message', [
            'code' => $oneTimePassword->password,
        ]);

        SendSmsJob::dispatch(
            to: $user->mobile,
            message: $message,
            userId: $user->id,
        );
    }
}
