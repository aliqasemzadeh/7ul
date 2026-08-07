<?php

namespace Tests\Feature;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LinkShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_shortener_page(): void
    {
        $this->get(route('shortener'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_generate_short_link(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::shortener')
            ->set('destination', 'https://example.com/path')
            ->set('type', LinkTypeEnum::Link->value)
            ->set('isPublic', true)
            ->call('generateShortLink')
            ->assertHasNoErrors()
            ->assertSet('shortLink', fn (?string $shortLink): bool => is_string($shortLink) && str_contains($shortLink, url('/')));

        $link = Link::query()->first();

        $this->assertNotNull($link);
        $this->assertSame($user->id, $link->user_id);
        $this->assertSame('https://example.com/path', $link->destination);
        $this->assertSame(LinkTypeEnum::Link, $link->type);
        $this->assertTrue($link->is_public_stats);
        $this->assertSame(8, strlen($link->short_code));
        $this->assertNotNull($link->creator_ip);
    }

    public function test_destination_is_required_to_generate_short_link(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::shortener')
            ->set('destination', '')
            ->call('generateShortLink')
            ->assertHasErrors(['destination']);
    }

    public function test_link_type_redirects_and_records_visit(): void
    {
        $link = Link::factory()->create([
            'destination' => 'https://example.com/target',
            'type' => LinkTypeEnum::Link,
        ]);

        $this->get(route('links.redirect', $link->short_code))
            ->assertRedirect('https://example.com/target');

        $this->assertDatabaseHas('link_visits', [
            'link_id' => $link->id,
        ]);

        $visit = $link->visits()->first();

        $this->assertNotNull($visit);
        $this->assertNotNull($visit->device_type);
        $this->assertNotNull($visit->browser);
        $this->assertNotNull($visit->os);
    }

    public function test_text_type_renders_content_view(): void
    {
        $link = Link::factory()->ofType(LinkTypeEnum::Text)->create([
            'destination' => 'Hello short text',
        ]);

        $this->get(route('links.redirect', $link->short_code))
            ->assertOk()
            ->assertSee('Hello short text');
    }

    public function test_code_type_renders_content_view(): void
    {
        $link = Link::factory()->ofType(LinkTypeEnum::Code)->create([
            'destination' => 'echo "hi";',
        ]);

        $this->get(route('links.redirect', $link->short_code))
            ->assertOk()
            ->assertSee('echo "hi";', false);
    }

    public function test_iframe_type_renders_raw_content(): void
    {
        $link = Link::factory()->ofType(LinkTypeEnum::Iframe)->create([
            'destination' => '<iframe src="https://example.com"></iframe>',
        ]);

        $this->get(route('links.redirect', $link->short_code))
            ->assertOk()
            ->assertSee('<iframe src="https://example.com"></iframe>', false);
    }

    public function test_public_stats_are_visible_to_guests(): void
    {
        $link = Link::factory()->create([
            'is_public_stats' => true,
        ]);

        $this->get(route('links.stats', $link->short_code))
            ->assertOk()
            ->assertSee(__('app.shortener.stats_heading'))
            ->assertSee('Chrome');
    }

    public function test_private_stats_are_forbidden_for_non_owners(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $link = Link::factory()->for($owner)->privateStats()->create();

        $this->actingAs($stranger)
            ->get(route('links.stats', $link->short_code))
            ->assertForbidden();
    }

    public function test_private_stats_are_visible_to_owner(): void
    {
        $owner = User::factory()->create();

        $link = Link::factory()->for($owner)->privateStats()->create();

        $this->actingAs($owner)
            ->get(route('links.stats', $link->short_code))
            ->assertOk();
    }
}
