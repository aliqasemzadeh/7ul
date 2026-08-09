@php
    /** @var \App\Settings\SiteSettings $settings */
    $settings = app(\App\Settings\SiteSettings::class);
    $socialLinks = $settings->filledSocialLinks();
    $icons = [
        'telegram' => 'icon-[hugeicons--telegram]',
        'instagram' => 'icon-[hugeicons--instagram]',
        'aparat' => 'icon-[hugeicons--video-01]',
        'eitaa' => 'icon-[hugeicons--message-01]',
        'bale' => 'icon-[hugeicons--chat]',
        'rubika' => 'icon-[hugeicons--smart-phone-01]',
        'x' => 'icon-[hugeicons--new-twitter]',
        'youtube' => 'icon-[hugeicons--youtube]',
        'linkedin' => 'icon-[hugeicons--linkedin-01]',
        'whatsapp' => 'icon-[hugeicons--whatsapp]',
    ];
@endphp

@if ($socialLinks !== [] || filled($settings->contact_email) || filled($settings->contact_phone) || filled($settings->contact_address))
    <div {{ $attributes->class(['flex flex-col items-center gap-4 sm:items-end']) }}>
        @if ($socialLinks !== [])
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach ($socialLinks as $network => $url)
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex size-9 items-center justify-center rounded-ui border border-border text-fg-muted transition hover:border-primary hover:text-primary"
                        title="{{ __('app.admin.settings.social_'.$network) }}"
                        aria-label="{{ __('app.admin.settings.social_'.$network) }}"
                    >
                        <span class="iconify {{ $icons[$network] ?? 'icon-[hugeicons--link-04]' }} size-4"></span>
                    </a>
                @endforeach
            </div>
        @endif

        @if (filled($settings->contact_email) || filled($settings->contact_phone) || filled($settings->contact_address))
            <div class="space-y-1 text-center text-sm text-fg-muted sm:text-end">
                @if (filled($settings->contact_email))
                    <p>
                        <a href="mailto:{{ $settings->contact_email }}" class="transition hover:text-primary" dir="ltr">
                            {{ $settings->contact_email }}
                        </a>
                    </p>
                @endif
                @if (filled($settings->contact_phone))
                    <p>
                        <a href="tel:{{ $settings->contact_phone }}" class="transition hover:text-primary" dir="ltr">
                            {{ $settings->contact_phone }}
                        </a>
                    </p>
                @endif
                @if (filled($settings->contact_address))
                    <p>{{ $settings->contact_address }}</p>
                @endif
            </div>
        @endif
    </div>
@endif
