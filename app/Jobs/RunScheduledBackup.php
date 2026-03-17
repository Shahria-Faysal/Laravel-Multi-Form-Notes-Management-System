<?php

namespace App\Jobs;

use App\Services\BackupService;
use App\Models\BackupSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunScheduledBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public $queue = 'backups';
    /**
     * Create a new job instance.
     */
    public function __construct(public BackupSchedule $schedule)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        BackupService::runUserBackup(
            userId:    $this->schedule->user_id,
            label:     $this->schedule->label,
            isInstant: false,
        );
    }
}
