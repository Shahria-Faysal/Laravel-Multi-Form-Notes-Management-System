<?php

use App\Jobs\RunScheduledBackup;
use App\Models\BackupSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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

            $timeMatches = $now->format('H:i') === Carbon::parse($schedule->time)->format('H:i');
            $dayMatches  = in_array($todayShort, $schedule->days ?? []);
            // Log::info("Schedule {$schedule->id} — time: {$schedule->time}, timeMatches: " . ($timeMatches ? 'yes' : 'no') . ", dayMatches: " . ($dayMatches ? 'yes' : 'no'));
            if ($timeMatches && $dayMatches) {
                RunScheduledBackup::dispatch($schedule);
            }
        });

})->everyMinute();

