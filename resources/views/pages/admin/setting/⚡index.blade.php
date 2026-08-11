<?php

use App\Enums\LoginMethod;
use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Livewire\Forms\Admin\AuthSettingsForm;
use App\Livewire\Forms\Admin\SiteSettingsForm;
use App\Settings\AuthSettings;
use App\Settings\SiteSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;
    use WithFileUploads;

    public SiteSettingsForm $form;

    public AuthSettingsForm $authForm;

    public function mount(SiteSettings $settings, AuthSettings $authSettings): void
    {
        $this->form->fillFromSettings($settings);
        $this->authForm->fillFromSettings($authSettings);
    }

    public function rendering($view): void
    {
        $view->title(__('app.admin.settings.title'));
    }

    public function save(): void
    {
        $this->form->save();
        $this->authForm->save();

        $this->dispatch('notify', message: __('app.admin.settings.saved'), type: 'success');
    }

    public function removeLogo(): void
    {
        $this->form->removeLogo();

        $this->dispatch('notify', message: __('app.admin.settings.logo_removed'), type: 'success');
    }

    public function removeFavicon(): void
    {
        $this->form->removeFavicon();

        $this->dispatch('notify', message: __('app.admin.settings.favicon_removed'), type: 'success');
    }
};
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.settings.heading') }}</h2>
        <p class="mt-1 text-sm text-fg-muted">{{ __('app.admin.settings.subtitle') }}</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.settings.general') }}</h3>
                <p class="text-sm text-fg-muted">{{ __('app.admin.settings.general_help') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="space-y-2 lg:col-span-2">
                    <x-ui.input
                        wire:model="form.site_name"
                        name="form.site_name"
                        :label="__('app.admin.settings.site_name')"
                        :invalid="$errors->has('form.site_name')"
                    />
                    @error('form.site_name')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <x-ui.textarea
                        wire:model="form.site_description"
                        name="form.site_description"
                        :label="__('app.admin.settings.site_description')"
                        :invalid="$errors->has('form.site_description')"
                        rows="3"
                    />
                    @error('form.site_description')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <x-ui.input
                        type="file"
                        wire:model="form.logo"
                        name="form.logo"
                        :label="__('app.admin.settings.logo')"
                        :invalid="$errors->has('form.logo')"
                        accept="image/*"
                    />
                    @error('form.logo')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="form.logo" class="text-sm text-fg-muted">
                        {{ __('app.admin.settings.uploading') }}
                    </div>
                    @if ($form->logo_path)
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($form->logo_path) }}"
                                alt="{{ __('app.admin.settings.logo') }}"
                                class="h-12 w-auto rounded-ui border border-border object-contain bg-bg-surface p-1"
                            />
                            <x-ui.button type="button" variant="soft" intent="danger" size="sm" wire:click="removeLogo" wire:confirm="{{ __('app.admin.settings.confirm_remove_logo') }}">
                                {{ __('app.admin.settings.remove') }}
                            </x-ui.button>
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <x-ui.input
                        type="file"
                        wire:model="form.favicon"
                        name="form.favicon"
                        :label="__('app.admin.settings.favicon')"
                        :invalid="$errors->has('form.favicon')"
                        accept=".ico,image/png,image/jpeg,image/webp,image/gif,image/svg+xml"
                    />
                    @error('form.favicon')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="form.favicon" class="text-sm text-fg-muted">
                        {{ __('app.admin.settings.uploading') }}
                    </div>
                    @if ($form->favicon_path)
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($form->favicon_path) }}"
                                alt="{{ __('app.admin.settings.favicon') }}"
                                class="size-10 rounded-ui border border-border object-contain bg-bg-surface p-1"
                            />
                            <x-ui.button type="button" variant="soft" intent="danger" size="sm" wire:click="removeFavicon" wire:confirm="{{ __('app.admin.settings.confirm_remove_favicon') }}">
                                {{ __('app.admin.settings.remove') }}
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.settings.auth') }}</h3>
                <p class="text-sm text-fg-muted">{{ __('app.admin.settings.auth_help') }}</p>
            </div>

            <div class="space-y-2">
                <x-ui.select
                    name="authForm.login_method"
                    :label="__('app.admin.settings.login_method')"
                    wire:model="authForm.login_method"
                    class="w-full"
                >
                    @foreach (LoginMethod::cases() as $method)
                        <option value="{{ $method->value }}" wire:key="login-method-{{ $method->value }}">
                            {{ $method->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
                @error('authForm.login_method')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror
                <p class="text-xs text-fg-muted">{{ __('app.admin.settings.auth_registration_note') }}</p>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.settings.social') }}</h3>
                <p class="text-sm text-fg-muted">{{ __('app.admin.settings.social_help') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach ([
                    'social_telegram' => __('app.admin.settings.social_telegram'),
                    'social_instagram' => __('app.admin.settings.social_instagram'),
                    'social_aparat' => __('app.admin.settings.social_aparat'),
                    'social_eitaa' => __('app.admin.settings.social_eitaa'),
                    'social_bale' => __('app.admin.settings.social_bale'),
                    'social_rubika' => __('app.admin.settings.social_rubika'),
                    'social_x' => __('app.admin.settings.social_x'),
                    'social_youtube' => __('app.admin.settings.social_youtube'),
                    'social_linkedin' => __('app.admin.settings.social_linkedin'),
                    'social_whatsapp' => __('app.admin.settings.social_whatsapp'),
                ] as $field => $label)
                    <div class="space-y-2" wire:key="settings-{{ $field }}">
                        <x-ui.input
                            wire:model="form.{{ $field }}"
                            name="form.{{ $field }}"
                            :label="$label"
                            :invalid="$errors->has('form.'.$field)"
                            dir="ltr"
                            placeholder="https://"
                        />
                        @error('form.'.$field)
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-fg-title">{{ __('app.admin.settings.contact') }}</h3>
                <p class="text-sm text-fg-muted">{{ __('app.admin.settings.contact_help') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <x-ui.input
                        type="email"
                        wire:model="form.contact_email"
                        name="form.contact_email"
                        :label="__('app.admin.settings.contact_email')"
                        :invalid="$errors->has('form.contact_email')"
                        dir="ltr"
                    />
                    @error('form.contact_email')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <x-ui.input
                        wire:model="form.contact_phone"
                        name="form.contact_phone"
                        :label="__('app.admin.settings.contact_phone')"
                        :invalid="$errors->has('form.contact_phone')"
                        dir="ltr"
                    />
                    @error('form.contact_phone')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 md:col-span-2">
                    <x-ui.textarea
                        wire:model="form.contact_address"
                        name="form.contact_address"
                        :label="__('app.admin.settings.contact_address')"
                        :invalid="$errors->has('form.contact_address')"
                        rows="3"
                    />
                    @error('form.contact_address')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.button type="submit" variant="solid" intent="primary" class="w-full justify-center">
            {{ __('app.admin.settings.save') }}
        </x-ui.button>
    </form>
</div>
