@props([
    'title' => null,
    'class' => 'bg-bg min-h-screen overflow-hidden overflow-y-auto font-sans',
    'dir' => null,
    'lang' => null,
])

@php
    $htmlLang = $lang ?? str_replace('_', '-', app()->getLocale());
    $htmlDir = $dir ?? (str_starts_with(app()->getLocale(), 'fa') ? 'rtl' : 'ltr');
    $pageTitle = $title ?? config('app.name', 'Laravel');
@endphp
<!doctype html>
<html lang="{{ $htmlLang }}" dir="{{ $htmlDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>

    @livewireStyles
    @vite(['resources/css/app.css'])
    {{ $head ?? '' }}
    <script>
        (function(){const s=document.documentElement,d=s.dataset.theme,l=localStorage.getItem('theme'),m=window.matchMedia('(prefers-color-scheme: dark)').matches;s.classList.toggle('dark',d?d==='dark':l?l==='dark':m)})();
    </script>
</head>

<body {{ $attributes->class([$class]) }}>
    {{ $slot }}
    @vite(['resources/js/app.js', 'resources/js/flexilla.js'])
    @livewireScripts
</body>
</html>
