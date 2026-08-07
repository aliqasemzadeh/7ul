<?php

namespace App\Actions\Links;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Str;

class CreateShortLink
{
    public function handle(
        User $user,
        string $destination,
        LinkTypeEnum $type = LinkTypeEnum::Link,
        bool $isPublicStats = true,
        ?string $creatorIp = null,
    ): Link {
        do {
            $code = Str::random(8);
        } while (Link::query()->where('short_code', $code)->exists());

        return Link::query()->create([
            'user_id' => $user->id,
            'destination' => $destination,
            'short_code' => $code,
            'type' => $type,
            'creator_ip' => $creatorIp,
            'is_public_stats' => $isPublicStats,
        ]);
    }
}
