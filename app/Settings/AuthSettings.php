<?php

namespace App\Settings;

use App\Enums\LoginMethod;
use Spatie\LaravelSettings\Settings;

class AuthSettings extends Settings
{
    public string $login_method;

    public static function group(): string
    {
        return 'auth';
    }

    public function loginMethod(): LoginMethod
    {
        return LoginMethod::from($this->login_method);
    }

    public function allowsRegistration(): bool
    {
        return $this->loginMethod()->requiresRegistration();
    }
}
