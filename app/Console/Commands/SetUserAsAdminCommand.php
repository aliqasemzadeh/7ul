<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Signature('app:set-user-as-admin {mobile?}')]
#[Description('Ask for a mobile number and assign the admin role')]
class SetUserAsAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mobile = $this->argument('mobile');

        if (! is_string($mobile) || $mobile === '') {
            $mobile = $this->ask(__('app.console.set_admin.ask_mobile'));
        }

        if (! is_string($mobile) || $mobile === '') {
            $this->error(__('app.auth.mobile_required'));

            return self::FAILURE;
        }

        $mobile = $this->normalizeIranianMobile($mobile);

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            $this->error(__('app.auth.mobile_invalid'));

            return self::FAILURE;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::query()->firstOrCreate(
            ['mobile' => $mobile],
        );

        $user->assignRole($adminRole);

        $this->info(__('app.console.set_admin.success', ['mobile' => $mobile]));

        return self::SUCCESS;
    }

    protected function normalizeIranianMobile(string $mobile): string
    {
        $mobile = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $mobile,
        );

        return preg_replace('/\D+/', '', $mobile) ?? '';
    }
}
