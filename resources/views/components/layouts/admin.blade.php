@props([
    'title' => null,
])

@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName();
    $mobile = $user?->mobile;
    $initials = $mobile
        ? mb_substr($mobile, -2)
        : __('app.welcome.brand_short');

    $items = [
        [
            'id' => 'users',
            'href' => route('admin.users.index'),
            'text' => __('app.admin.nav.users'),
            'icon' => 'icon-[hugeicons--user-multiple]',
            'isActive' => in_array($currentRoute, ['admin.users.index', 'admin.users.links', 'admin.users.roles'], true),
        ],
        [
            'id' => 'links',
            'href' => route('admin.links.index'),
            'text' => __('app.admin.nav.links'),
            'icon' => 'icon-[hugeicons--link-04]',
            'isActive' => $currentRoute === 'admin.links.index',
        ],
        [
            'id' => 'functions',
            'href' => route('admin.functions.index'),
            'text' => __('app.admin.nav.functions'),
            'icon' => 'icon-[hugeicons--function-of-x]',
            'isActive' => $currentRoute === 'admin.functions.index',
        ],
        [
            'id' => 'settings',
            'href' => route('admin.settings.index'),
            'text' => __('app.admin.nav.settings'),
            'icon' => 'icon-[hugeicons--settings-01]',
            'isActive' => $currentRoute === 'admin.settings.index',
        ],
        [
            'id' => 'backups',
            'href' => route('admin.backups.index'),
            'text' => __('app.admin.nav.backups'),
            'icon' => 'icon-[hugeicons--database-02]',
            'isActive' => $currentRoute === 'admin.backups.index',
        ],
    ];
@endphp

<x-layouts.base
    :title="$title"
    class="bg-bg text-fg min-h-screen font-sans antialiased"
>
    <x-ui.sidebar-wrapper
        class="fixed inset-y-0 start-0 z-80 flex h-dvh w-11/12 max-w-64 flex-col justify-between overflow-hidden border-e border-bg-muted/70 bg-bg-surface px-4 py-3 transition-all ease-linear max-md:ltr:-translate-x-full max-md:rtl:translate-x-full max-md:fx-open:translate-x-0 md:w-64 md:transition-none"
    >
        <div class="min-h-max border-b border-border py-2">
            <x-site.brand
                :href="route('admin.users.index')"
                size="sm"
                text="Seven Up"
                :accent="__('app.admin.brand_suffix')"
                wire:navigate
                class="font-semibold"
            />
        </div>

        <nav class="flex flex-1 flex-col pt-6">
            <span class="mb-2 text-sm uppercase text-fg-muted">{{ __('app.admin.nav.section') }}</span>
            <ul class="space-y-2 text-fg-muted">
                @foreach ($items as $item)
                    <x-atoms.sidebar-item
                        :text="$item['text']"
                        :href="$item['href']"
                        :icon="$item['icon']"
                        :is-active="$item['isActive']"
                        wire:key="admin-nav-{{ $item['id'] }}"
                    />
                @endforeach
            </ul>
        </nav>

        <div>
            <ul class="flex flex-col gap-y-2 text-fg-muted">
                <li class="w-full">
                    <div class="flex w-full items-center gap-3 rounded-ui border border-border-strong/40 p-1.5">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-ui bg-primary/15 text-sm font-bold text-primary"
                            aria-hidden="true"
                        >
                            {{ $initials }}
                        </span>

                        <div class="flex flex-1 flex-col overflow-hidden text-start -space-y-0.5">
                            <span class="truncate text-sm font-semibold text-fg-title" dir="ltr">
                                {{ $mobile ?: __('app.panel.account') }}
                            </span>
                            <span class="truncate text-xs text-fg-muted">
                                {{ __('app.admin.account') }}
                            </span>
                        </div>
                    </div>
                </li>

                <li class="w-full">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="ghost" size="sm" class="w-full justify-center">
                            {{ __('app.shortener.logout') }}
                        </x-ui.button>
                    </form>
                </li>
            </ul>
        </div>
    </x-ui.sidebar-wrapper>

    <main class="min-h-screen w-full min-w-0 overflow-x-hidden md:ps-64">
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

        <div class="min-w-0 px-4 pb-10 pt-2 sm:px-8">
            {{ $slot }}
        </div>
    </main>

    <div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="
            message = $event.detail.message ?? '';
            type = $event.detail.type ?? 'success';
            show = true;
            clearTimeout(window.__adminToastTimer);
            window.__adminToastTimer = setTimeout(() => show = false, 3200);
        "
        x-show="show"
        x-cloak
        x-transition.opacity.duration.200ms
        class="pointer-events-none fixed inset-x-0 bottom-6 z-200 flex justify-center px-4"
        role="status"
        aria-live="polite"
    >
        <div
            class="pointer-events-auto max-w-md rounded-ui border px-4 py-3 text-sm font-medium shadow-lg"
            :class="{
                'border-success/30 bg-success/10 text-success': type === 'success',
                'border-danger/30 bg-danger/10 text-danger': type === 'danger',
                'border-border bg-bg-surface text-fg-title': type !== 'success' && type !== 'danger',
            }"
            x-text="message"
        ></div>
    </div>
</x-layouts.base>
