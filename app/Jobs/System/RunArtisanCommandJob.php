<?php

namespace App\Jobs\System;

use App\Models\SystemActionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class RunArtisanCommandJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $command,
        public array $parameters = [],
    ) {}

    public function handle(): void
    {
        $log = SystemActionLog::create([
            'command' => $this->command . ' ' . json_encode($this->parameters),
            'status' => 'running',
        ]);

        try {
            Artisan::call($this->command, $this->parameters);

            $log->update([
                'output' => Artisan::output(),
                'status' => 'success',
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'output' => Artisan::output() . "\n\nError: " . $exception->getMessage(),
                'status' => 'failed',
            ]);
        }
    }
}
