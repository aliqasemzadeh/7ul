<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;

#[Fillable(['mobile', 'email', 'email_verified_at', 'mobile_verified_at', 'registration_ip'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use AuthenticationLoggable, HasFactory, HasOneTimePasswords, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<SmsLog, $this>
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }
}
