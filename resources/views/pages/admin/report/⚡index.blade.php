<?php

use App\Enums\ReportStatusEnum;
use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Models\Report;
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

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.reports.title'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function reports()
    {
        return Report::query()
            ->with(['link.user'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('tracking_code', 'like', $term)
                        ->orWhere('reason', 'like', $term)
                        ->orWhereHas('link', function ($linkQuery) use ($term): void {
                            $linkQuery->withTrashed()->where(function ($innerLink) use ($term): void {
                                $innerLink->where('short_code', 'like', $term)
                                    ->orWhere('destination', 'like', $term);
                            });
                        });
                });
            })
            ->when($this->status !== '', function ($query): void {
                $query->where('status', $this->status);
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
    <div>
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.reports.heading') }}</h2>
        <p class="mt-1 text-fg-muted">{{ __('app.admin.reports.subtitle') }}</p>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input
                name="search"
                :label="__('app.admin.reports.search')"
                :placeholder="__('app.admin.reports.search_placeholder')"
                wire:model.live.debounce.300ms="search"
                class="w-full"
            />

            <x-ui.select
                name="status"
                :label="__('app.admin.reports.status')"
                wire:model.live="status"
                class="w-full"
            >
                <option value="">{{ __('app.admin.reports.status_all') }}</option>
                @foreach (ReportStatusEnum::cases() as $case)
                    <option value="{{ $case->value }}" wire:key="status-filter-{{ $case->value }}">
                        {{ $case->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.tracking_code') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.short_link') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.reason') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.status') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.created_at') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.reports.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->reports as $report)
                    <x-ui.table.row wire:key="admin-report-{{ $report->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title" dir="ltr">
                            {{ $report->tracking_code }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold" dir="ltr">
                            @if ($report->link)
                                {{ url('/'.$report->link->short_code) }}
                            @else
                                {{ __('app.shortener.unknown') }}
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell
                            class="!text-start max-w-56 truncate text-fg-muted"
                            title="{{ $report->reason }}"
                        >
                            {{ $report->reason }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold">
                            {{ $report->status->label() }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" dir="ltr">
                            {{ $this->formatJalali($report->created_at) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="outline"
                                :href="route('admin.reports.check', $report)"
                                wire:navigate
                                title="{{ __('app.admin.reports.check') }}"
                            >
                                <span aria-hidden="true" class="iconify icon-[hugeicons--checkmark-badge-01] size-4"></span>
                                <span class="sr-only">{{ __('app.admin.reports.check') }}</span>
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
                            {{ __('app.admin.reports.empty') }}
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>

        @if ($this->reports->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->reports->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
