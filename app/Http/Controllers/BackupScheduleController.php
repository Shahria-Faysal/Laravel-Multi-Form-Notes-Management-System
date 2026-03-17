<?php

namespace App\Http\Controllers;

use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupScheduleController extends Controller
{
    // index — only show current user's schedules
    public function index()
    {
        // dd(BackupSchedule::where('user_id', auth()->id())->latest()->get());
        $schedules = BackupSchedule::where('user_id', auth()->id())->latest()->get();
        return view('BackupModes', compact('schedules'));
    }

    // store — attach user_id when creating
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
        ]);

        return back()->with('success', 'Backup slot added.');
    }

    // destroy — make sure user owns it before deleting
    public function destroy($id)
    {
        BackupSchedule::where('user_id', auth()->id())->findOrFail($id)->delete();
        return back()->with('success', 'Backup slot removed.');
    }

    // toggle — make sure user owns it before toggling
    public function toggle(Request $request, $id)
    {
        $schedule = BackupSchedule::where('user_id', auth()->id())->findOrFail($id);
        $schedule->status = $request->status;
        $schedule->save();

        return response()->json(['success' => true]);
    }

    // instant backup triggered by the user
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
