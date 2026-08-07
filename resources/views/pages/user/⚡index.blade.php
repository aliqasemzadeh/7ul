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

    public function mount(): void
    {
        app()->setLocale('fa');
    }

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
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-bg-subtle text-fg-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.links.short_link') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.type') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.links.destination') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.total_visits') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.links.created_at') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.links.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->links as $link)
                        <tr class="border-b border-border last:border-0" wire:key="link-{{ $link->id }}">
                            <td class="px-4 py-3 font-semibold text-fg-title" dir="ltr">
                                {{ url('/'.$link->short_code) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $link->type->label() }}</td>
                            <td class="max-w-56 truncate px-4 py-3 text-fg-muted" title="{{ $link->destination }}">
                                {{ $link->destination }}
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($link->visits_count) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap" dir="ltr">
                                {{ $this->formatJalali($link->created_at) }}
                            </td>
                            <td class="px-4 py-3">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-fg-muted">
                                {{ __('app.panel.links.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->links->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->links->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
