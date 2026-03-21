<?php

use App\Jobs\RunScheduledBackup;
use App\Models\BackupSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::call(function () {

    $now        = Carbon::now();
    $dayMap     = [
        'Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We',
        'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'
    ];
    $todayShort = $dayMap[$now->format('D')];

    BackupSchedule::where('status', true)
        ->get()
        ->each(function ($schedule) use ($now, $todayShort) {

            // ── Continuous backup ─────────────────────────────────────────
            // is_continuous stores the interval in minutes e.g. 5
            // check if enough minutes have passed since last backup
            if ($schedule->is_continuous !== 0) {

                $lastBackup = DB::table('backup_logs')
                    ->where('user_id', $schedule->user_id)
                    ->where('status', 'success')
                    ->latest('created_at')
                    ->first();

                $shouldRun = false;

                if (!$lastBackup) {
                    // no backup ever taken → run immediately
                    $shouldRun = true;
                } else {
                    $nextAllowed = Carbon::parse($lastBackup->created_at)
                        ->addMinutes($schedule->is_continuous);

                    // if current time has passed the next allowed time → run
                    if ($now->gte($nextAllowed)) {
                        $shouldRun = true;
                    }
                }

                if ($shouldRun) {
                    RunScheduledBackup::dispatch($schedule);
                }

                // continuous backup handled, skip the time/day check below
                return;
            }

            // ── Scheduled backup (specific time + day) ────────────────────
            $timeMatches = $now->format('H:i') === Carbon::parse($schedule->time)->format('H:i');
            $dayMatches  = in_array($todayShort, $schedule->days ?? []);

            if ($timeMatches && $dayMatches) {
                RunScheduledBackup::dispatch($schedule);
            }
        });

})->everyMinute();


