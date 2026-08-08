<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $admin = User::factory()->create(['mobile' => '09120000001']);
        $other = User::factory()->create(['mobile' => '09120000002']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->assertSee('09120000001')
            ->assertSee('09120000002')
            ->assertSee(__('app.admin.users.heading'));
    }

    public function test_authenticated_user_can_create_user(): void
    {
        $admin = User::factory()->create();

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
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('create')
            ->set('form.mobile', '12345')
            ->call('save')
            ->assertHasErrors(['form.mobile']);
    }

    public function test_authenticated_user_can_update_user(): void
    {
        $admin = User::factory()->create();
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
        $admin = User::factory()->create();
        $user = User::factory()->create(['mobile' => '09123333333']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('delete', $user->id)
            ->assertDispatched('notify');

        $this->assertModelMissing($user);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->call('delete', $admin->id)
            ->assertDispatched('notify');

        $this->assertModelExists($admin);
    }

    public function test_users_can_be_searched_by_mobile(): void
    {
        $admin = User::factory()->create(['mobile' => '09120000001']);
        User::factory()->create(['mobile' => '09129999999']);

        Livewire::actingAs($admin)
            ->test('pages::admin.user.index')
            ->set('search', '09129999999')
            ->assertSee('09129999999')
            ->assertDontSee('09120000001');
    }
}
