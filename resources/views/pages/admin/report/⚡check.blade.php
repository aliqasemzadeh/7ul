<?php

use App\Enums\ReportStatusEnum;
use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;

    public Report $report;

    public string $admin_note = '';

    public function mount(Report $report): void
    {
        $this->report = $report->load(['link.user', 'reviewer']);
        $this->admin_note = (string) ($report->admin_note ?? '');
    }

    public function rendering($view): void
    {
        $view->title(__('app.admin.reports.check_title'));
    }

    public function accept(): void
    {
        if ($this->report->status !== ReportStatusEnum::Pending) {
            $this->dispatch('notify', message: __('app.admin.reports.already_reviewed'), type: 'danger');

            return;
        }

        $this->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function (): void {
            $this->report->refresh();

            if ($this->report->status !== ReportStatusEnum::Pending) {
                return;
            }

            $link = $this->report->link;

            if ($link !== null && $link->deleted_at === null) {
                $link->delete();
            }

            $this->report->update([
                'status' => ReportStatusEnum::Accepted,
                'admin_note' => $this->admin_note !== '' ? $this->admin_note : null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        $this->dispatch('notify', message: __('app.admin.reports.accepted'), type: 'success');
        $this->redirect(route('admin.reports.index'), navigate: true);
    }

    public function reject(): void
    {
        if ($this->report->status !== ReportStatusEnum::Pending) {
            $this->dispatch('notify', message: __('app.admin.reports.already_reviewed'), type: 'danger');

            return;
        }

        $this->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->report->update([
            'status' => ReportStatusEnum::Rejected,
            'admin_note' => $this->admin_note !== '' ? $this->admin_note : null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatch('notify', message: __('app.admin.reports.rejected'), type: 'success');
        $this->redirect(route('admin.reports.index'), navigate: true);
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
            <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.reports.check_heading') }}</h2>
            <p class="mt-1 text-fg-muted">{{ __('app.admin.reports.check_subtitle') }}</p>
        </div>

        <x-ui.button
            type="button"
            size="md"
            variant="outline"
            :href="route('admin.reports.index')"
            wire:navigate
            class="justify-center"
        >
            {{ __('app.admin.reports.back') }}
        </x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.reports.report_details') }}</h3>

            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="text-fg-muted">{{ __('app.admin.reports.tracking_code') }}</dt>
                    <dd class="mt-1 font-semibold text-fg-title" dir="ltr">{{ $report->tracking_code }}</dd>
                </div>
                <div>
                    <dt class="text-fg-muted">{{ __('app.admin.reports.status') }}</dt>
                    <dd class="mt-1 font-semibold text-fg-title">{{ $report->status->label() }}</dd>
                </div>
                <div>
                    <dt class="text-fg-muted">{{ __('app.admin.reports.created_at') }}</dt>
                    <dd class="mt-1 font-semibold text-fg-title" dir="ltr">{{ $this->formatJalali($report->created_at) }}</dd>
                </div>
                <div>
                    <dt class="text-fg-muted">{{ __('app.admin.reports.reason') }}</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-fg-title">{{ $report->reason }}</dd>
                </div>
                @if ($report->reporter_ip)
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.reports.reporter_ip') }}</dt>
                        <dd class="mt-1 font-semibold text-fg-title" dir="ltr">{{ $report->reporter_ip }}</dd>
                    </div>
                @endif
                @if ($report->reviewed_at)
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.reports.reviewed_at') }}</dt>
                        <dd class="mt-1 font-semibold text-fg-title" dir="ltr">{{ $this->formatJalali($report->reviewed_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.reports.reviewer') }}</dt>
                        <dd class="mt-1 font-semibold text-fg-title" dir="ltr">
                            {{ $report->reviewer?->mobile ?: __('app.shortener.unknown') }}
                        </dd>
                    </div>
                    @if ($report->admin_note)
                        <div>
                            <dt class="text-fg-muted">{{ __('app.admin.reports.admin_note') }}</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-fg-title">{{ $report->admin_note }}</dd>
                        </div>
                    @endif
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.reports.link_details') }}</h3>

            @if ($report->link)
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.reports.short_link') }}</dt>
                        <dd class="mt-1 break-all font-semibold text-fg-title" dir="ltr">
                            {{ url('/'.$report->link->short_code) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.links.type') }}</dt>
                        <dd class="mt-1 font-semibold text-fg-title">{{ $report->link->type->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.reports.destination') }}</dt>
                        <dd class="mt-1 break-all font-semibold text-fg-title" dir="ltr">{{ $report->link->destination }}</dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">{{ __('app.admin.links.owner') }}</dt>
                        <dd class="mt-1 font-semibold text-fg-title" dir="ltr">
                            {{ $report->link->user?->mobile ?: __('app.shortener.unknown') }}
                        </dd>
                    </div>
                    @if ($report->link->trashed())
                        <div>
                            <p class="font-semibold text-red-600 dark:text-red-400">
                                {{ __('app.admin.reports.link_deleted') }}
                            </p>
                        </div>
                    @endif
                </dl>
            @else
                <p class="mt-4 text-fg-muted">{{ __('app.admin.reports.link_missing') }}</p>
            @endif
        </x-ui.card>
    </div>

    @if ($report->status === ReportStatusEnum::Pending)
        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.reports.review_actions') }}</h3>

            <div class="mt-4 space-y-4">
                <div>
                    <x-ui.textarea
                        name="admin_note"
                        :label="__('app.admin.reports.admin_note')"
                        :placeholder="__('app.admin.reports.admin_note_placeholder')"
                        wire:model="admin_note"
                        :invalid="$errors->has('admin_note')"
                        class="w-full min-h-24"
                    />
                    @error('admin_note')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.button
                        type="button"
                        size="md"
                        class="w-full justify-center"
                        wire:click="accept"
                        wire:confirm="{{ __('app.admin.reports.confirm_accept') }}"
                    >
                        {{ __('app.admin.reports.accept') }}
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        size="md"
                        variant="outline"
                        intent="danger"
                        class="w-full justify-center"
                        wire:click="reject"
                        wire:confirm="{{ __('app.admin.reports.confirm_reject') }}"
                    >
                        {{ __('app.admin.reports.reject') }}
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
