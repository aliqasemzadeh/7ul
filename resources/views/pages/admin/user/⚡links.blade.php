<?php

use App\Models\User;
use App\Models\Link;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public function deleteLink(int $linkId): void
    {
        $link = $this->user->links()->findOrFail($linkId);
        $link->delete();

        $this->dispatch('notify', message: __('app.admin.links.deleted'), type: 'success');
        $this->dispatch('user-links-updated');
    }
};
?>

<div>
    <div class="space-y-4">
        @forelse ($user->links as $link)
            <x-ui.card class="p-4" wire:key="user-link-{{ $link->id }}">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm font-bold text-fg-title">{{ $link->short_code }}</span>
                            <x-ui.badge size="sm" variant="outline">{{ $link->type->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 truncate text-xs text-fg-muted" dir="ltr">{{ $link->destination }}</p>
                    </div>
                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="ghost"
                        intent="danger"
                        wire:click="deleteLink({{ $link->id }})"
                        wire:confirm="{{ __('app.admin.links.confirm_delete') }}"
                    >
                        <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                    </x-ui.button>
                </div>
            </x-ui.card>
        @empty
            <div class="py-10 text-center text-fg-muted">
                {{ __('app.admin.links.empty') }}
            </div>
        @endforelse
    </div>
</div>
