<?php

namespace App\Livewire\Forms\Admin;

use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class SiteSettingsForm extends Form
{
    public string $site_name = '';

    public string $site_description = '';

    public ?string $logo_path = null;

    public ?string $favicon_path = null;

    public mixed $logo = null;

    public mixed $favicon = null;

    public ?string $social_telegram = null;

    public ?string $social_instagram = null;

    public ?string $social_aparat = null;

    public ?string $social_eitaa = null;

    public ?string $social_bale = null;

    public ?string $social_rubika = null;

    public ?string $social_x = null;

    public ?string $social_youtube = null;

    public ?string $social_linkedin = null;

    public ?string $social_whatsapp = null;

    public ?string $contact_email = null;

    public ?string $contact_phone = null;

    public ?string $contact_address = null;

    public function fillFromSettings(SiteSettings $settings): void
    {
        $this->site_name = $settings->site_name;
        $this->site_description = $settings->site_description;
        $this->logo_path = $settings->logo_path;
        $this->favicon_path = $settings->favicon_path;
        $this->social_telegram = $settings->social_telegram;
        $this->social_instagram = $settings->social_instagram;
        $this->social_aparat = $settings->social_aparat;
        $this->social_eitaa = $settings->social_eitaa;
        $this->social_bale = $settings->social_bale;
        $this->social_rubika = $settings->social_rubika;
        $this->social_x = $settings->social_x;
        $this->social_youtube = $settings->social_youtube;
        $this->social_linkedin = $settings->social_linkedin;
        $this->social_whatsapp = $settings->social_whatsapp;
        $this->contact_email = $settings->contact_email;
        $this->contact_phone = $settings->contact_phone;
        $this->contact_address = $settings->contact_address;
        $this->logo = null;
        $this->favicon = null;
        $this->resetValidation();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,gif,svg', 'max:512'],
            'social_telegram' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_aparat' => ['nullable', 'url', 'max:255'],
            'social_eitaa' => ['nullable', 'url', 'max:255'],
            'social_bale' => ['nullable', 'url', 'max:255'],
            'social_rubika' => ['nullable', 'url', 'max:255'],
            'social_x' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
            'social_whatsapp' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'site_name.required' => __('app.admin.settings.site_name_required'),
            'logo.image' => __('app.admin.settings.logo_invalid'),
            'favicon.mimes' => __('app.admin.settings.favicon_invalid'),
            'contact_email.email' => __('app.admin.settings.contact_email_invalid'),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function save(): void
    {
        $validated = $this->validate();

        $settings = app(SiteSettings::class);

        $settings->site_name = $validated['site_name'];
        $settings->site_description = $validated['site_description'] ?? '';
        $settings->social_telegram = $this->nullableString($validated['social_telegram'] ?? null);
        $settings->social_instagram = $this->nullableString($validated['social_instagram'] ?? null);
        $settings->social_aparat = $this->nullableString($validated['social_aparat'] ?? null);
        $settings->social_eitaa = $this->nullableString($validated['social_eitaa'] ?? null);
        $settings->social_bale = $this->nullableString($validated['social_bale'] ?? null);
        $settings->social_rubika = $this->nullableString($validated['social_rubika'] ?? null);
        $settings->social_x = $this->nullableString($validated['social_x'] ?? null);
        $settings->social_youtube = $this->nullableString($validated['social_youtube'] ?? null);
        $settings->social_linkedin = $this->nullableString($validated['social_linkedin'] ?? null);
        $settings->social_whatsapp = $this->nullableString($validated['social_whatsapp'] ?? null);
        $settings->contact_email = $this->nullableString($validated['contact_email'] ?? null);
        $settings->contact_phone = $this->nullableString($validated['contact_phone'] ?? null);
        $settings->contact_address = $this->nullableString($validated['contact_address'] ?? null);

        if ($this->logo instanceof TemporaryUploadedFile) {
            $settings->logo_path = $this->storeUploadedFile($this->logo, $settings->logo_path, 'logo');
        }

        if ($this->favicon instanceof TemporaryUploadedFile) {
            $settings->favicon_path = $this->storeUploadedFile($this->favicon, $settings->favicon_path, 'favicon');
        }

        $settings->save();

        $this->fillFromSettings($settings);
    }

    public function removeLogo(): void
    {
        $settings = app(SiteSettings::class);

        $this->deleteStoredFile($settings->logo_path);
        $settings->logo_path = null;
        $settings->save();

        $this->logo_path = null;
        $this->logo = null;
    }

    public function removeFavicon(): void
    {
        $settings = app(SiteSettings::class);

        $this->deleteStoredFile($settings->favicon_path);
        $settings->favicon_path = null;
        $settings->save();

        $this->favicon_path = null;
        $this->favicon = null;
    }

    protected function storeUploadedFile(TemporaryUploadedFile $file, ?string $previousPath, string $basename): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $path = $file->storeAs('settings', $basename.'.'.$extension, 'public');

        $this->deleteStoredFile($previousPath);

        return $path;
    }

    protected function deleteStoredFile(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function nullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? $value : null;
    }
}
