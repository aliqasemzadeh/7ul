<?php

namespace App\Enums;

enum LoginMethod: string
{
    case MobileOtp = 'mobile_otp';
    case EmailOtp = 'email_otp';
    case EmailPassword = 'email_password';

    public function label(): string
    {
        return __('app.admin.settings.login_methods.'.$this->value);
    }

    public function requiresRegistration(): bool
    {
        return $this === self::EmailPassword;
    }

    public function usesPassword(): bool
    {
        return $this === self::EmailPassword;
    }

    public function usesEmail(): bool
    {
        return $this === self::EmailOtp || $this === self::EmailPassword;
    }

    public function usesOtp(): bool
    {
        return $this === self::MobileOtp || $this === self::EmailOtp;
    }
}
