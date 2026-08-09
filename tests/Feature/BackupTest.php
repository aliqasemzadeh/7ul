<?php

namespace Tests\Feature;

use App\Jobs\System\RunBackupJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupTest extends TestCase
{
    public function test_backup_job_can_be_dispatched_and_executes_successfully(): void
    {
        // Mocking Artisan call to backup:run
        Artisan::shouldReceive('call')
            ->with('backup:run', \Mockery::type('array'))
            ->once()
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->andReturn('Backup successful');

        $job = new RunBackupJob(type: 'database', destination: 'local');
        $job->handle();

        // If no exception is thrown and Mockery expectations are met, the test passes
        $this->assertTrue(true);
    }

    public function test_backup_configuration_is_correct(): void
    {
        $this->assertEquals(['local'], config('backup.backup.destination.disks'));
        $this->assertContains(env('DB_CONNECTION', 'mysql'), config('backup.backup.source.databases'));
    }

    public function test_backup_is_scheduled_at_six_am(): void
    {
        $schedule = app(Schedule::class);

        $event = collect($schedule->events())->first(function ($event) {
            return str_contains($event->description, RunBackupJob::class) ||
                   (isset($event->job) && $event->job instanceof RunBackupJob);
        });

        $this->assertNotNull($event, 'Backup job is not scheduled.');
        $this->assertEquals('0 6 * * *', $event->expression);
    }
}
