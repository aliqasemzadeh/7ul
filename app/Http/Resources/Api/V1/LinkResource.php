<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Link
 */
class LinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'short_code' => $this->short_code,
            'short_url' => url('/'.$this->short_code),
            'destination' => $this->destination,
            'type' => $this->type->value,
            'is_public_stats' => $this->is_public_stats,
            'visits_count' => $this->whenCounted('visits'),
            'created_at' => $this->created_at?->toIso8601String(),
            'stats_url' => route('links.stats', $this->short_code),
        ];
    }
}
