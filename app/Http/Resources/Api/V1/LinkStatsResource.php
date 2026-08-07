<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkStatsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{
         *     link: \App\Models\Link,
         *     visits: \Illuminate\Support\Collection,
         *     totalVisits: int,
         *     byDevice: \Illuminate\Support\Collection,
         *     byBrowser: \Illuminate\Support\Collection,
         *     byOs: \Illuminate\Support\Collection
         * } $stats
         */
        $stats = $this->resource;

        return [
            'link' => new LinkResource($stats['link']),
            'total_visits' => $stats['totalVisits'],
            'by_device' => $stats['byDevice'],
            'by_browser' => $stats['byBrowser'],
            'by_os' => $stats['byOs'],
            'visits' => $stats['visits']->map(fn ($visit): array => [
                'ip_address' => $visit->ip_address,
                'device_type' => $visit->device_type,
                'browser' => $visit->browser,
                'os' => $visit->os,
                'visited_at' => $visit->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
