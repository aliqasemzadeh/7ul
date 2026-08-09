<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.site_name', '7UL.ir');
        $this->migrator->add('site.site_description', '');
        $this->migrator->add('site.logo_path', null);
        $this->migrator->add('site.favicon_path', null);
        $this->migrator->add('site.social_telegram', null);
        $this->migrator->add('site.social_instagram', null);
        $this->migrator->add('site.social_aparat', null);
        $this->migrator->add('site.social_eitaa', null);
        $this->migrator->add('site.social_bale', null);
        $this->migrator->add('site.social_rubika', null);
        $this->migrator->add('site.social_x', null);
        $this->migrator->add('site.social_youtube', null);
        $this->migrator->add('site.social_linkedin', null);
        $this->migrator->add('site.social_whatsapp', null);
        $this->migrator->add('site.contact_email', null);
        $this->migrator->add('site.contact_phone', null);
        $this->migrator->add('site.contact_address', null);
    }
};
