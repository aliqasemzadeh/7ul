<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Links\CreateShortLink;
use App\Actions\Links\GetLinkStats;
use App\Enums\LinkTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLinkRequest;
use App\Http\Resources\Api\V1\LinkResource;
use App\Http\Resources\Api\V1\LinkStatsResource;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $links = $request->user()
            ->links()
            ->withCount('visits')
            ->latest()
            ->paginate(20);

        return LinkResource::collection($links);
    }

    public function store(StoreLinkRequest $request, CreateShortLink $createShortLink): JsonResource
    {
        $validated = $request->validated();

        $link = $createShortLink->handle(
            user: $request->user(),
            destination: $validated['destination'],
            type: LinkTypeEnum::from($validated['type']),
            isPublicStats: (bool) ($validated['is_public_stats'] ?? true),
            creatorIp: $request->ip(),
        );

        $link->loadCount('visits');

        return (new LinkResource($link))->response()->setStatusCode(201);
    }

    public function show(Request $request, string $shortCode): LinkResource
    {
        $link = $this->ownedLink($request, $shortCode);
        $link->loadCount('visits');

        return new LinkResource($link);
    }

    public function stats(Request $request, string $shortCode, GetLinkStats $getLinkStats): LinkStatsResource
    {
        $link = $this->ownedLink($request, $shortCode);

        return new LinkStatsResource($getLinkStats->handle($link));
    }

    private function ownedLink(Request $request, string $shortCode): Link
    {
        return $request->user()
            ->links()
            ->where('short_code', $shortCode)
            ->firstOrFail();
    }
}
