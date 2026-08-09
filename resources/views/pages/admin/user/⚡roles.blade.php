<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('components.layouts.admin')] class extends Component
{
    public User $user;

    public string $selectedRole = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.users.roles_title', ['mobile' => $this->user->mobile]));
    }

    #[Computed]
    public function availableRoles()
    {
        return Role::query()->orderBy('name')->get();
    }

    #[Computed]
    public function assignedRoles()
    {
        return $this->user->roles()->orderBy('name')->get();
    }

    public function addRole(): void
    {
        $this->validate([
            'selectedRole' => 'required|exists:roles,name',
        ]);

        if (! $this->user->hasRole($this->selectedRole)) {
            $this->user->assignRole($this->selectedRole);
            $this->dispatch('notify', message: __('app.admin.users.role_added'), type: 'success');
        }

        $this->selectedRole = '';
        unset($this->assignedRoles);
    }

    public function removeRole(string $roleName): void
    {
        $this->user->removeRole($roleName);
        unset($this->assignedRoles);

        $this->dispatch('notify', message: __('app.admin.users.role_removed'), type: 'success');
    }
};
?>

<div class="space-y-6">
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-primary hover:underline" wire:navigate>
            {{ __('app.admin.users.back') }}
        </a>
        <h2 class="mt-2 text-2xl font-black text-fg-title">{{ __('app.admin.users.roles_heading') }}</h2>
        <p class="mt-1 text-fg-muted">
            {{ __('app.admin.users.roles_subtitle') }}
            <span class="font-semibold text-fg-title" dir="ltr">({{ $user->mobile }})</span>
        </p>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-ui.select
                    name="selectedRole"
                    :label="__('app.admin.users.role')"
                    wire:model="selectedRole"
                    class="w-full"
                >
                    <option value="">{{ __('app.admin.users.role_placeholder') }}</option>
                    @foreach ($this->availableRoles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </x-ui.select>
                @error('selectedRole')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <x-ui.button type="button" size="md" class="justify-center" wire:click="addRole">
                <span aria-hidden="true" class="iconify icon-[hugeicons--add-01] size-4"></span>
                {{ __('app.admin.users.add_role') }}
            </x-ui.button>
        </div>
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.role') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->assignedRoles as $role)
                    <x-ui.table.row wire:key="role-{{ $role->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title">
                            {{ $role->name }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="ghost"
                                intent="danger"
                                title="{{ __('app.admin.users.remove_role') }}"
                                wire:click="removeRole('{{ $role->name }}')"
                                wire:confirm="{{ __('app.admin.users.confirm_remove_role') }}"
                            >
                                <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                                <span class="sr-only">{{ __('app.admin.users.remove_role') }}</span>
                            </x-ui.button>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.row>
                        <x-ui.table.cell
                            colspan="2"
                            white-space="normal"
                            align="center"
                            class="py-10 text-fg-muted"
                        >
                            {{ __('app.admin.users.roles_empty') }}
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
    </x-ui.card>
</div>
