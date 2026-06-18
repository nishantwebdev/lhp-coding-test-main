<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/events')->name('home');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/data', [EventController::class, 'data'])->name('events.data');
Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('events/{event}/attendees/data', [AttendeeController::class, 'data'])
    ->name('events.attendees.data');
Route::post('events/{event}/attendees', [AttendeeController::class, 'store'])
    ->name('events.attendees.store')
    ->middleware('throttle:10,1');

Route::inertia('events-visual-1', 'Events/VisualOne')->name('events.visual1');
Route::inertia('events-visual-2', 'Events/VisualTwo')->name('events.visual2');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');

require __DIR__.'/settings.php';
