<?php

namespace Tests\Feature;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_panel_pages(): void
    {
        $this->get(route('user.index'))->assertRedirect(route('login'));
        $this->get(route('user.create'))->assertRedirect(route('login'));
        $this->get(route('user.api'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_only_own_links(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = Link::factory()->for($user)->create([
            'destination' => 'https://mine.example',
        ]);
        Link::factory()->for($other)->create([
            'destination' => 'https://theirs.example',
        ]);

        Livewire::actingAs($user)
            ->test('pages::user.index')
            ->assertSee('https://mine.example')
            ->assertDontSee('https://theirs.example')
            ->assertSee(url('/'.$own->short_code));
    }

    public function test_authenticated_user_can_create_professional_link(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::user.create')
            ->set('destination', 'Hello professional text')
            ->set('type', LinkTypeEnum::Text->value)
            ->set('isPublic', false)
            ->call('generateShortLink')
            ->assertHasNoErrors()
            ->assertSet('shortLink', fn (?string $shortLink): bool => is_string($shortLink) && str_contains($shortLink, url('/')));

        $link = Link::query()->first();

        $this->assertNotNull($link);
        $this->assertSame($user->id, $link->user_id);
        $this->assertSame('Hello professional text', $link->destination);
        $this->assertSame(LinkTypeEnum::Text, $link->type);
        $this->assertFalse($link->is_public_stats);
    }

    public function test_owner_can_view_panel_stats_even_when_private(): void
    {
        $owner = User::factory()->create();
        $link = Link::factory()->for($owner)->privateStats()->create();

        $this->actingAs($owner)
            ->get(route('user.links.stats', $link->short_code))
            ->assertOk()
            ->assertSee(__('app.shortener.stats_heading'));
    }

    public function test_non_owner_cannot_view_panel_stats(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $link = Link::factory()->for($owner)->create();

        $this->actingAs($stranger)
            ->get(route('user.links.stats', $link->short_code))
            ->assertForbidden();
    }

    public function test_api_page_ensures_token_and_can_regenerate(): void
    {
        $user = User::factory()->create(['api_token' => null]);

        $component = Livewire::actingAs($user)
            ->test('pages::user.api');

        $user->refresh();
        $this->assertNotNull($user->api_token);
        $original = $user->api_token;

        $component
            ->assertSet('apiToken', $original)
            ->call('regenerateToken');

        $user->refresh();
        $this->assertNotSame($original, $user->api_token);
        $component->assertSet('apiToken', $user->api_token);
    }

    public function test_api_page_loads_with_examples_and_tester(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::user.api')
            ->assertOk()
            ->assertSee(__('app.panel.api.tester_heading'))
            ->assertSee(__('app.panel.api.examples_heading'))
            ->assertSee('GET — Python')
            ->assertSee('GET — Node.js')
            ->assertSee('GET — ASP.NET');
    }
}
