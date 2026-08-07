<?php

use App\Actions\Auth\SendMobileOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $step = 'mobile';

    public string $mobile = '';

    public string $otp = '';

    public string $statusMessage = '';

    public function mount(): void
    {
        app()->setLocale('fa');

        if (Auth::check()) {
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function rendering($view): void
    {
        $view->title(__('app.auth.login_title'));
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => __('app.auth.mobile_required'),
            'mobile.regex' => __('app.auth.mobile_invalid'),
        ];
    }

    public function updatedMobile(string $value): void
    {
        $this->mobile = $this->normalizeIranianMobile($value);
    }

    public function sendCode(SendMobileOtp $sendMobileOtp): void
    {
        $this->mobile = $this->normalizeIranianMobile($this->mobile);
        $this->validateOnly('mobile');
        $this->ensureIsNotRateLimited();

        $user = User::query()->firstOrCreate(
            ['mobile' => $this->mobile],
            ['registration_ip' => request()->ip()],
        );

        $sendMobileOtp->handle($user);
        $this->hitRateLimiters();

        $this->otp = '';
        $this->step = 'otp';
        $this->statusMessage = __('app.auth.otp_sent');
        $this->resetErrorBag('otp');
    }

    public function resendCode(SendMobileOtp $sendMobileOtp): void
    {
        $this->sendCode($sendMobileOtp);
    }

    public function verify(): void
    {
        $this->validateOnly('otp');

        $user = User::query()->where('mobile', $this->mobile)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'mobile' => __('app.auth.user_not_found'),
            ]);
        }

        $result = $user->attemptLoginUsingOneTimePassword($this->otp, remember: true);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'otp' => $this->otpErrorMessage($result),
            ]);
        }

        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
    }

    public function backToMobile(): void
    {
        $this->step = 'mobile';
        $this->otp = '';
        $this->statusMessage = '';
        $this->resetErrorBag();
    }

    protected function ensureIsNotRateLimited(): void
    {
        $mobileKey = $this->mobileRateLimitKey();
        $ipKey = $this->ipRateLimitKey();

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            throw ValidationException::withMessages([
                'mobile' => __('app.auth.ip_throttle'),
            ]);
        }

        if (RateLimiter::tooManyAttempts($mobileKey, 1)) {
            throw ValidationException::withMessages([
                'mobile' => __('app.auth.throttle', [
                    'seconds' => RateLimiter::availableIn($mobileKey),
                ]),
            ]);
        }
    }

    protected function hitRateLimiters(): void
    {
        RateLimiter::hit($this->mobileRateLimitKey(), 60);
        RateLimiter::hit($this->ipRateLimitKey(), 3600);
    }

    protected function mobileRateLimitKey(): string
    {
        return 'otp-send:mobile:'.$this->mobile;
    }

    protected function ipRateLimitKey(): string
    {
        return 'otp-send:ip:'.request()->ip();
    }

    protected function otpErrorMessage(ConsumeOneTimePasswordResult $result): string
    {
        return match ($result) {
            ConsumeOneTimePasswordResult::OneTimePasswordExpired => __('one-time-passwords::validation.one_time_password_expired'),
            ConsumeOneTimePasswordResult::RateLimitExceeded => __('one-time-passwords::validation.rate_limit_exceeded'),
            default => __('one-time-passwords::validation.incorrect_one_time_password'),
        };
    }

    protected function normalizeIranianMobile(string $mobile): string
    {
        $mobile = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $mobile,
        );

        return preg_replace('/\D+/', '', $mobile) ?? '';
    }
};
?>

<div class="relative flex min-h-screen flex-col overflow-hidden">
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 ui-radial-gradient text-primary/25 [--unify-radial-bg:var(--color-bg)]"
    ></div>
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 opacity-[0.35] ui-grid-dotted text-primary/40 [--bg-grid-dotted:transparent] [--dotsize:1px] [--unify-ui-grid-width:28px] [--unify-ui-grid-height:28px]"
    ></div>

    <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-10">
        <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-ui bg-primary text-lg font-black text-white shadow-sm transition duration-300 ease-out group-hover:scale-105">
                {{ __('app.welcome.brand_short') }}
            </span>
            <span class="hidden text-xl font-black tracking-tight text-fg-title sm:inline">
                Seven Up
                <span class="text-primary">{{ __('app.welcome.brand_accent') }}</span>
            </span>
        </a>

        <div class="flex items-center gap-3 sm:gap-4">
            <x-ui.theme-toggle />
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-5 pb-16 pt-8 sm:px-8">
        <div class="animate-[modal-animation-in_0.5s_ease-out]">
            <p class="mb-3 text-sm font-semibold tracking-wide text-primary">
                {{ __('app.welcome.brand') }}
            </p>

            @if ($step === 'mobile')
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.login_heading') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.auth.login_subtitle') }}
                </p>

                <form wire:submit="sendCode" class="mt-8 space-y-5">
                    <div>
                        <x-ui.input
                            type="tel"
                            name="mobile"
                            inputmode="numeric"
                            autocomplete="tel"
                            maxlength="11"
                            pattern="09[0-9]{9}"
                            dir="ltr"
                            :label="__('app.auth.mobile')"
                            :placeholder="__('app.auth.mobile_placeholder')"
                            wire:model.blur="mobile"
                            :invalid="$errors->has('mobile')"
                            class="w-full"
                        />
                        @error('mobile')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" size="lg" class="w-full justify-center">
                        <span wire:loading.remove wire:target="sendCode">{{ __('app.auth.send_code') }}</span>
                        <span wire:loading wire:target="sendCode">...</span>
                    </x-ui.button>
                </form>
            @else
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.otp_heading') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.auth.otp_subtitle', ['mobile' => $mobile]) }}
                </p>

                @if ($statusMessage)
                    <p class="mt-4 rounded-ui bg-primary/10 px-3 py-2 text-sm text-primary">
                        {{ $statusMessage }}
                    </p>
                @endif

                <form wire:submit="verify" class="mt-8 space-y-5">
                    <div>
                        <x-ui.input
                            type="text"
                            name="otp"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            dir="ltr"
                            :label="__('app.auth.otp')"
                            :placeholder="__('app.auth.otp_placeholder')"
                            wire:model="otp"
                            :invalid="$errors->has('otp')"
                            class="w-full tracking-[0.4em]"
                        />
                        @error('otp')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" size="lg" class="w-full justify-center">
                        <span wire:loading.remove wire:target="verify">{{ __('app.auth.verify') }}</span>
                        <span wire:loading wire:target="verify">...</span>
                    </x-ui.button>

                    <div class="flex items-center justify-between gap-3 text-sm">
                        <button type="button" wire:click="backToMobile" class="text-fg-muted transition hover:text-primary">
                            {{ __('app.auth.back') }}
                        </button>
                        <button type="button" wire:click="resendCode" class="font-semibold text-primary transition hover:opacity-80">
                            {{ __('app.auth.resend') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </main>
</div>
