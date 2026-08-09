<?php

namespace Tests\Feature\Admin;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_users(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_users_list(): void
    {
        $admin = User::factory()->admin()->create(['mobile' => '09120000001']);
        $other = User::factory()->create(['mobile' => '09120000002']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->assertSee('09120000001')
            ->assertSee('09120000002')
            ->assertSee(__('app.admin.users.heading'));
    }

    public function test_authenticated_user_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('create')
            ->set('form.mobile', '09121234567')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertNotNull(User::query()->where('mobile', '09121234567')->first());
    }

    public function test_create_user_validates_mobile_format(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('create')
            ->set('form.mobile', '12345')
            ->call('save')
            ->assertHasErrors(['form.mobile']);
    }

    public function test_authenticated_user_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['mobile' => '09121111111']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('edit', $user->id)
            ->set('form.mobile', '09122222222')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertSame('09122222222', $user->fresh()->mobile);
    }

    public function test_authenticated_user_can_delete_other_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['mobile' => '09123333333']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('delete', $user->id)
            ->assertDispatched('notify');

        $this->assertModelMissing($user);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('delete', $admin->id)
            ->assertDispatched('notify');

        $this->assertModelExists($admin);
    }

    public function test_users_can_be_searched_by_mobile(): void
    {
        $admin = User::factory()->admin()->create(['mobile' => '09120000001']);
        User::factory()->create(['mobile' => '09129999999']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->set('search', '09129999999')
            ->assertSee('09129999999')
            ->assertDontSee('09120000001');
    }

    public function test_guests_are_redirected_from_user_links_page(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.links', $user))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_user_links_page(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['mobile' => '09124444444']);
        $link = Link::factory()->for($user)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.links', $user))
            ->assertOk()
            ->assertSee($link->short_code)
            ->assertSee(__('app.admin.users.links_heading'));
    }

    public function test_authenticated_user_can_delete_link_from_user_links_page(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $link = Link::factory()->for($user)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.links', ['user' => $user])
            ->call('deleteLink', $link->id)
            ->assertDispatched('notify');

        $this->assertSoftDeleted($link);
    }

    public function test_guests_are_redirected_from_user_roles_page(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.roles', $user))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_user_roles_page(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['mobile' => '09125555555']);

        $this->actingAs($admin)
            ->get(route('admin.users.roles', $user))
            ->assertOk()
            ->assertSee(__('app.admin.users.roles_heading'));
    }

    public function test_authenticated_user_can_assign_and_remove_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.roles', ['user' => $user])
            ->set('selectedRole', 'editor')
            ->call('addRole')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertTrue($user->fresh()->hasRole('editor'));

        Livewire::actingAs($admin)
            ->test('pages::admin.user.roles', ['user' => $user])
            ->call('removeRole', 'editor')
            ->assertDispatched('notify');

        $this->assertFalse($user->fresh()->hasRole('editor'));
    }
}
