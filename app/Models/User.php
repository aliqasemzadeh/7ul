<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['mobile', 'email', 'password', 'registration_ip', 'api_token'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use AuthenticationLoggable, HasFactory, HasOneTimePasswords, HasRoles, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * @return list<string>
     */
    public function notifyAuthenticationLogVia(): array
    {
        return [];
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function ensureApiToken(): string
    {
        if (is_string($this->api_token) && $this->api_token !== '') {
            return $this->api_token;
        }

        return $this->regenerateApiToken();
    }

    public function regenerateApiToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::query()->where('api_token', $token)->exists());

        $this->forceFill(['api_token' => $token])->save();

        return $token;
    }
}
