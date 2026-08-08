<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.user')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function rendering($view): void
    {
        $view->title(__('app.panel.links.title'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function links()
    {
        return Auth::user()
            ->links()
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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-black text-fg-title">{{ __('app.panel.links.heading') }}</h2>
            <p class="mt-1 text-fg-muted">{{ __('app.panel.links.subtitle') }}</p>
        </div>

        <x-ui.button href="{{ route('user.create') }}" size="md" class="justify-center" wire:navigate>
            {{ __('app.panel.nav.create') }}
        </x-ui.button>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <x-ui.input
            name="search"
            :label="__('app.panel.links.search')"
            :placeholder="__('app.panel.links.search_placeholder')"
            wire:model.live.debounce.300ms="search"
            class="w-full"
        />
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.panel.links.short_link') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.shortener.type') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.panel.links.destination') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.shortener.total_visits') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.panel.links.created_at') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.panel.links.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->links as $link)
                    <x-ui.table.row wire:key="link-{{ $link->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title" dir="ltr">
                            {{ url('/'.$link->short_code) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start">{{ $link->type->label() }}</x-ui.table.cell>
                        <x-ui.table.cell
                            class="!text-start max-w-56 truncate text-fg-muted"
                            title="{{ $link->destination }}"
                        >
                            {{ $link->destination }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold">{{ number_format($link->visits_count) }}</x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" dir="ltr">
                            {{ $this->formatJalali($link->created_at) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <div class="flex flex-wrap gap-2" x-data="{ copied: false }">
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    x-on:click="
                                        navigator.clipboard.writeText(@js(url('/'.$link->short_code)));
                                        copied = true;
                                        setTimeout(() => copied = false, 2000);
                                    "
                                >
                                    <span x-show="!copied">{{ __('app.shortener.copy') }}</span>
                                    <span x-cloak x-show="copied">{{ __('app.shortener.copied') }}</span>
                                </x-ui.button>

                                <x-ui.button
                                    :href="route('user.links.stats', $link->short_code)"
                                    size="sm"
                                    variant="outline"
                                    wire:navigate
                                >
                                    {{ __('app.shortener.view_stats') }}
                                </x-ui.button>
                            </div>
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
                            {{ __('app.panel.links.empty') }}
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
