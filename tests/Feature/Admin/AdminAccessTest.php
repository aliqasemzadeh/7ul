<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.links.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_non_admin_users_cannot_mount_admin_livewire_pages(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::admin.user.index')
            ->assertForbidden();
    }

    public function test_admin_users_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_user_panel_shows_admin_nav_only_for_admins(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('user.index'))
            ->assertOk()
            ->assertDontSee(__('app.panel.nav.admin'));

        $this->actingAs($admin)
            ->get(route('user.index'))
            ->assertOk()
            ->assertSee(__('app.panel.nav.admin'));
    }
}
