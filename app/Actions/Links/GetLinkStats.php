<?php

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Collection;

class GetLinkStats
{
    /**
     * @return array{
     *     link: Link,
     *     visits: Collection<int, \App\Models\LinkVisit>,
     *     totalVisits: int,
     *     byDevice: Collection<string, int>,
     *     byBrowser: Collection<string, int>,
     *     byOs: Collection<string, int>
     * }
     */
    public function handle(Link $link): array
    {
        $link->loadMissing(['visits' => fn ($query) => $query->latest()]);

        $visits = $link->visits;

        return [
            'link' => $link,
            'visits' => $visits,
            'totalVisits' => $visits->count(),
            'byDevice' => $visits->groupBy('device_type')->map->count()->sortDesc(),
            'byBrowser' => $visits->groupBy('browser')->map->count()->sortDesc(),
            'byOs' => $visits->groupBy('os')->map->count()->sortDesc(),
        ];
    }
}
