<?php

use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(AuthSettings $authSettings): void
    {
        app()->setLocale('fa');

        if (Auth::check()) {
            $this->redirect(route('home'), navigate: true);

            return;
        }

        if (! $authSettings->allowsRegistration()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function rendering($view): void
    {
        $view->title(__('app.auth.register_title'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
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
            'email.unique' => __('app.auth.email_taken'),
            'password.required' => __('app.auth.password_required'),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('app.auth.password')]),
            'password.min' => __('app.auth.password_min'),
        ];
    }

    public function register(AuthSettings $authSettings): void
    {
        if (! $authSettings->allowsRegistration()) {
            throw ValidationException::withMessages([
                'email' => __('app.auth.user_not_found'),
            ]);
        }

        $validated = $this->validate();

        $user = User::query()->create([
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'registration_ip' => request()->ip(),
        ]);

        Auth::login($user, remember: true);
        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
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

            <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                {{ __('app.auth.register_heading') }}
            </h1>
            <p class="mt-3 text-fg-muted">
                {{ __('app.auth.register_subtitle') }}
            </p>

            <form wire:submit="register" class="mt-8 space-y-5">
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
                        autocomplete="new-password"
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
                    <span wire:loading.remove wire:target="register">{{ __('app.auth.register') }}</span>
                    <span wire:loading wire:target="register">...</span>
                </x-ui.button>

                <p class="text-center text-sm text-fg-muted">
                    {{ __('app.auth.have_account') }}
                    <a href="{{ route('login') }}" class="font-semibold text-primary transition hover:opacity-80" wire:navigate>
                        {{ __('app.auth.login_link') }}
                    </a>
                </p>
            </form>
        </div>
    </main>
</div>
