<?php

use App\Actions\Auth\SendEmailOtp;
use App\Actions\Auth\SendMobileOtp;
use App\Enums\LoginMethod;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $step = 'identify';

    public string $mobile = '';

    public string $email = '';

    public string $password = '';

    public string $otp = '';

    public string $statusMessage = '';

    public function mount(AuthSettings $authSettings): void
    {
        app()->setLocale('fa');

        if (Auth::check()) {
            $this->redirect(route('home'), navigate: true);
        }
    }

    #[Computed]
    public function loginMethod(): LoginMethod
    {
        return app(AuthSettings::class)->loginMethod();
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
        return match ($this->loginMethod) {
            LoginMethod::MobileOtp => [
                'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
                'otp' => ['required', 'string', 'size:6'],
            ],
            LoginMethod::EmailOtp => [
                'email' => ['required', 'email', 'max:255'],
                'otp' => ['required', 'string', 'size:6'],
            ],
            LoginMethod::EmailPassword => [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string'],
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => __('app.auth.mobile_required'),
            'mobile.regex' => __('app.auth.mobile_invalid'),
            'email.required' => __('app.auth.email_required'),
            'email.email' => __('app.auth.email_invalid'),
            'password.required' => __('app.auth.password_required'),
        ];
    }

    public function updatedMobile(string $value): void
    {
        $this->mobile = $this->normalizeIranianMobile($value);
    }

    public function sendCode(SendMobileOtp $sendMobileOtp, SendEmailOtp $sendEmailOtp): void
    {
        if ($this->loginMethod === LoginMethod::MobileOtp) {
            $this->sendMobileCode($sendMobileOtp);

            return;
        }

        if ($this->loginMethod === LoginMethod::EmailOtp) {
            $this->sendEmailCode($sendEmailOtp);

            return;
        }

        throw ValidationException::withMessages([
            'email' => __('app.auth.user_not_found'),
        ]);
    }

    public function resendCode(SendMobileOtp $sendMobileOtp, SendEmailOtp $sendEmailOtp): void
    {
        $this->sendCode($sendMobileOtp, $sendEmailOtp);
    }

    public function verify(): void
    {
        $this->validateOnly('otp');

        $user = match ($this->loginMethod) {
            LoginMethod::MobileOtp => User::query()->where('mobile', $this->mobile)->first(),
            LoginMethod::EmailOtp => User::query()->where('email', $this->email)->first(),
            default => null,
        };

        if (! $user) {
            throw ValidationException::withMessages([
                $this->loginMethod === LoginMethod::MobileOtp ? 'mobile' : 'email' => __('app.auth.user_not_found'),
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

    public function loginWithPassword(): void
    {
        if ($this->loginMethod !== LoginMethod::EmailPassword) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.user_not_found'),
            ]);
        }

        $this->validateOnly('email');
        $this->validateOnly('password');
        $this->ensureLoginIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], remember: true)) {
            RateLimiter::hit($this->loginRateLimitKey(), 60);

            throw ValidationException::withMessages([
                'email' => __('app.auth.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($this->loginRateLimitKey());
        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
    }

    public function backToIdentify(): void
    {
        $this->step = 'identify';
        $this->otp = '';
        $this->statusMessage = '';
        $this->resetErrorBag();
    }

    protected function sendMobileCode(SendMobileOtp $sendMobileOtp): void
    {
        $this->mobile = $this->normalizeIranianMobile($this->mobile);
        $this->validateOnly('mobile');
        $this->ensureOtpIsNotRateLimited($this->mobile);

        $user = User::query()->firstOrCreate(
            ['mobile' => $this->mobile],
            ['registration_ip' => request()->ip()],
        );

        $sendMobileOtp->handle($user);
        $this->hitOtpRateLimiters($this->mobile);

        $this->otp = '';
        $this->step = 'otp';
        $this->statusMessage = __('app.auth.otp_sent');
        $this->resetErrorBag('otp');
    }

    protected function sendEmailCode(SendEmailOtp $sendEmailOtp): void
    {
        $this->email = strtolower(trim($this->email));
        $this->validateOnly('email');
        $this->ensureOtpIsNotRateLimited($this->email);

        $user = User::query()->firstOrCreate(
            ['email' => $this->email],
            ['registration_ip' => request()->ip()],
        );

        $sendEmailOtp->handle($user);
        $this->hitOtpRateLimiters($this->email);

        $this->otp = '';
        $this->step = 'otp';
        $this->statusMessage = __('app.auth.otp_sent_email');
        $this->resetErrorBag('otp');
    }

    protected function ensureOtpIsNotRateLimited(string $identifier): void
    {
        $identifierKey = $this->otpIdentifierRateLimitKey($identifier);
        $ipKey = $this->otpIpRateLimitKey();

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            throw ValidationException::withMessages([
                $this->loginMethod === LoginMethod::MobileOtp ? 'mobile' : 'email' => __('app.auth.ip_throttle'),
            ]);
        }

        if (RateLimiter::tooManyAttempts($identifierKey, 1)) {
            throw ValidationException::withMessages([
                $this->loginMethod === LoginMethod::MobileOtp ? 'mobile' : 'email' => __('app.auth.throttle', [
                    'seconds' => RateLimiter::availableIn($identifierKey),
                ]),
            ]);
        }
    }

    protected function hitOtpRateLimiters(string $identifier): void
    {
        RateLimiter::hit($this->otpIdentifierRateLimitKey($identifier), 60);
        RateLimiter::hit($this->otpIpRateLimitKey(), 3600);
    }

    protected function ensureLoginIsNotRateLimited(): void
    {
        $key = $this->loginRateLimitKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.throttle', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }
    }

    protected function otpIdentifierRateLimitKey(string $identifier): string
    {
        return 'otp-send:'.$this->loginMethod->value.':'.$identifier;
    }

    protected function otpIpRateLimitKey(): string
    {
        return 'otp-send:ip:'.request()->ip();
    }

    protected function loginRateLimitKey(): string
    {
        return 'login:'.$this->email.'|'.request()->ip();
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

@php
    $method = $this->loginMethod;
    $destination = $method === \App\Enums\LoginMethod::MobileOtp ? $mobile : $email;
@endphp

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
        <x-site.brand
            :href="route('home')"
            :accent="__('app.welcome.brand_accent')"
            wire:navigate
        />

        <div class="flex items-center gap-3 sm:gap-4">
            <x-ui.theme-toggle />
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-5 pb-16 pt-8 sm:px-8">
        <div class="animate-[modal-animation-in_0.5s_ease-out]">
            <p class="mb-3 text-sm font-semibold tracking-wide text-primary">
                {{ $siteSettings->site_name ?: __('app.welcome.brand') }}
            </p>

            @if ($method === \App\Enums\LoginMethod::EmailPassword)
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.login_heading_password') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.auth.login_subtitle_password') }}
                </p>

                <form wire:submit="loginWithPassword" class="mt-8 space-y-5">
                    <div>
                        <x-ui.input
                            type="email"
                            name="email"
                            autocomplete="email"
                            dir="ltr"
                            :label="__('app.auth.email')"
                            :placeholder="__('app.auth.email_placeholder')"
                            wire:model.blur="email"
                            :invalid="$errors->has('email')"
                            class="w-full"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.input
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            dir="ltr"
                            :label="__('app.auth.password')"
                            wire:model="password"
                            :invalid="$errors->has('password')"
                            class="w-full"
                        />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end text-sm">
                        <a href="{{ route('password.request') }}" class="font-semibold text-primary transition hover:opacity-80" wire:navigate>
                            {{ __('app.auth.forgot_password') }}
                        </a>
                    </div>

                    <x-ui.button type="submit" size="lg" class="w-full justify-center">
                        <span wire:loading.remove wire:target="loginWithPassword">{{ __('app.auth.login') }}</span>
                        <span wire:loading wire:target="loginWithPassword">...</span>
                    </x-ui.button>

                    <p class="text-center text-sm text-fg-muted">
                        {{ __('app.auth.no_account') }}
                        <a href="{{ route('register') }}" class="font-semibold text-primary transition hover:opacity-80" wire:navigate>
                            {{ __('app.auth.register_link') }}
                        </a>
                    </p>
                </form>
            @elseif ($step === 'identify')
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.login_heading') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ $method === \App\Enums\LoginMethod::MobileOtp
                        ? __('app.auth.login_subtitle')
                        : __('app.auth.login_subtitle_email_otp') }}
                </p>

                <form wire:submit="sendCode" class="mt-8 space-y-5">
                    @if ($method === \App\Enums\LoginMethod::MobileOtp)
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
                    @else
                        <div>
                            <x-ui.input
                                type="email"
                                name="email"
                                autocomplete="email"
                                dir="ltr"
                                :label="__('app.auth.email')"
                                :placeholder="__('app.auth.email_placeholder')"
                                wire:model.blur="email"
                                :invalid="$errors->has('email')"
                                class="w-full"
                            />
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

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
                    {{ __('app.auth.otp_subtitle', ['destination' => $destination]) }}
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
                        <button type="button" wire:click="backToIdentify" class="text-fg-muted transition hover:text-primary">
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
