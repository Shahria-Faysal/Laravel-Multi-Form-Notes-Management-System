<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class AutoBackup extends Command
{
    protected $signature = 'TakeBackup:start {interval=5} {userId=0}';
    protected $description = 'Start automatic database backups';

    public function handle()
    {
        $interval = (int) $this->argument('interval');
        $userId   = (int) $this->argument('userId');

        if ($interval < 1) {
            $this->error("Interval must be at least 1 minute.");
            return;
        }

        $this->info("Auto backup started. Interval: {$interval} minutes");

        while (true) {
            if ($userId === 0) {
                BackupService::runBackup($interval);
            } else {
                BackupService::runUserBackup(
                    userId:    $userId,
                    label:     'Auto backup',
                    isInstant: false,
                );
            }

            $this->info("Backup completed at " . now());
            sleep($interval * 60);
        }
    }
}