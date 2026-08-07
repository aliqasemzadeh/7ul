<?php

use App\Actions\Links\GetLinkStats;
use App\Models\Link;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.user')] class extends Component
{
    public string $shortCode;

    public function mount(string $shortCode): void
    {
        $this->shortCode = $shortCode;

        $link = Link::query()->where('short_code', $shortCode)->firstOrFail();

        if ($link->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function rendering($view): void
    {
        $view->title(__('app.shortener.stats_title', ['code' => $this->shortCode]));
    }

    #[Computed]
    public function stats(): array
    {
        $link = Auth::user()
            ->links()
            ->where('short_code', $this->shortCode)
            ->firstOrFail();

        return app(GetLinkStats::class)->handle($link);
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

@php($stats = $this->stats)

<div class="space-y-6">
    <div>
        <a href="{{ route('user.index') }}" class="text-sm font-medium text-primary hover:underline" wire:navigate>
            {{ __('app.panel.stats.back') }}
        </a>
        <h2 class="mt-2 text-2xl font-black text-fg-title">{{ __('app.shortener.stats_heading') }}</h2>
        <p class="mt-1 text-fg-muted" dir="ltr">{{ url('/'.$stats['link']->short_code) }}</p>
        <p class="mt-2 text-sm text-fg-muted">
            {{ __('app.shortener.total_visits') }}:
            <span class="font-semibold text-fg-title">{{ number_format($stats['totalVisits']) }}</span>
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_device') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['byDevice'] as $device => $count)
                    <li class="flex items-center justify-between gap-3">
                        <span>{{ $device ?: __('app.shortener.unknown') }}</span>
                        <span class="font-semibold">{{ number_format($count) }}</span>
                    </li>
                @empty
                    <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_browser') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['byBrowser'] as $browser => $count)
                    <li class="flex items-center justify-between gap-3">
                        <span>{{ $browser ?: __('app.shortener.unknown') }}</span>
                        <span class="font-semibold">{{ number_format($count) }}</span>
                    </li>
                @empty
                    <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_os') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['byOs'] as $os => $count)
                    <li class="flex items-center justify-between gap-3">
                        <span>{{ $os ?: __('app.shortener.unknown') }}</span>
                        <span class="font-semibold">{{ number_format($count) }}</span>
                    </li>
                @empty
                    <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                @endforelse
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-bg-subtle text-fg-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.visited_at') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.ip') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.device') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.browser') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.os') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['visits'] as $visit)
                        <tr class="border-b border-border last:border-0" wire:key="visit-{{ $visit->id }}">
                            <td class="px-4 py-3 whitespace-nowrap" dir="ltr">
                                {{ $this->formatJalali($visit->created_at) }}
                            </td>
                            <td class="px-4 py-3" dir="ltr">{{ $visit->ip_address ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->device_type ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->browser ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->os ?: __('app.shortener.unknown') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-fg-muted">
                                {{ __('app.shortener.no_visits') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
