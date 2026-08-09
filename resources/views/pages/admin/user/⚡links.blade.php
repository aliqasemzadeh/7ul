<?php

use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;
    use WithPagination;

    public User $user;

    #[Url]
    public string $search = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.users.links_title', ['mobile' => $this->user->mobile]));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function links()
    {
        return $this->user->links()
            ->withCount('visits')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('short_code', 'like', $term)
                        ->orWhere('destination', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function deleteLink(int $linkId): void
    {
        $link = $this->user->links()->findOrFail($linkId);
        $link->delete();

        unset($this->links);

        $this->dispatch('notify', message: __('app.admin.links.deleted'), type: 'success');
    }

    public function formatJalali(?\Illuminate\Support\Carbon $date): string
    {
        if ($date === null) {
            return __('app.shortener.unknown');
        }

        return Jalalian::fromCarbon($date)->format('Y/m/d H:i');
    }
};
?>

<div class="space-y-6">
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-primary hover:underline" wire:navigate>
            {{ __('app.admin.users.back') }}
        </a>
        <h2 class="mt-2 text-2xl font-black text-fg-title">{{ __('app.admin.users.links_heading') }}</h2>
        <p class="mt-1 text-fg-muted">
            {{ __('app.admin.users.links_subtitle') }}
            <span class="font-semibold text-fg-title" dir="ltr">({{ $user->mobile }})</span>
        </p>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <x-ui.input
            name="search"
            :label="__('app.admin.links.search')"
            :placeholder="__('app.admin.links.search_placeholder')"
            wire:model.live.debounce.300ms="search"
            class="w-full"
        />
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.short_link') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.type') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.destination') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.visits') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.created_at') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->links as $link)
                    <x-ui.table.row wire:key="user-link-{{ $link->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title" dir="ltr">
                            {{ url('/'.$link->short_code) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start">
                            {{ $link->type->label() }}
                        </x-ui.table.cell>
                        <x-ui.table.cell
                            class="!text-start max-w-56 truncate text-fg-muted"
                            title="{{ $link->destination }}"
                        >
                            {{ $link->destination }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold">
                            {{ number_format($link->visits_count) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" dir="ltr">
                            {{ $this->formatJalali($link->created_at) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="ghost"
                                intent="danger"
                                title="{{ __('app.admin.links.delete') }}"
                                wire:click="deleteLink({{ $link->id }})"
                                wire:confirm="{{ __('app.admin.links.confirm_delete') }}"
                            >
                                <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                                <span class="sr-only">{{ __('app.admin.links.delete') }}</span>
                            </x-ui.button>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.row>
                        <x-ui.table.cell
                            colspan="6"
                            white-space="normal"
                            align="center"
                            class="py-10 text-fg-muted"
                        >
                            {{ __('app.admin.links.empty') }}
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>

        @if ($this->links->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->links->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
