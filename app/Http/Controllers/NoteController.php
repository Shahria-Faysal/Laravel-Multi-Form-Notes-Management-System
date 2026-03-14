<?php

namespace App\Http\Controllers;

use App\DataTables\NotesDataTable;
use App\Events\FormSubmitted;
use App\Jobs\SendEmailJob;
use App\Models\Note;
use App\Notifications\NewNote;
use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(NotesDataTable $notesDataTable)
    {
        $noteTrashDataTable = app(NotesDataTable::class);

        $notesDataTable->with('tableId', 'notes-table');
        $noteTrashDataTable->with('tableId', 'trash-table');

        // ✅ Backup cooldown check
        $backupCooldown = false;
        $remainingSeconds = 0;

        $lastBackup = DB::table('backup_logs')
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        if ($lastBackup && $lastBackup->interval > 0) {
            $nextAllowed = Carbon::parse($lastBackup->created_at)
                ->addMinutes($lastBackup->interval);

            if (now()->lt($nextAllowed)) {
                $backupCooldown = true;
                $remainingSeconds = now()->diffInSeconds($nextAllowed);
            }
        }

        return $notesDataTable->render('home', [
            'notesDataTable' => $notesDataTable->html(),
            'TrashTable' => $noteTrashDataTable->html(),
            'backupCooldown' => $backupCooldown,    // ✅ passed to blade
            'remainingSeconds' => $remainingSeconds,  // ✅ passed to blade
        ]);
    }

    public function tableData(Request $request)
    {
        $tableId = $request->input('tableId', 'notes-table');

        $dt = app(NotesDataTable::class);
        $dt->with('tableId', $tableId);

        return $dt->ajax();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shownotes');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user = auth()->user();
        SendEmailJob::dispatch($request->all(), $user);

        // Schedule::job(new SendEmailJob($request->all()))->everyFiveMinutes();

        return response()->json(['success' => true]);
    }
    public function destroy(Note $note)
    {
        try {
            $note->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete']);
        }
    }

    public function update(Request $request, Note $note)
    {
        try {
            $note->update([
                'title' => $request->title,
                'note' => $request->note
            ]);
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update note']);
        }
    }

    public function forceDelete(Note $note)
    {
        try {
            $note->forceDelete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete']);
        }
    }

    public function restore(Note $note)
    {
        try {
            $note->restore();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to restore']);
        }
    }

    public function backup(Request $request)
    {
        $interval = (int) $request->input('interval');

        // ✅ Check cooldown from last successful backup
        $lastBackup = DB::table('backup_logs')
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        if ($lastBackup && $lastBackup->interval > 0) {
            $nextAllowed = Carbon::parse($lastBackup->created_at)
                ->addMinutes($lastBackup->interval);

            if (now()->lt($nextAllowed)) {
                $remaining = now()->diffInSeconds($nextAllowed);
                return back()->with('error', "Please wait {$remaining} seconds before taking another backup.");
            }
        }

        try {
            $host = '127.0.0.1';
            $port = '3306';
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $mysqldump = 'D:\\PROGRAMMING\\Databse\\Xampp\\mysql\\bin\\mysqldump.exe';
            $backupDir = storage_path('app\\backups');

            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupPath = $backupDir . '\\' . $database . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $command = "\"{$mysqldump}\" -h {$host} -P {$port} -u {$username} {$database} > \"{$backupPath}\" 2>&1";

            shell_exec($command);

            if (file_exists($backupPath) && filesize($backupPath) > 0) {
                DB::table('backup_logs')->insert([
                    'filename' => basename($backupPath),
                    'status' => 'success',
                    'interval' => $interval,
                    'created_at' => now(),
                ]);
                return back()->with('success', 'Backup saved: ' . basename($backupPath));
            }

            throw new \Exception('Backup file was not created');

        } catch (\Exception $e) {
            DB::table('backup_logs')->insert([
                'filename' => 'failed_' . now()->format('Y-m-d_H-i-s'),
                'status' => 'failed',
                'interval' => $interval,
                'created_at' => now(),
            ]);
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }

        // try {
        //     Artisan::call('backup:run');
        //     dd(Artisan::output());

        //     DB::table('backup_logs')->insert([
        //         'filename' => 'spatie_backup_' . now()->format('Y-m-d_H-i-s'),
        //         'status' => 'success',
        //         'interval' => $interval,
        //         'created_at' => now(),
        //     ]);

        //     return back()->with('success', 'Backup completed successfully!');

        // } catch (\Exception $e) {

        //     DB::table('backup_logs')->insert([
        //         'filename' => 'spatie_backup_' . now()->format('Y-m-d_H-i-s'),
        //         'status' => 'failed',
        //         'interval' => $interval,
        //         'created_at' => now(),
        //     ]);

        //     return back()->with('error', 'Backup failed: ' . $e->getMessage());
        // }
    }
}
