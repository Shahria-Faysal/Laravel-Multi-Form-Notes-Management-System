<?php

namespace App\Jobs;

use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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
        $user = DB::table('users')
            ->where('id', $this->schedule->user_id)
            ->first();

        if ($user && $user->is_admin) {

            BackupService::runBackup(interval: $this->schedule->is_continuous);

        } else {

            BackupService::runUserBackup(
                userId: $this->schedule->user_id,
                label: $this->schedule->label ?? '',
                isInstant: false,
                interval: $this->schedule->is_continuous
            );
        }
    }
}
