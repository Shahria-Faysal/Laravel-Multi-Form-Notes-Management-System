<?php

use App\Http\Controllers\BackupScheduleController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Auth;

// Auth::routes();


Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');

Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');

Route::get('/notes/table-data', [NoteController::class, 'tableData'])->name('notes.table.data');

Route::post('notes/store', [NoteController::class, 'store'])->name('notes.store');

Route::put('notes/update/{note}',[NoteController::class, 'update'])->name('notes.update');

Route::delete('notes/trash/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::delete('notes/delete/{note}', [NoteController::class, 'forceDelete'])->name('notes.force-delete')->withTrashed();

Route::post('notes/restore/{note}', [NoteController::class, 'restore'])->name('notes.restore')->withTrashed();

Route::post('notes/take-backup', [NoteController::class, 'backup'])->name('notes.backup');

// Route::view('/notes/backupmodes','backupModes')->name('BackupModes');

Route::resource('notes', NoteController::class);



Route::get('/',[UserController::class, 'dashboard'])->name('dashboard');

Route::view('/login', 'login')->name('login'); // show login page
Route::post('/login', [UserController::class, 'login'])->name('loginMatch');

Route::view('/register', 'register')->name('register');
Route::post('/register', [UserController::class, 'register'])->name('registerSave');

Route::middleware('auth')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

});

Route::resource('users', UserController::class);



Route::middleware('auth')->group(function () {

    Route::get('/backup/schedule', [BackupScheduleController::class, 'index'])->name('backup.schedule.index');

    Route::post('/backup/schedule', [BackupScheduleController::class, 'store'])->name('backup.schedule.store');

    Route::delete('/backup/schedule/{id}', [BackupScheduleController::class, 'destroy'])->name('backup.schedule.destroy');

    Route::patch('/backup/schedule/{id}/toggle', [BackupScheduleController::class, 'toggle']);

    Route::post('/backup/instant', [BackupScheduleController::class, 'instant'])->name('backup.instant');

});