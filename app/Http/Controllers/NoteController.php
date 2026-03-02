<?php

namespace App\Http\Controllers;

use App\DataTables\NotesDataTable;
use App\Jobs\SendEmailJob;
use App\Models\Note;
use App\Notifications\NewNote;
use Illuminate\Http\Request;

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

        return $notesDataTable->render('home', [
            'notesDataTable' => $notesDataTable->html(),
            'TrashTable' => $noteTrashDataTable->html(),
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
        // foreach ($request->input('forms') as $noteData) {

        //     $note = Note::create([
        //         'title' => $noteData['title'],
        //         'note' => $noteData['note'],
        //     ]);
        //     SendEmailJob::dispatch($note);
        //     // $note->notify(new NewNote($note));
        // }
        SendEmailJob::dispatch($request->all());

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
}
