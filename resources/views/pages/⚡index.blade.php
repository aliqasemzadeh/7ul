<?php

use App\Actions\Links\CreateShortLink;
use App\Actions\Links\GenerateLinkQrCode;
use App\Enums\LinkTypeEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $url = '';

    public ?string $shortLink = null;

    public ?string $shortCode = null;

    public ?string $qrCodeDataUri = null;

    public function mount(): void
    {
        app()->setLocale('fa');
    }

    public function rendering($view): void
    {
        $view->title(__('app.welcome.title'));
    }

    public function shorten(CreateShortLink $createShortLink, GenerateLinkQrCode $generateLinkQrCode): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $validated = $this->validate([
            'url' => ['required', 'url', 'max:2048'],
        ], [
            'url.required' => __('app.welcome.url_required'),
            'url.url' => __('app.welcome.url_invalid'),
        ]);

        $link = $createShortLink->handle(
            user: Auth::user(),
            destination: $validated['url'],
            type: LinkTypeEnum::Link,
            isPublicStats: true,
            creatorIp: request()->ip(),
        );

        $this->shortCode = $link->short_code;
        $this->shortLink = url('/'.$link->short_code);
        $this->qrCodeDataUri = $generateLinkQrCode->handle($this->shortLink);
    }
};
?>

