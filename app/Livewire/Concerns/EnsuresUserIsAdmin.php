<?php

namespace App\Livewire\Concerns;

trait EnsuresUserIsAdmin
{
    public function bootEnsuresUserIsAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
}
