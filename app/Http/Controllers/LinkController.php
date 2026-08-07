<?php

namespace App\Http\Controllers;

use App\Actions\Links\GetLinkStats;
use App\Enums\LinkTypeEnum;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class LinkController extends Controller
{
    public function redirect(Request $request, string $shortCode): RedirectResponse|View
    {
        $link = Link::query()->where('short_code', $shortCode)->firstOrFail();

        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        $link->visits()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $agent->deviceType(),
            'browser' => $agent->browser() ?: null,
            'os' => $agent->platform() ?: null,
        ]);

        return match ($link->type) {
            LinkTypeEnum::Link, LinkTypeEnum::Utm => redirect()->away($link->destination),
            LinkTypeEnum::Iframe => view('links.iframe', ['content' => $link->destination, 'link' => $link]),
            LinkTypeEnum::Code => view('links.code', ['content' => $link->destination, 'link' => $link]),
            LinkTypeEnum::Text => view('links.text', ['content' => $link->destination, 'link' => $link]),
        };
    }

    public function stats(string $shortCode, GetLinkStats $getLinkStats): View
    {
        $link = Link::query()->where('short_code', $shortCode)->firstOrFail();

        if (! $link->is_public_stats && Auth::id() !== $link->user_id) {
            abort(403);
        }

        return view('links.stats', $getLinkStats->handle($link));
    }
}
