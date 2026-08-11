<?php

use App\Actions\Auth\SendEmailOtp;
use App\Enums\LoginMethod;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
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
    public string $step = 'email';

    public string $email = '';

    public string $otp = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $statusMessage = '';

    public function mount(AuthSettings $authSettings): void
    {
        app()->setLocale('fa');

        if (Auth::check()) {
            $this->redirect(route('home'), navigate: true);

            return;
        }

        if ($authSettings->loginMethod() !== LoginMethod::EmailPassword) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function rendering($view): void
    {
        $view->title(__('app.auth.forget_title'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('app.auth.email_required'),
            'email.email' => __('app.auth.email_invalid'),
            'password.required' => __('app.auth.password_required'),
            'password.min' => __('app.auth.password_min'),
        ];
    }

    public function sendCode(SendEmailOtp $sendEmailOtp): void
    {
        $this->email = strtolower(trim($this->email));
        $this->validateOnly('email');
        $this->ensureIsNotRateLimited();

        $user = User::query()->where('email', $this->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.user_not_found'),
            ]);
        }

        $sendEmailOtp->handle($user);
        $this->hitRateLimiters();

        $this->otp = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->step = 'reset';
        $this->statusMessage = __('app.auth.otp_sent_email');
        $this->resetErrorBag(['otp', 'password']);
    }

    public function resendCode(SendEmailOtp $sendEmailOtp): void
    {
        $this->sendCode($sendEmailOtp);
    }

    public function resetPassword(): void
    {
        $this->validateOnly('otp');
        $this->validateOnly('password');

        $user = User::query()->where('email', $this->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.user_not_found'),
            ]);
        }

        $result = $user->consumeOneTimePassword($this->otp);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'otp' => $this->otpErrorMessage($result),
            ]);
        }

        $user->forceFill([
            'password' => $this->password,
        ])->save();

        Auth::login($user, remember: true);
        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
    }

    public function backToEmail(): void
    {
        $this->step = 'email';
        $this->otp = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->statusMessage = '';
        $this->resetErrorBag();
    }

    protected function ensureIsNotRateLimited(): void
    {
        $emailKey = $this->emailRateLimitKey();
        $ipKey = $this->ipRateLimitKey();

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.ip_throttle'),
            ]);
        }

        if (RateLimiter::tooManyAttempts($emailKey, 1)) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.throttle', [
                    'seconds' => RateLimiter::availableIn($emailKey),
                ]),
            ]);
        }
    }

    protected function hitRateLimiters(): void
    {
        RateLimiter::hit($this->emailRateLimitKey(), 60);
        RateLimiter::hit($this->ipRateLimitKey(), 3600);
    }

    protected function emailRateLimitKey(): string
    {
        return 'password-reset-otp:email:'.$this->email;
    }

    protected function ipRateLimitKey(): string
    {
        return 'password-reset-otp:ip:'.request()->ip();
    }

    protected function otpErrorMessage(ConsumeOneTimePasswordResult $result): string
    {
        return match ($result) {
            ConsumeOneTimePasswordResult::OneTimePasswordExpired => __('one-time-passwords::validation.one_time_password_expired'),
            ConsumeOneTimePasswordResult::RateLimitExceeded => __('one-time-passwords::validation.rate_limit_exceeded'),
            default => __('one-time-passwords::validation.incorrect_one_time_password'),
        };
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

            @if ($step === 'email')
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.forget_heading') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.auth.forget_subtitle') }}
                </p>

                <form wire:submit="sendCode" class="mt-8 space-y-5">
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

                    <x-ui.button type="submit" size="lg" class="w-full justify-center">
                        <span wire:loading.remove wire:target="sendCode">{{ __('app.auth.send_code') }}</span>
                        <span wire:loading wire:target="sendCode">...</span>
                    </x-ui.button>

                    <p class="text-center text-sm text-fg-muted">
                        <a href="{{ route('login') }}" class="font-semibold text-primary transition hover:opacity-80" wire:navigate>
                            {{ __('app.auth.back') }}
                        </a>
                    </p>
                </form>
            @else
                <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                    {{ __('app.auth.reset_password') }}
                </h1>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.auth.forget_otp_subtitle', ['email' => $email]) }}
                </p>

                @if ($statusMessage)
                    <p class="mt-4 rounded-ui bg-primary/10 px-3 py-2 text-sm text-primary">
                        {{ $statusMessage }}
                    </p>
                @endif

                <form wire:submit="resetPassword" class="mt-8 space-y-5">
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

                    <div>
                        <x-ui.input
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            dir="ltr"
                            :label="__('app.auth.new_password')"
                            wire:model="password"
                            :invalid="$errors->has('password')"
                            class="w-full"
                        />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.input
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            dir="ltr"
                            :label="__('app.auth.password_confirmation')"
                            wire:model="password_confirmation"
                            class="w-full"
                        />
                    </div>

                    <x-ui.button type="submit" size="lg" class="w-full justify-center">
                        <span wire:loading.remove wire:target="resetPassword">{{ __('app.auth.reset_password') }}</span>
                        <span wire:loading wire:target="resetPassword">...</span>
                    </x-ui.button>

                    <div class="flex items-center justify-between gap-3 text-sm">
                        <button type="button" wire:click="backToEmail" class="text-fg-muted transition hover:text-primary">
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
