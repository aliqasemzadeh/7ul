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

#[Fillable(['mobile', 'registration_ip'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use AuthenticationLoggable, HasFactory, HasOneTimePasswords, Notifiable;

    /**
     * @return list<string>
     */
    public function notifyAuthenticationLogVia(): array
    {
        return [];
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }
}
