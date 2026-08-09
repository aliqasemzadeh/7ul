<?php

namespace App\Jobs\System;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class UpdateProjectJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $runComposer = true,
    ) {}

    public function handle(): void
    {
        if (! $this->gitPull()) {
            return;
        }

        if ($this->runComposer) {
            $this->runComposerUpdate();
        }

        $this->runMigrations();
        $this->clearCache();
        $this->clearRoute();
        $this->clearView();
        $this->addTheme();
        $this->restartQueue();
    }

    protected function gitPull(): bool
    {
        Log::info('Starting project update (git pull)...');

        $process = Process::forever()
            ->path(base_path())
            ->run('git pull');

        if ($process->successful()) {
            Log::info("Git pull successful:\n".$process->output());

            return true;
        }

        Log::error("Git pull failed:\n".$process->errorOutput());

        return false;
    }

    protected function runMigrations(): void
    {
        $this->runArtisan('migrate');
    }

    protected function runComposerUpdate(): void
    {
        Log::info('Running composer install...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->composerCommand().' install --no-dev --optimize-autoloader --no-interaction');

            if ($process->successful()) {
                Log::info("Composer install successful:\n".$process->output());

                return;
            }

            Log::error("Composer install failed:\n".$process->errorOutput());
        } catch (Throwable $exception) {
            Log::error('Composer install failed: '.$exception->getMessage());
        }
    }

    protected function clearCache(): void
    {
        $this->runArtisan('cache:clear');
    }

    protected function clearRoute(): void
    {
        $this->runArtisan('route:clear');
    }

    protected function clearView(): void
    {
        $this->runArtisan('view:clear');
    }

    protected function addTheme(): void
    {
        Log::info('Building theme assets (npm run build)...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->npmCommand().' run build');

            if ($process->successful()) {
                Log::info("Theme build successful:\n".$process->output());

                return;
            }

            Log::error("Theme build failed:\n".$process->errorOutput());
        } catch (Throwable $exception) {
            Log::error('Theme build failed: '.$exception->getMessage());
        }
    }

    protected function restartQueue(): void
    {
        $this->runArtisan('queue:restart');
    }

    protected function runArtisan(string $command, array $parameters = []): void
    {
        Log::info("{$command}...");

        Artisan::call($command, $parameters);

        Log::info("{$command}:\n".Artisan::output());
    }

    protected function npmCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    }

    protected function composerCommand(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'composer.bat' : 'composer';
    }
}
