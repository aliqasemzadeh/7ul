<?php

namespace App\Jobs\System;

use App\Models\SystemActionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class UpdateProjectJob implements ShouldQueue
{
    use Queueable;

    protected ?SystemActionLog $log = null;
    protected string $fullOutput = "";

    public function __construct(
        public bool $runComposer = true,
    ) {}

    public function handle(): void
    {
        $this->log = SystemActionLog::create([
            'command' => 'project:update ' . ($this->runComposer ? '--with-composer' : '--quick'),
            'status' => 'running',
        ]);

        try {
            if (! $this->gitPull()) {
                $this->finishLog('failed');
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

            $this->finishLog('success');
        } catch (Throwable $e) {
            $this->appendToOutput("\n\nCritical Error: " . $e->getMessage());
            $this->finishLog('failed');
        }
    }

    protected function appendToOutput(string $text): void
    {
        $this->fullOutput .= $text . "\n";
        $this->log?->update(['output' => $this->fullOutput]);
    }

    protected function finishLog(string $status): void
    {
        $this->log?->update([
            'output' => $this->fullOutput,
            'status' => $status,
        ]);
    }

    protected function gitPull(): bool
    {
        $this->appendToOutput('Starting project update (git pull)...');

        $process = Process::forever()
            ->path(base_path())
            ->run('git pull');

        if ($process->successful()) {
            $this->appendToOutput("Git pull successful:\n".$process->output());

            return true;
        }

        $this->appendToOutput("Git pull failed:\n".$process->errorOutput());

        return false;
    }

    protected function runMigrations(): void
    {
        $this->runArtisan('migrate');
    }

    protected function runComposerUpdate(): void
    {
        $this->appendToOutput('Running composer install...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->composerCommand().' install --no-dev --optimize-autoloader --no-interaction');

            if ($process->successful()) {
                $this->appendToOutput("Composer install successful:\n".$process->output());

                return;
            }

            $this->appendToOutput("Composer install failed:\n".$process->errorOutput());
        } catch (Throwable $exception) {
            $this->appendToOutput('Composer install failed: '.$exception->getMessage());
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
        $this->appendToOutput('Building theme assets (npm run build)...');

        try {
            $process = Process::forever()
                ->path(base_path())
                ->run($this->npmCommand().' run build');

            if ($process->successful()) {
                $this->appendToOutput("Theme build successful:\n".$process->output());

                return;
            }

            $this->appendToOutput("Theme build failed:\n".$process->errorOutput());
        } catch (Throwable $exception) {
            $this->appendToOutput('Theme build failed: '.$exception->getMessage());
        }
    }

    protected function restartQueue(): void
    {
        $this->runArtisan('queue:restart');
    }

    protected function runArtisan(string $command, array $parameters = []): void
    {
        $this->appendToOutput("Running artisan {$command}...");

        Artisan::call($command, $parameters);

        $this->appendToOutput("Artisan {$command} output:\n".Artisan::output());
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
