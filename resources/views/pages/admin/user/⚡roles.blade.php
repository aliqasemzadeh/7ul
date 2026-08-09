<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public User $user;

    public string $selectedRole = '';

    #[Computed]
    public function availableRoles()
    {
        return Role::all();
    }

    public function addRole(): void
    {
        $this->validate([
            'selectedRole' => 'required|exists:roles,name',
        ]);

        if (!$this->user->hasRole($this->selectedRole)) {
            $this->user->assignRole($this->selectedRole);
            $this->dispatch('notify', message: __('app.admin.users.role_added'), type: 'success');
        }

        $this->selectedRole = '';
    }

    public function removeRole(string $roleName): void
    {
        $this->user->removeRole($roleName);
        $this->dispatch('notify', message: __('app.admin.users.role_removed'), type: 'success');
    }
};
?>

<div class="space-y-6">
    <div class="flex items-end gap-2">
        <div class="flex-1">
            <x-ui.select
                name="selectedRole"
                :label="__('app.admin.users.role')"
                wire:model="selectedRole"
                class="w-full"
            >
                <option value="">{{ __('app.shortener.unknown') }}</option>
                @foreach ($this->availableRoles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <x-ui.button type="button" size="md" wire:click="addRole">
            <span aria-hidden="true" class="iconify icon-[hugeicons--add-01] size-4"></span>
        </x-ui.button>
    </div>

    <div class="space-y-2">
        @forelse ($user->roles as $role)
            <div class="flex items-center justify-between rounded-lg border border-border p-3" wire:key="role-{{ $role->id }}">
                <span class="font-medium text-fg-title">{{ $role->name }}</span>
                <x-ui.button
                    type="button"
                    size="xs"
                    variant="ghost"
                    intent="danger"
                    wire:click="removeRole('{{ $role->name }}')"
                >
                    <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                </x-ui.button>
            </div>
        @empty
            <p class="text-center text-sm text-fg-muted">{{ __('app.admin.users.empty') }}</p>
        @endforelse
    </div>
</div>