<div class="relative flex min-h-screen flex-col overflow-hidden">
    {{-- Atmospheric background --}}
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
            :accent="__('app.welcome.brand_accent')"
        />

        @if (Route::has('login'))
            <nav class="flex items-center gap-2 sm:gap-3">
                <x-ui.theme-toggle />
                @auth
                    <x-ui.button :href="route('user.index')" size="sm" wire:navigate>
                        {{ __('app.welcome.nav.dashboard') }}
                    </x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="ghost" size="sm">
                        {{ __('app.welcome.nav.login') }}
                    </x-ui.button>
                @endauth
            </nav>
        @else
            <nav class="flex items-center gap-2 sm:gap-3">
                <x-ui.theme-toggle />
            </nav>
        @endif
    </header>

    <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-5 pb-16 pt-8 sm:px-8 lg:px-10 lg:pt-14">
        {{-- Hero: brand + headline + shortener only --}}
        <section class="mx-auto flex w-full max-w-3xl flex-col items-center text-center animate-[modal-animation-in_0.6s_ease-out]">
            <p class="mb-4 text-sm font-semibold tracking-wide text-primary">
                {{ $siteSettings->site_name ?: __('app.welcome.brand') }}
            </p>

            <h1 class="text-balance text-4xl font-black leading-tight text-fg-title sm:text-5xl lg:text-6xl">
                {!! __('app.welcome.tagline', [
                    'highlight' => '<span class="text-primary">' . e(__('app.welcome.tagline_highlight')) . '</span>',
                ]) !!}
            </h1>

            <p class="mt-5 max-w-2xl text-pretty text-base leading-relaxed text-fg-muted sm:text-lg">
                {{ __('app.welcome.subtitle') }}
            </p>

            <form
                class="mt-10 w-full"
                wire:submit="shorten"
                aria-label="{{ __('app.welcome.shorten') }}"
            >
                <x-ui.input.group
                    size="none"
                    class="h-auto flex-col gap-2 p-1.5 sm:flex-row sm:items-center sm:gap-0 sm:ps-4"
                >
                    <x-ui.input
                        variant="unstyled"
                        type="url"
                        name="url"
                        :placeholder="__('app.welcome.url_placeholder')"
                        wire:model="url"
                        class="w-full flex-1 px-3 py-3 text-base text-fg placeholder:text-fg-muted sm:px-2"
                        :invalid="$errors->has('url')"
                    />
                    <x-ui.button
                        type="submit"
                        size="lg"
                        :radius="false"
                        class="w-full justify-center gap-2 rounded-ui sm:w-auto sm:min-w-36"
                    >
                        <span wire:loading.remove wire:target="shorten">{{ __('app.welcome.shorten') }}</span>
                        <span wire:loading wire:target="shorten">...</span>
                        <span class="iconify icon-[hugeicons--link-04] size-5" aria-hidden="true" wire:loading.remove wire:target="shorten"></span>
                    </x-ui.button>
                </x-ui.input.group>
                @error('url')
                    <p class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </form>

            @if ($shortLink)
                <div
                    class="mt-6 w-full rounded-ui border border-border bg-card p-4 text-start shadow-sm"
                    x-data="{ copied: false }"
                >
                    <p class="text-sm font-medium text-fg-muted">{{ __('app.welcome.your_link') }}</p>
                    <p class="mt-2 break-all font-semibold text-fg-title" dir="ltr" x-ref="shortLink">
                        {{ $shortLink }}
                    </p>

                    @if ($qrCodeDataUri)
                        <div class="mt-4 flex flex-col items-center gap-3 border-t border-border pt-4">
                            <p class="text-sm font-medium text-fg-muted">{{ __('app.welcome.qr_label') }}</p>
                            <img
                                src="{{ $qrCodeDataUri }}"
                                alt="{{ __('app.welcome.qr_label') }}"
                                class="size-48 rounded-ui bg-white p-2"
                                width="192"
                                height="192"
                            />
                            <x-ui.button
                                :href="$qrCodeDataUri"
                                download="7ul-{{ $shortCode }}.svg"
                                variant="outline"
                                size="md"
                                class="w-full justify-center sm:w-auto"
                                :in-same-window="true"
                            >
                                {{ __('app.welcome.download_qr') }}
                            </x-ui.button>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <x-ui.button
                            type="button"
                            size="md"
                            class="w-full justify-center sm:flex-1"
                            x-on:click="
                                navigator.clipboard.writeText($refs.shortLink.innerText.trim());
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                        >
                            <span x-show="!copied">{{ __('app.welcome.copy') }}</span>
                            <span x-cloak x-show="copied">{{ __('app.welcome.copied') }}</span>
                        </x-ui.button>
                        <x-ui.button
                            :href="route('links.stats', $shortCode)"
                            variant="outline"
                            size="md"
                            class="w-full justify-center sm:flex-1"
                        >
                            {{ __('app.welcome.view_stats') }}
                        </x-ui.button>
                    </div>
                </div>
            @endif

            <p class="mt-4 text-xs text-fg-muted">
                {!! __('app.welcome.terms', [
                    'terms' => '<a href="#" class="underline decoration-border-strong underline-offset-2 transition hover:text-primary">' . e(__('app.welcome.terms_link')) . '</a>',
                ]) !!}
            </p>
        </section>

        {{-- Features below the fold --}}
        <section class="mx-auto mt-20 w-full max-w-6xl sm:mt-28" aria-labelledby="welcome-features-heading">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <h2 id="welcome-features-heading" class="text-2xl font-bold text-fg-title sm:text-3xl">
                    {{ __('app.welcome.features.heading') }}
                </h2>
                <p class="mt-3 text-fg-muted">
                    {{ __('app.welcome.features.subheading') }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @php
                    $features = [
                        [
                            'icon' => 'icon-[hugeicons--analytics-01]',
                            'soft' => 'ui-soft-primary',
                            'title' => __('app.welcome.features.analytics.title'),
                            'description' => __('app.welcome.features.analytics.description'),
                        ],
                        [
                            'icon' => 'icon-[hugeicons--flash]',
                            'soft' => 'ui-soft-success',
                            'title' => __('app.welcome.features.speed.title'),
                            'description' => __('app.welcome.features.speed.description'),
                        ],
                        [
                            'icon' => 'icon-[hugeicons--security-lock]',
                            'soft' => 'ui-soft-warning',
                            'title' => __('app.welcome.features.security.title'),
                            'description' => __('app.welcome.features.security.description'),
                        ],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <article
                        wire:key="feature-{{ $feature['icon'] }}"
                        class="group ui-card bg-card ring-1 ring-border-card [--card-padding:--spacing(8)] [--card-radius:1.25rem] transition duration-300 ease-out hover:-translate-y-0.5 hover:ring-primary/30"
                    >
                        <div
                            class="mb-5 flex size-12 items-center justify-center rounded-ui {{ $feature['soft'] }} transition duration-300 ease-out group-hover:scale-110"
                        >
                            <span class="iconify {{ $feature['icon'] }} size-6" aria-hidden="true"></span>
                        </div>
                        <h3 class="text-lg font-bold text-fg-title">{{ $feature['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-fg-muted">{{ $feature['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="mx-auto mt-auto flex w-full max-w-7xl flex-col items-center justify-between gap-6 border-t border-border px-5 py-10 sm:flex-row sm:px-8 lg:px-10">
        <div class="flex flex-col items-center gap-2 sm:items-start">
            <x-site.brand size="sm" :show-text="true" :force-text-visible="true" />
            <p class="text-sm text-fg-muted">
                {{ __('app.welcome.footer.rights') }}
                &copy; {{ now()->year }}
            </p>
            <a
                href="{{ route('report') }}"
                wire:navigate
                class="text-sm font-semibold text-fg-muted transition hover:text-fg-title"
            >
                {{ __('app.welcome.footer.report') }}
            </a>
        </div>

        <x-site.contact-social />
    </footer>
</div>
