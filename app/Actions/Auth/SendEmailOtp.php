<?php

namespace App\Actions\Auth;

use App\Models\User;

class SendEmailOtp
{
    public function handle(User $user): void
    {
        $user->sendOneTimePassword();
    }
}
