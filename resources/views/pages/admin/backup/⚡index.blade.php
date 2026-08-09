<?php

use Livewire\Component;
use App\Jobs\System\RunBackupJob;
use Illuminate\Support\Facades\Artisan;

new class extends Component
{
    public function runBackup()
    {
        RunBackupJob::dispatch('database', 'local');

        $this->dispatch('notify', message: __('app.admin.backups.dispatched'), type: 'success');
    }
};
?>

<x-layouts.admin :title="__('app.admin.nav.backups')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <x-ui.button wire:click="runBackup()" icon="icon-[hugeicons--database-02]">
                {{ __('app.admin.backups.run_manual') }}
            </x-ui.button>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-fg-title mb-4">{{ __('app.admin.backups.current_config') }}</h2>
                    <ul class="space-y-3 text-sm">
                        <li class="flex justify-between">
                            <span class="text-fg-muted">{{ __('app.admin.backups.schedule') }}:</span>
                            <span class="font-medium text-fg-title">Every day at 06:00</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-fg-muted">{{ __('app.admin.backups.type') }}:</span>
                            <span class="font-medium text-fg-title">Database</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-fg-muted">{{ __('app.admin.backups.destination') }}:</span>
                            <span class="font-medium text-fg-title">Local (storage/app/private)</span>
                        </li>
                    </ul>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
