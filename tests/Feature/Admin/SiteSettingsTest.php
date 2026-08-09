<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_settings(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.setting.index')
            ->assertSee(__('app.admin.settings.heading'))
            ->assertSet('form.site_name', '7UL.ir');
    }

    public function test_authenticated_user_can_update_site_settings(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.setting.index')
            ->set('form.site_name', 'Seven Up Link')
            ->set('form.site_description', 'URL shortener')
            ->set('form.social_telegram', 'https://t.me/sevenup')
            ->set('form.contact_email', 'info@7ul.ir')
            ->set('form.contact_phone', '02112345678')
            ->set('form.contact_address', 'Tehran')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $settings = app(SiteSettings::class);

        $this->assertSame('Seven Up Link', $settings->site_name);
        $this->assertSame('URL shortener', $settings->site_description);
        $this->assertSame('https://t.me/sevenup', $settings->social_telegram);
        $this->assertSame('info@7ul.ir', $settings->contact_email);
        $this->assertSame('02112345678', $settings->contact_phone);
        $this->assertSame('Tehran', $settings->contact_address);
    }

    public function test_site_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.setting.index')
            ->set('form.site_name', '')
            ->call('save')
            ->assertHasErrors(['form.site_name']);
    }

    public function test_contact_email_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.setting.index')
            ->set('form.contact_email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['form.contact_email']);
    }

    public function test_authenticated_user_can_upload_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $logo = UploadedFile::fake()->image('logo.png', 120, 40);

        Livewire::actingAs($admin)
            ->test('pages::admin.setting.index')
            ->set('form.logo', $logo)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $settings = app(SiteSettings::class);

        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
    }

    public function test_home_page_uses_site_settings_meta(): void
    {
        $settings = app(SiteSettings::class);
        $settings->site_name = 'Custom Site';
        $settings->site_description = 'Custom description for SEO';
        $settings->contact_email = 'hello@7ul.ir';
        $settings->social_telegram = 'https://t.me/custom';
        $settings->save();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Custom description for SEO', false)
            ->assertSee('hello@7ul.ir')
            ->assertSee('https://t.me/custom', false);
    }
}
