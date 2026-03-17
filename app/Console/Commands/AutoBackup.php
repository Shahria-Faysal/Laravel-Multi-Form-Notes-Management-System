<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class AutoBackup extends Command
{
    protected $signature = 'TakeBackup:start {interval=5}';
    protected $description = 'Start automatic database backups';

    public function handle()
    {
        $interval = (int) $this->argument('interval');

        if ($interval < 1) {
            $this->error("Interval must be at least 1 minute.");
            return;
        }

        $this->info("Auto backup started. Interval: {$interval} minutes");

        while (true) {

            BackupService::runBackup($interval);

            $this->info("Backup completed at " . now());

            sleep($interval * 60);
        }
    }
}