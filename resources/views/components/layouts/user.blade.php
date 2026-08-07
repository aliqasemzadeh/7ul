@props([
    'title' => null,
])

@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName();

    $items = [
        [
            'id' => 'links',
            'href' => route('user.index'),
            'text' => __('app.panel.nav.links'),
            'icon' => 'icon-[hugeicons--link-04]',
            'isActive' => in_array($currentRoute, ['user.index', 'user.links.stats'], true),
        ],
        [
            'id' => 'create',
            'href' => route('user.create'),
            'text' => __('app.panel.nav.create'),
            'icon' => 'icon-[hugeicons--add-01]',
            'isActive' => $currentRoute === 'user.create',
        ],
        [
            'id' => 'api',
            'href' => route('user.api'),
            'text' => __('app.panel.nav.api'),
            'icon' => 'icon-[hugeicons--api]',
            'isActive' => $currentRoute === 'user.api',
        ],
    ];
@endphp

<x-layouts.base
    :title="$title"
    lang="fa"
    dir="rtl"
    class="bg-bg text-fg min-h-screen font-sans antialiased"
>
    <x-ui.sidebar-wrapper
        class="fixed inset-y-0 end-0 z-80 flex h-dvh w-11/12 max-w-64 flex-col justify-between overflow-hidden border-s border-bg-muted/70 bg-bg-surface px-4 py-3 transition-all translate-x-full ease-linear fx-open:translate-x-0 md:w-64 md:translate-x-0 md:transition-none"
    >
        <div class="min-h-max border-b border-border py-2">
            <a href="{{ route('home') }}" class="flex items-center gap-x-3 font-semibold text-fg-title" wire:navigate>
                <span
                    class="flex size-8 items-center justify-center rounded-ui bg-primary text-sm font-black text-white shadow-sm"
                >
                    {{ __('app.welcome.brand_short') }}
                </span>
                <span class="truncate">
                    Seven Up
                    <span class="text-primary">{{ __('app.welcome.brand_accent') }}</span>
                </span>
            </a>
        </div>

        <nav class="flex flex-1 flex-col pt-6">
            <span class="mb-2 text-sm uppercase text-fg-muted">{{ __('app.panel.nav.section') }}</span>
            <ul class="space-y-2 text-fg-muted">
                @foreach ($items as $item)
                    <x-atoms.sidebar-item
                        :text="$item['text']"
                        :href="$item['href']"
                        :icon="$item['icon']"
                        :is-active="$item['isActive']"
                        wire:key="nav-{{ $item['id'] }}"
                    />
                @endforeach
            </ul>
        </nav>

        <div class="space-y-3 border-t border-border pt-3">
            <div class="rounded-ui border border-border-strong/40 p-2.5">
                <p class="text-sm font-semibold text-fg-title" dir="ltr">{{ $user?->mobile }}</p>
                <p class="mt-0.5 text-xs text-fg-muted">{{ __('app.panel.account') }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" size="sm" class="w-full justify-center">
                    {{ __('app.shortener.logout') }}
                </x-ui.button>
            </form>
        </div>
    </x-ui.sidebar-wrapper>

    <main class="min-h-screen md:pe-64">
        <header class="sticky top-0 z-35 flex h-16 w-full bg-bg/90 backdrop-blur">
            <div class="flex h-full w-full items-center justify-between px-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        data-toggle-sidebar
                        class="flex aspect-square items-center justify-center outline-none md:hidden"
                        aria-label="{{ __('app.panel.toggle_sidebar') }}"
                    >
                        <span aria-hidden="true" class="iconify icon-[hugeicons--menu-01] size-5"></span>
                    </button>
                    <h1 class="text-base font-semibold text-fg-title sm:text-lg">{{ $title }}</h1>
                </div>

                <div class="flex items-center gap-1">
                    <x-ui.theme-toggle />
                </div>
            </div>
        </header>

        <div class="px-4 pb-10 pt-2 sm:px-8">
            {{ $slot }}
        </div>
    </main>
</x-layouts.base>
