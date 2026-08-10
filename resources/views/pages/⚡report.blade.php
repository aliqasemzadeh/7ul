<?php

use App\Actions\Links\ResolveShortCodeFromInput;
use App\Enums\ReportStatusEnum;
use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $linkInput = '';

    public string $reason = '';

    public string $trackingCode = '';

    public ?string $issuedTrackingCode = null;

    public ?string $foundShortCode = null;

    public ?string $foundDestination = null;

    public ?string $trackedStatus = null;

    public ?string $trackedReason = null;

    public ?string $trackedAt = null;

    public string $trackError = '';

    public function mount(): void
    {
        app()->setLocale('fa');
    }

    public function rendering($view): void
    {
        $view->title(__('app.report.title'));
    }

    public function submitReport(ResolveShortCodeFromInput $resolveShortCodeFromInput): void
    {
        $this->issuedTrackingCode = null;
        $this->foundShortCode = null;
        $this->foundDestination = null;

        $this->validate([
            'linkInput' => ['required', 'string', 'max:2048'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'linkInput.required' => __('app.report.link_required'),
            'reason.required' => __('app.report.reason_required'),
            'reason.min' => __('app.report.reason_min'),
        ]);

        $link = $resolveShortCodeFromInput->findLink($this->linkInput);

        if ($link === null) {
            $this->addError('linkInput', __('app.report.link_not_found'));

            return;
        }

        $report = Report::query()->create([
            'link_id' => $link->id,
            'tracking_code' => Report::generateTrackingCode(),
            'reason' => $this->reason,
            'status' => ReportStatusEnum::Pending,
            'reporter_ip' => request()->ip(),
        ]);

        $this->issuedTrackingCode = $report->tracking_code;
        $this->foundShortCode = $link->short_code;
        $this->foundDestination = $link->destination;
        $this->reason = '';
        $this->linkInput = '';
    }

    public function track(): void
    {
        $this->trackedStatus = null;
        $this->trackedReason = null;
        $this->trackedAt = null;
        $this->trackError = '';

        $this->validate([
            'trackingCode' => ['required', 'string', 'size:10'],
        ], [
            'trackingCode.required' => __('app.report.tracking_required'),
            'trackingCode.size' => __('app.report.tracking_invalid'),
        ]);

        $code = strtoupper(trim($this->trackingCode));

        $report = Report::query()
            ->where('tracking_code', $code)
            ->first();

        if ($report === null) {
            $this->trackError = __('app.report.tracking_not_found');

            return;
        }

        $this->trackedStatus = $report->status->label();
        $this->trackedReason = $report->reason;
        $this->trackedAt = Jalalian::fromCarbon($report->created_at)->format('Y/m/d H:i');
    }
};
?>

<div class="relative flex min-h-screen flex-col overflow-hidden">
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 ui-radial-gradient text-primary/25 [--unify-radial-bg:var(--color-bg)]"
    ></div>

    <header class="mx-auto flex w-full max-w-3xl items-center justify-between gap-4 px-5 py-5 sm:px-8">
        <x-site.brand
            text="Seven Up"
            :accent="__('app.welcome.brand_accent')"
            :href="route('home')"
            wire:navigate
        />

        <a
            href="{{ route('home') }}"
            wire:navigate
            class="text-sm font-semibold text-fg-muted transition hover:text-fg-title"
        >
            {{ __('app.report.back_home') }}
        </a>
    </header>

    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-8 px-5 pb-16 sm:px-8">
        <div>
            <h1 class="text-3xl font-black text-fg-title">{{ __('app.report.heading') }}</h1>
            <p class="mt-2 text-fg-muted">{{ __('app.report.subtitle') }}</p>
        </div>

        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h2 class="text-xl font-bold text-fg-title">{{ __('app.report.submit_heading') }}</h2>
            <p class="mt-1 text-sm text-fg-muted">{{ __('app.report.submit_help') }}</p>

            <form wire:submit="submitReport" class="mt-6 space-y-4">
                <div>
                    <x-ui.input
                        name="linkInput"
                        :label="__('app.report.link_label')"
                        :placeholder="__('app.report.link_placeholder')"
                        wire:model="linkInput"
                        :invalid="$errors->has('linkInput')"
                        class="w-full"
                        dir="ltr"
                    />
                    @error('linkInput')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui.textarea
                        name="reason"
                        :label="__('app.report.reason_label')"
                        :placeholder="__('app.report.reason_placeholder')"
                        wire:model="reason"
                        :invalid="$errors->has('reason')"
                        class="w-full min-h-28"
                    />
                    @error('reason')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="submit" size="md" class="w-full justify-center">
                    <span wire:loading.remove wire:target="submitReport">
                        {{ __('app.report.submit') }}
                    </span>
                    <span wire:loading wire:target="submitReport">...</span>
                </x-ui.button>
            </form>

            @if ($issuedTrackingCode)
                <div class="mt-6 rounded-ui border border-emerald-500/30 bg-emerald-500/10 p-4">
                    <p class="font-semibold text-fg-title">{{ __('app.report.submitted') }}</p>
                    <p class="mt-2 text-sm text-fg-muted">
                        {{ __('app.report.link_found', ['code' => $foundShortCode]) }}
                    </p>
                    <p class="mt-1 truncate text-sm text-fg-muted" dir="ltr" title="{{ $foundDestination }}">
                        {{ $foundDestination }}
                    </p>
                    <p class="mt-4 text-sm text-fg-muted">{{ __('app.report.tracking_save') }}</p>
                    <p class="mt-1 text-2xl font-black tracking-widest text-fg-title" dir="ltr">
                        {{ $issuedTrackingCode }}
                    </p>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card class="p-(--card-padding)" :shadow="true">
            <h2 class="text-xl font-bold text-fg-title">{{ __('app.report.track_heading') }}</h2>
            <p class="mt-1 text-sm text-fg-muted">{{ __('app.report.track_help') }}</p>

            <form wire:submit="track" class="mt-6 space-y-4">
                <div>
                    <x-ui.input
                        name="trackingCode"
                        :label="__('app.report.tracking_label')"
                        :placeholder="__('app.report.tracking_placeholder')"
                        wire:model="trackingCode"
                        :invalid="$errors->has('trackingCode')"
                        class="w-full"
                        dir="ltr"
                    />
                    @error('trackingCode')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @if ($trackError !== '')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $trackError }}</p>
                    @endif
                </div>

                <x-ui.button type="submit" size="md" variant="outline" class="w-full justify-center">
                    <span wire:loading.remove wire:target="track">
                        {{ __('app.report.track') }}
                    </span>
                    <span wire:loading wire:target="track">...</span>
                </x-ui.button>
            </form>

            @if ($trackedStatus)
                <div class="mt-6 rounded-ui border border-border bg-bg-subtle p-4">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-fg-muted">{{ __('app.report.status') }}</dt>
                            <dd class="mt-1 font-semibold text-fg-title">{{ $trackedStatus }}</dd>
                        </div>
                        <div>
                            <dt class="text-fg-muted">{{ __('app.report.submitted_at') }}</dt>
                            <dd class="mt-1 font-semibold text-fg-title" dir="ltr">{{ $trackedAt }}</dd>
                        </div>
                        <div>
                            <dt class="text-fg-muted">{{ __('app.report.reason_label') }}</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-fg-title">{{ $trackedReason }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </x-ui.card>
    </main>
</div>
