<x-layouts.base
    :title="__('app.welcome.title')"
    lang="fa"
    dir="rtl"
    class="bg-bg text-fg min-h-screen font-sans antialiased"
>
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
            <a href="{{ url('/') }}" class="group flex items-center gap-3">
                <span
                    class="flex size-10 items-center justify-center rounded-ui bg-primary text-lg font-black text-white shadow-sm transition duration-300 ease-out group-hover:scale-105"
                >
                    {{ __('app.welcome.brand_short') }}
                </span>
                <span class="hidden text-xl font-black tracking-tight text-fg-title sm:inline">
                    Seven Up
                    <span class="text-primary">{{ __('app.welcome.brand_accent') }}</span>
                </span>
            </a>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3 sm:gap-4">
                    <x-ui.theme-toggle />
                    @auth
                        <x-ui.button :href="url('/dashboard')" size="sm">
                            {{ __('app.welcome.nav.dashboard') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="ghost" size="sm">
                            {{ __('app.welcome.nav.login') }}
                        </x-ui.button>
                    @endauth
                </nav>
            @else
                <nav class="flex items-center gap-3 sm:gap-4">
                    <x-ui.theme-toggle />
                </nav>
            @endif
        </header>

        <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-5 pb-16 pt-8 sm:px-8 lg:px-10 lg:pt-14">
            {{-- Hero: brand + headline + shortener only --}}
            <section class="mx-auto flex w-full max-w-3xl flex-col items-center text-center animate-[modal-animation-in_0.6s_ease-out]">
                <p class="mb-4 text-sm font-semibold tracking-wide text-primary">
                    {{ __('app.welcome.brand') }}
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
                    onsubmit="return false;"

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
                            class="w-full flex-1 px-3 py-3 text-base text-fg placeholder:text-fg-muted sm:px-2"
                            required
                        />
                        <x-ui.button
                            type="submit"
                            size="lg"
                            :radius="false"
                            class="w-full justify-center gap-2 rounded-ui sm:w-auto sm:min-w-36"
                        >
                            <span>{{ __('app.welcome.shorten') }}</span>
                            <span class="iconify icon-[hugeicons--link-04] size-5" aria-hidden="true"></span>
                        </x-ui.button>
                    </x-ui.input.group>
                </form>

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
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-ui bg-primary text-sm font-bold text-white">
                        {{ __('app.welcome.brand_short') }}
                    </span>
                    <span class="font-bold text-fg-title">{{ __('app.welcome.brand') }}</span>
                </div>
                <p class="text-sm text-fg-muted">
                    {{ __('app.welcome.footer.rights') }}
                    &copy; {{ now()->year }}
                </p>
            </div>

            <nav class="flex flex-wrap items-center justify-center gap-6 text-sm text-fg-muted">
                <a href="#" class="transition hover:text-primary">{{ __('app.welcome.footer.about') }}</a>
                <a href="#" class="transition hover:text-primary">{{ __('app.welcome.footer.contact') }}</a>
                <a href="#" class="transition hover:text-primary">{{ __('app.welcome.footer.api') }}</a>
                <a href="#" class="transition hover:text-primary">{{ __('app.welcome.footer.blog') }}</a>
            </nav>
        </footer>
    </div>
</x-layouts.base>
