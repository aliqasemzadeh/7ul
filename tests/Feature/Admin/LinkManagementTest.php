<?php

namespace Tests\Feature\Admin;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LinkManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_links(): void
    {
        $this->get(route('admin.links.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_links_list(): void
    {
        $admin = User::factory()->admin()->create();
        $link = Link::factory()->create([
            'user_id' => $admin->id,
            'destination' => 'https://example.com/admin-list',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->assertSee($link->short_code)
            ->assertSee('https://example.com/admin-list')
            ->assertSee(__('app.admin.links.heading'));
    }

    public function test_authenticated_user_can_create_link(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->call('create')
            ->set('form.user_id', $owner->id)
            ->set('form.destination', 'https://example.com/created')
            ->set('form.type', LinkTypeEnum::Link->value)
            ->set('form.is_public_stats', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('links', [
            'user_id' => $owner->id,
            'destination' => 'https://example.com/created',
            'type' => LinkTypeEnum::Link->value,
        ]);
    }

    public function test_create_link_validates_destination(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->call('create')
            ->set('form.user_id', $admin->id)
            ->set('form.destination', '')
            ->call('save')
            ->assertHasErrors(['form.destination']);
    }

    public function test_authenticated_user_can_update_link(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $link = Link::factory()->create([
            'user_id' => $admin->id,
            'destination' => 'https://example.com/old',
            'type' => LinkTypeEnum::Link,
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->call('edit', $link->id)
            ->set('form.user_id', $owner->id)
            ->set('form.destination', 'https://example.com/new')
            ->set('form.type', LinkTypeEnum::Text->value)
            ->set('form.is_public_stats', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $link->refresh();

        $this->assertSame($owner->id, $link->user_id);
        $this->assertSame('https://example.com/new', $link->destination);
        $this->assertSame(LinkTypeEnum::Text, $link->type);
        $this->assertFalse($link->is_public_stats);
    }

    public function test_authenticated_user_can_delete_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = Link::factory()->create(['user_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->call('delete', $link->id)
            ->assertDispatched('notify');

        $this->assertSoftDeleted($link);
    }

    public function test_links_can_be_searched_by_short_code(): void
    {
        $admin = User::factory()->admin()->create();
        $match = Link::factory()->create([
            'user_id' => $admin->id,
            'short_code' => 'Ab12Cd34',
            'destination' => 'https://example.com/match',
        ]);
        Link::factory()->create([
            'user_id' => $admin->id,
            'short_code' => 'Zz99Yy88',
            'destination' => 'https://example.com/other',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->set('search', 'Ab12Cd34')
            ->assertSee($match->short_code)
            ->assertDontSee('Zz99Yy88');
    }

    public function test_links_can_be_searched_by_owner_mobile(): void
    {
        $admin = User::factory()->admin()->create(['mobile' => '09120000001']);
        $owner = User::factory()->create(['mobile' => '09129999999']);
        $match = Link::factory()->create([
            'user_id' => $owner->id,
            'destination' => 'https://example.com/owner-match',
        ]);
        Link::factory()->create([
            'user_id' => $admin->id,
            'destination' => 'https://example.com/admin-owned',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.link.index')
            ->set('search', '09129999999')
            ->assertSee($match->short_code)
            ->assertSee('https://example.com/owner-match')
            ->assertDontSee('https://example.com/admin-owned');
    }
}
