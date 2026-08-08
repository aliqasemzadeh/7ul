<?php

namespace App\Livewire\Forms\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user = null;

    public string $mobile = '';

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->mobile = $user->mobile;
    }

    public function resetForm(): void
    {
        $this->user = null;
        $this->mobile = '';
        $this->resetValidation();
    }

    /**
     * @return array<string, list<string|Unique>>
     */
    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique('users', 'mobile')->ignore($this->user?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => __('app.auth.mobile_required'),
            'mobile.regex' => __('app.auth.mobile_invalid'),
            'mobile.unique' => __('app.admin.users.mobile_unique'),
        ];
    }

    public function store(): User
    {
        $this->mobile = $this->normalizeIranianMobile($this->mobile);
        $validated = $this->validate();

        return User::query()->create([
            'mobile' => $validated['mobile'],
            'registration_ip' => request()->ip(),
        ]);
    }

    public function update(): User
    {
        $this->mobile = $this->normalizeIranianMobile($this->mobile);
        $validated = $this->validate();

        $this->user->update([
            'mobile' => $validated['mobile'],
        ]);

        return $this->user->refresh();
    }

    protected function normalizeIranianMobile(string $mobile): string
    {
        $mobile = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $mobile,
        );

        return preg_replace('/\D+/', '', $mobile) ?? '';
    }
}
