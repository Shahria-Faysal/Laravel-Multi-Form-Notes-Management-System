<?php

namespace App\Http\Controllers;

use App\DataTables\NotesDataTable;
use App\Jobs\SendEmailJob;
use App\Models\Note;
use App\Notifications\NewNote;
use ArrayAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

        $backupCooldown = Cache::has('backup_cooldown');

        return $notesDataTable->render('home', [
            'notesDataTable' => $notesDataTable->html(),
            'TrashTable' => $noteTrashDataTable->html(),
            'backupCooldown' => $backupCooldown,
            // compact('backupCooldown') // this iswrong, passing as nested array
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

        SendEmailJob::dispatch($request->all());

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

        $cooldownKey = 'backup_cooldown';

        // ✅ Check if cooldown is active
        if (Cache::has($cooldownKey)) {
            $remainingSeconds = Cache::get($cooldownKey) - now()->timestamp;
            return back()->with('error', "Please wait {$remainingSeconds} seconds before taking another backup.");
        }

        $interval = (int) $request->input('interval');
        $cooldownMinutes = $interval;

        // ✅ Set cooldown BEFORE running backup
        Cache::put($cooldownKey, now()->addMinutes($cooldownMinutes)->timestamp, now()->addMinutes($cooldownMinutes));


        $host = '127.0.0.1';
        $port = '3306';
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $mysqldump = 'D:\\PROGRAMMING\\Databse\\Xampp\\mysql\\bin\\mysqldump.exe';

        // $backupDir = storage_path('app\\backups');
        $backupDir = 'D:\\PROGRAMMING\\Projects\\laravel\\bulk-form\\DB_Backup';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupPath = $backupDir . '\\' . $database . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $command = "\"{$mysqldump}\" -h {$host} -P {$port} -u {$username} {$database} > \"{$backupPath}\" 2>&1";

        shell_exec($command);

        if (file_exists($backupPath) && filesize($backupPath) > 0) {
            return back()->with('success', 'Backup saved successfully: ' . basename($backupPath));
        }

        return back()->with('error', 'Backup failed!');

        // Artisan::call('backup:run');
        // dd(Artisan::output());

        // return back()->with('success', 'Backup completed successfully!');
    }
}
