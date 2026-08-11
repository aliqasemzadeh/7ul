<?php

namespace App\Livewire\Forms\Admin;

use App\Enums\LoginMethod;
use App\Settings\AuthSettings;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AuthSettingsForm extends Form
{
    public string $login_method = LoginMethod::MobileOtp->value;

    public function fillFromSettings(AuthSettings $settings): void
    {
        $this->login_method = $settings->login_method;
        $this->resetValidation();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'login_method' => ['required', Rule::enum(LoginMethod::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login_method.required' => __('app.admin.settings.login_method_required'),
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $settings = app(AuthSettings::class);
        $settings->login_method = $validated['login_method'];
        $settings->save();

        $this->fillFromSettings($settings);
    }
}
