<?php

namespace App\Livewire\Forms\Admin;

use App\Actions\Links\CreateShortLink;
use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class LinkForm extends Form
{
    public ?Link $link = null;

    public ?int $user_id = null;

    public string $destination = '';

    public string $type = LinkTypeEnum::Link->value;

    public bool $is_public_stats = true;

    public function setLink(Link $link): void
    {
        $this->link = $link;
        $this->user_id = $link->user_id;
        $this->destination = $link->destination;
        $this->type = $link->type->value;
        $this->is_public_stats = $link->is_public_stats;
    }

    public function resetForm(): void
    {
        $this->link = null;
        $this->user_id = null;
        $this->destination = '';
        $this->type = LinkTypeEnum::Link->value;
        $this->is_public_stats = true;
        $this->resetValidation();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'destination' => ['required', 'string'],
            'type' => ['required', Rule::enum(LinkTypeEnum::class)],
            'is_public_stats' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => __('app.admin.links.owner_required'),
            'user_id.exists' => __('app.admin.links.owner_required'),
            'destination.required' => __('app.shortener.destination_required'),
        ];
    }

    public function store(CreateShortLink $createShortLink): Link
    {
        $validated = $this->validate();

        return $createShortLink->handle(
            user: User::query()->findOrFail($validated['user_id']),
            destination: $validated['destination'],
            type: LinkTypeEnum::from($validated['type']),
            isPublicStats: $validated['is_public_stats'],
            creatorIp: request()->ip(),
        );
    }

    public function update(): Link
    {
        $validated = $this->validate();

        $this->link->update([
            'user_id' => $validated['user_id'],
            'destination' => $validated['destination'],
            'type' => LinkTypeEnum::from($validated['type']),
            'is_public_stats' => $validated['is_public_stats'],
        ]);

        return $this->link->refresh();
    }
}
