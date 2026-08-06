<!doctype html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @livewireStyles
    @vite(['resources/css/app.css'])
    {{ $head ?? '' }}
    <script>
        (function(){const s=document.documentElement,d=s.dataset.theme,l=localStorage.getItem('theme'),m=window.matchMedia('(prefers-color-scheme: dark)').matches;s.classList.toggle('dark',d?d==='dark':l?l==='dark':m)})();
    </script>
</head>

<body class="{{ $class ?? 'bg-bg min-h-screen overflow-hidden overflow-y-auto' }}">
    {{ $slot }}
    @livewireScripts
    @vite(['resources/js/app.js','resources/js/flexilla.js'])
</body>
</html>