<?php

namespace App\Http\Controllers;

use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupScheduleController extends Controller
{
    public function index()
    {
        // dd(BackupSchedule::where('user_id', auth()->id())->latest()->get());
        $schedules = BackupSchedule::where('user_id', auth()->id())->latest()->get();
        return view('BackupModes', compact('schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'time' => 'required',
            'days' => 'required',
        ]);

        BackupSchedule::create([
            'user_id' => auth()->id(),
            'label' => $request->label,
            'time' => $request->time,
            'days' => json_decode($request->days),
            'status' => true,
            'is_instant' => false,
            'is_continuous' => $request->is_continuous ?? 0,
        ]);

        return back()->with('success', 'Backup slot added.');
    }

    public function destroy($id)
    {
        BackupSchedule::where('user_id', auth()->id())->findOrFail($id)->delete();
        return back()->with('success', 'Backup slot removed.');
    }

    public function toggle(Request $request, $id)
    {
        $schedule = BackupSchedule::where('user_id', auth()->id())->findOrFail($id);
        $schedule->status = $request->status;
        $schedule->save();

        return response()->json(['success' => true]);
    }

    public function contToggle(Request $request, $id)
    {
        $schedule = BackupSchedule::where('user_id', auth()->id())->findOrFail($id);
        $schedule->is_continous = $request->is_continous;
        $schedule->save();

        return response()->json(['success' => true]);
    }

    public function instant(Request $request)
    {
        $success = BackupService::runUserBackup(
            userId: auth()->id(),
            label: 'Instant backup',
            isInstant: true,
        );

        return back()->with($success ? 'backup_taken' : 'error', $success ? true : 'Backup failed.');
    }
}
