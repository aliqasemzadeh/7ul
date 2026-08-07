<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.user')] class extends Component
{
    use WithPagination;

    public string $apiToken = '';

    public function mount(): void
    {
        $this->apiToken = Auth::user()->ensureApiToken();
    }

    public function rendering($view): void
    {
        $view->title(__('app.panel.api.title'));
    }

    public function regenerateToken(): void
    {
        $this->apiToken = Auth::user()->regenerateApiToken();
    }

    #[Computed]
    public function loginLogs()
    {
        return Auth::user()
            ->authentications()
            ->latest('login_at')
            ->paginate(10, pageName: 'logsPage');
    }

    public function formatJalali(mixed $date): string
    {
        if ($date === null) {
            return __('app.shortener.unknown');
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d H:i');
    }
};
?>

@php
    $baseUrl = url('/api/v1');
    $getExample = <<<CURL
curl -X GET "{$baseUrl}/links" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"
CURL;

    $postExample = <<<CURL
curl -X POST "{$baseUrl}/links" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json" \\
  -H "Content-Type: application/json" \\
  -d '{"destination":"https://example.com","type":"link","is_public_stats":true}'
CURL;

    $phpGetExample = <<<'PHP'
$client = new \GuzzleHttp\Client();
$response = $client->get(config('app.url').'/api/v1/links', [
    'headers' => [
        'Authorization' => 'Bearer '.env('SEVENUP_API_TOKEN'),
        'Accept' => 'application/json',
    ],
]);
$links = json_decode($response->getBody()->getContents(), true);
PHP;

    $phpPostExample = <<<'PHP'
$client = new \GuzzleHttp\Client();
$response = $client->post(config('app.url').'/api/v1/links', [
    'headers' => [
        'Authorization' => 'Bearer '.env('SEVENUP_API_TOKEN'),
        'Accept' => 'application/json',
    ],
    'json' => [
        'destination' => 'https://example.com/landing',
        'type' => 'link',
        'is_public_stats' => true,
    ],
]);
$link = json_decode($response->getBody()->getContents(), true);
PHP;

    $aiSkill = <<<SKILL
---
name: sevenup-link-api
description: Use this skill when integrating with the Seven Up Link (7UL) short-link HTTP API to list, create, show, or fetch stats for short links.
---

# Seven Up Link API Skill

## Auth
- Every request needs header: `Authorization: Bearer {api_token}`
- Token is issued in the user panel API page and stored on the user record.
- Base URL: {$baseUrl}

## Endpoints
| Method | Path | Purpose |
| --- | --- | --- |
| GET | /links | Paginated list of the authenticated user's links |
| POST | /links | Create a short link |
| GET | /links/{shortCode} | Show one owned link |
| GET | /links/{shortCode}/stats | Visit analytics for an owned link |

## POST /links body
```json
{
  "destination": "string (required)",
  "type": "link|utm|iframe|code|text",
  "is_public_stats": true
}
```

## Rules for agents
- Never invent endpoints outside this table.
- Always send Accept: application/json.
- shortCode is exactly 8 alphanumeric characters.
- Only the token owner can access their links/stats.
- Prefer GET for reads and POST for create; do not use PUT/PATCH/DELETE unless added later.
SKILL;
@endphp

<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.panel.api.heading') }}</h2>
        <p class="mt-1 text-fg-muted">{{ __('app.panel.api.subtitle') }}</p>
    </div>

    <x-ui.card class="space-y-4 p-(--card-padding)" :shadow="true">
        <div>
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.panel.api.token_heading') }}</h3>
            <p class="mt-1 text-sm text-fg-muted">{{ __('app.panel.api.token_help') }}</p>
        </div>

        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center"
            x-data="{ copied: false }"
        >
            <code
                class="flex-1 break-all rounded-ui border border-border bg-bg-subtle px-3 py-2 text-sm text-fg-title"
                dir="ltr"
                x-ref="token"
            >{{ $apiToken }}</code>

            <div class="flex flex-col gap-2 sm:flex-row">
                <x-ui.button
                    type="button"
                    size="md"
                    class="w-full justify-center sm:w-auto"
                    x-on:click="
                        navigator.clipboard.writeText($refs.token.innerText.trim());
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-show="!copied">{{ __('app.panel.api.copy_token') }}</span>
                    <span x-cloak x-show="copied">{{ __('app.shortener.copied') }}</span>
                </x-ui.button>

                <x-ui.button
                    type="button"
                    size="md"
                    variant="outline"
                    class="w-full justify-center sm:w-auto"
                    wire:click="regenerateToken"
                    wire:confirm="{{ __('app.panel.api.regenerate_confirm') }}"
                >
                    {{ __('app.panel.api.regenerate') }}
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-4 p-(--card-padding)" :shadow="true">
        <h3 class="text-lg font-bold text-fg-title">{{ __('app.panel.api.docs_heading') }}</h3>
        <p class="text-sm text-fg-muted">
            {{ __('app.panel.api.base_url') }}:
            <code class="text-fg-title" dir="ltr">{{ $baseUrl }}</code>
        </p>
        <p class="text-sm text-fg-muted">
            {{ __('app.panel.api.auth_header') }}:
            <code class="text-fg-title" dir="ltr">Authorization: Bearer YOUR_API_TOKEN</code>
        </p>

        <div class="overflow-x-auto rounded-ui border border-border">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-bg-subtle text-fg-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.method') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.path') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.description') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-border">
                        <td class="px-4 py-3 font-semibold text-success">GET</td>
                        <td class="px-4 py-3" dir="ltr">/links</td>
                        <td class="px-4 py-3">{{ __('app.panel.api.endpoints.list') }}</td>
                    </tr>
                    <tr class="border-b border-border">
                        <td class="px-4 py-3 font-semibold text-primary">POST</td>
                        <td class="px-4 py-3" dir="ltr">/links</td>
                        <td class="px-4 py-3">{{ __('app.panel.api.endpoints.create') }}</td>
                    </tr>
                    <tr class="border-b border-border">
                        <td class="px-4 py-3 font-semibold text-success">GET</td>
                        <td class="px-4 py-3" dir="ltr">/links/{shortCode}</td>
                        <td class="px-4 py-3">{{ __('app.panel.api.endpoints.show') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold text-success">GET</td>
                        <td class="px-4 py-3" dir="ltr">/links/{shortCode}/stats</td>
                        <td class="px-4 py-3">{{ __('app.panel.api.endpoints.stats') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-4 p-(--card-padding)" :shadow="true">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.panel.api.skill_heading') }}</h3>
            <div x-data="{ copied: false }">
                <x-ui.button
                    type="button"
                    size="sm"
                    variant="outline"
                    x-on:click="
                        navigator.clipboard.writeText($refs.skill.innerText);
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-show="!copied">{{ __('app.panel.api.copy_skill') }}</span>
                    <span x-cloak x-show="copied">{{ __('app.shortener.copied') }}</span>
                </x-ui.button>
            </div>
        </div>
        <pre
            x-ref="skill"
            class="overflow-x-auto rounded-ui border border-border bg-bg-subtle p-4 text-xs leading-relaxed text-fg"
            dir="ltr"
        >{{ $aiSkill }}</pre>
    </x-ui.card>

    <x-ui.card class="space-y-4 p-(--card-padding)" :shadow="true">
        <h3 class="text-lg font-bold text-fg-title">{{ __('app.panel.api.examples_heading') }}</h3>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-fg-title">GET — cURL</h4>
            <pre class="overflow-x-auto rounded-ui border border-border bg-bg-subtle p-4 text-xs" dir="ltr">{{ $getExample }}</pre>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-fg-title">POST — cURL</h4>
            <pre class="overflow-x-auto rounded-ui border border-border bg-bg-subtle p-4 text-xs" dir="ltr">{{ $postExample }}</pre>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-fg-title">GET — PHP</h4>
            <pre class="overflow-x-auto rounded-ui border border-border bg-bg-subtle p-4 text-xs" dir="ltr">{{ $phpGetExample }}</pre>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-fg-title">POST — PHP</h4>
            <pre class="overflow-x-auto rounded-ui border border-border bg-bg-subtle p-4 text-xs" dir="ltr">{{ $phpPostExample }}</pre>
        </div>
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <div class="border-b border-border px-4 py-4 sm:px-6">
            <h3 class="text-lg font-bold text-fg-title">{{ __('app.panel.api.logs_heading') }}</h3>
            <p class="mt-1 text-sm text-fg-muted">{{ __('app.panel.api.logs_subtitle') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-border bg-bg-subtle text-fg-muted">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.logs.login_at') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.shortener.ip') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.logs.device') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.logs.status') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('app.panel.api.logs.suspicious') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->loginLogs as $log)
                        <tr class="border-b border-border last:border-0" wire:key="auth-log-{{ $log->id }}">
                            <td class="px-4 py-3 whitespace-nowrap" dir="ltr">
                                {{ $this->formatJalali($log->login_at) }}
                            </td>
                            <td class="px-4 py-3" dir="ltr">{{ $log->ip_address ?: __('app.shortener.unknown') }}</td>
                            <td class="max-w-64 truncate px-4 py-3" title="{{ $log->user_agent }}">
                                {{ $log->device_name ?: ($log->user_agent ?: __('app.shortener.unknown')) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->login_successful)
                                    <span class="font-medium text-success">{{ __('app.panel.api.logs.success') }}</span>
                                @else
                                    <span class="font-medium text-danger">{{ __('app.panel.api.logs.failed') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->is_suspicious)
                                    <span class="text-warning">{{ $log->suspicious_reason ?: __('app.panel.api.logs.yes') }}</span>
                                @else
                                    {{ __('app.panel.api.logs.no') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-fg-muted">
                                {{ __('app.panel.api.logs.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->loginLogs->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->loginLogs->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
