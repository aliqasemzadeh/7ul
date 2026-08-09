<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_name;

    public string $site_description;

    public ?string $logo_path;

    public ?string $favicon_path;

    public ?string $social_telegram;

    public ?string $social_instagram;

    public ?string $social_aparat;

    public ?string $social_eitaa;

    public ?string $social_bale;

    public ?string $social_rubika;

    public ?string $social_x;

    public ?string $social_youtube;

    public ?string $social_linkedin;

    public ?string $social_whatsapp;

    public ?string $contact_email;

    public ?string $contact_phone;

    public ?string $contact_address;

    public static function group(): string
    {
        return 'site';
    }

    public function logoUrl(): ?string
    {
        return $this->publicUrl($this->logo_path);
    }

    public function faviconUrl(): ?string
    {
        return $this->publicUrl($this->favicon_path);
    }

    /**
     * @return array<string, string>
     */
    public function filledSocialLinks(): array
    {
        $links = [
            'telegram' => $this->social_telegram,
            'instagram' => $this->social_instagram,
            'aparat' => $this->social_aparat,
            'eitaa' => $this->social_eitaa,
            'bale' => $this->social_bale,
            'rubika' => $this->social_rubika,
            'x' => $this->social_x,
            'youtube' => $this->social_youtube,
            'linkedin' => $this->social_linkedin,
            'whatsapp' => $this->social_whatsapp,
        ];

        return array_filter(
            $links,
            static fn (?string $url): bool => filled($url),
        );
    }

    protected function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
