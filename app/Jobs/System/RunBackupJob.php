<?php

namespace App\Jobs\System;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunBackupJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * @param  string  $type  What to backup: "both", "database" or "files".
     * @param  string  $destination  Where to store the backup: "local" or "remote".
     */
    public function __construct(
        public string $type = 'both',
        public string $destination = 'local',
    ) {}

    public function handle(): void
    {
        Log::info('Backup job started.', [
            'type' => $this->type,
            'destination' => $this->destination,
        ]);

        $options = [
            '--disable-notifications' => true,
            '--only-to-disk' => $this->destination === 'remote' ? 'backup_remote' : 'local',
        ];

        if ($this->type === 'database') {
            $options['--only-db'] = true;
        }

        if ($this->type === 'files') {
            $options['--only-files'] = true;
        }

        $exitCode = Artisan::call('backup:run', $options);

        $output = trim(Artisan::output());

        if ($exitCode === 0) {
            Log::info('Backup job finished successfully.', [
                'type' => $this->type,
                'destination' => $this->destination,
                'output' => $output,
            ]);
        } else {
            Log::error('Backup job finished with errors.', [
                'type' => $this->type,
                'destination' => $this->destination,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Backup job failed.', [
            'type' => $this->type,
            'destination' => $this->destination,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
