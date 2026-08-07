<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.shortener.stats_title', ['code' => $link->short_code]) }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-bg text-fg min-h-screen font-sans antialiased">
    <main class="mx-auto w-full max-w-5xl px-5 py-10 sm:px-8">
        <header class="mb-8 space-y-2">
            <p class="text-sm font-semibold text-primary">{{ __('app.welcome.brand') }}</p>
            <h1 class="text-3xl font-black text-fg-title">{{ __('app.shortener.stats_heading') }}</h1>
            <p class="text-fg-muted" dir="ltr">{{ url('/'.$link->short_code) }}</p>
            <p class="text-sm text-fg-muted">
                {{ __('app.shortener.total_visits') }}:
                <span class="font-semibold text-fg-title">{{ number_format($totalVisits) }}</span>
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-3">
            <section class="rounded-ui border border-border bg-bg-elevated p-4">
                <h2 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_device') }}</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($byDevice as $device => $count)
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ $device ?: __('app.shortener.unknown') }}</span>
                            <span class="font-semibold">{{ number_format($count) }}</span>
                        </li>
                    @empty
                        <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-ui border border-border bg-bg-elevated p-4">
                <h2 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_browser') }}</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($byBrowser as $browser => $count)
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ $browser ?: __('app.shortener.unknown') }}</span>
                            <span class="font-semibold">{{ number_format($count) }}</span>
                        </li>
                    @empty
                        <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-ui border border-border bg-bg-elevated p-4">
                <h2 class="mb-3 text-sm font-semibold text-fg-title">{{ __('app.shortener.by_os') }}</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($byOs as $os => $count)
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ $os ?: __('app.shortener.unknown') }}</span>
                            <span class="font-semibold">{{ number_format($count) }}</span>
                        </li>
                    @empty
                        <li class="text-fg-muted">{{ __('app.shortener.no_visits') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <section class="mt-8 overflow-x-auto rounded-ui border border-border bg-bg-elevated">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-bg-subtle text-fg-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.visited_at') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.ip') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.device') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.browser') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.os') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr class="border-b border-border last:border-0">
                            <td class="px-4 py-3 whitespace-nowrap" dir="ltr">{{ $visit->created_at }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $visit->ip_address ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->device_type ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->browser ?: __('app.shortener.unknown') }}</td>
                            <td class="px-4 py-3">{{ $visit->os ?: __('app.shortener.unknown') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-fg-muted">
                                {{ __('app.shortener.no_visits') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
