<?php

namespace Tests\Feature\Api;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_bearer_token(): void
    {
        $this->getJson('/api/v1/links')
            ->assertUnauthorized();
    }

    public function test_api_rejects_invalid_token(): void
    {
        $this->withToken('invalid-token')
            ->getJson('/api/v1/links')
            ->assertUnauthorized();
    }

    public function test_user_can_list_and_create_links_via_api(): void
    {
        $user = User::factory()->create();
        $token = $user->ensureApiToken();

        Link::factory()->for($user)->create([
            'destination' => 'https://listed.example',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/links')
            ->assertOk()
            ->assertJsonPath('data.0.destination', 'https://listed.example');

        $this->withToken($token)
            ->postJson('/api/v1/links', [
                'destination' => 'https://created.example',
                'type' => LinkTypeEnum::Link->value,
                'is_public_stats' => false,
            ])
            ->assertCreated()
            ->assertJsonPath('data.destination', 'https://created.example')
            ->assertJsonPath('data.type', 'link')
            ->assertJsonPath('data.is_public_stats', false);

        $this->assertDatabaseHas('links', [
            'user_id' => $user->id,
            'destination' => 'https://created.example',
            'is_public_stats' => false,
        ]);
    }

    public function test_user_can_view_owned_link_stats_via_api(): void
    {
        $user = User::factory()->create();
        $token = $user->ensureApiToken();
        $link = Link::factory()->for($user)->create();

        $link->visits()->create([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/links/'.$link->short_code.'/stats')
            ->assertOk()
            ->assertJsonPath('data.total_visits', 1)
            ->assertJsonPath('data.by_browser.Chrome', 1);
    }

    public function test_user_cannot_access_another_users_link_via_api(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $token = $stranger->ensureApiToken();
        $link = Link::factory()->for($owner)->create();

        $this->withToken($token)
            ->getJson('/api/v1/links/'.$link->short_code)
            ->assertNotFound();
    }

    public function test_regenerated_token_invalidates_previous_token(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->ensureApiToken();
        $newToken = $user->regenerateApiToken();

        $this->withToken($oldToken)
            ->getJson('/api/v1/links')
            ->assertUnauthorized();

        $this->withToken($newToken)
            ->getJson('/api/v1/links')
            ->assertOk();
    }
}
