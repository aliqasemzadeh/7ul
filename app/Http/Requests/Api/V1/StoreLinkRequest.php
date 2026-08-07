<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\LinkTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination' => ['required', 'string'],
            'type' => ['required', Rule::enum(LinkTypeEnum::class)],
            'is_public_stats' => ['sometimes', 'boolean'],
        ];
    }
}
