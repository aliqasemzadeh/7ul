<?php

use App\Jobs\System\RunBackupJob;
use App\Livewire\Concerns\EnsuresUserIsAdmin;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;

    public function rendering($view): void
    {
        $view->title(__('app.admin.nav.backups').' | 7UL.ir');
    }

    public function runBackup(): void
    {
        RunBackupJob::dispatch('database', 'local');

        $this->dispatch('notify', message: __('app.admin.backups.dispatched'), type: 'success');
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.nav.backups') }}</h2>
        <x-ui.button wire:click="runBackup()" icon="icon-[hugeicons--database-02]">
            {{ __('app.admin.backups.run_manual') }}
        </x-ui.button>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <x-ui.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-fg-title mb-4">{{ __('app.admin.backups.current_config') }}</h3>
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
