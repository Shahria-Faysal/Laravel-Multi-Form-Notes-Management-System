<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('home');
// });

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');

// Route::get('table-data', [NoteController::class, 'tableData'])->name('notes.table.data');

Route::get('/notes/table-data', [NoteController::class, 'tableData'])->name('notes.table.data');

Route::put('notes/update/{note}',[NoteController::class, 'update'])->name('notes.update');

Route::delete('notes/trash/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::delete('notes/delete/{note}', [NoteController::class, 'forceDelete'])->name('notes.force-delete')->withTrashed();

Route::post('notes/restore/{note}', [NoteController::class, 'restore'])->name('notes.restore')->withTrashed();

Route::post('notes/take-backup', [NoteController::class, 'backup'])->name('notes.backup');

Route::resource('notes', NoteController::class);