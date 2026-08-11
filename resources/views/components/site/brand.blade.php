@props([
    'showText' => true,
    'forceTextVisible' => false,
    'size' => 'md',
    'href' => null,
    'text' => null,
    'accent' => null,
])

@php
    /** @var \App\Settings\SiteSettings $settings */
    $settings = $siteSettings ?? app(\App\Settings\SiteSettings::class);
    $siteName = filled($settings->site_name) ? $settings->site_name : __('app.welcome.brand');
    $logoUrl = $settings->logoUrl();
    $displayText = $text ?? $siteName;
    $sizes = [
        'sm' => ['badge' => 'size-8 text-sm', 'logo' => 'h-8', 'text' => 'text-base'],
        'md' => ['badge' => 'size-10 text-lg', 'logo' => 'h-10', 'text' => 'text-xl'],
        'lg' => ['badge' => 'size-12 text-xl', 'logo' => 'h-12', 'text' => 'text-2xl'],
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $link = $href ?? url('/');
    $textVisibility = $forceTextVisible ? '' : 'hidden sm:inline';
@endphp

<a href="{{ $link }}" {{ $attributes->class(['group flex items-center gap-3']) }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $siteName }}"
            class="{{ $sizeClasses['logo'] }} w-auto object-contain transition duration-300 ease-out group-hover:scale-105"
        />
    @else
        <span
            class="{{ $sizeClasses['badge'] }} flex items-center justify-center rounded-ui bg-primary font-black text-white shadow-sm transition duration-300 ease-out group-hover:scale-105"
        >
            {{ __('app.welcome.brand_short') }}
        </span>
    @endif

    @if ($showText)
        <span class="{{ $sizeClasses['text'] }} {{ $textVisibility }} font-black tracking-tight text-fg-title">
            @if ($accent)
                {{ $displayText }}
                <span class="text-primary">{{ $accent }}</span>
            @else
                {{ $displayText }}
            @endif
        </span>
    @endif
</a>
