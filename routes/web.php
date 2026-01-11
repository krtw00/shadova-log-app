<?php

use App\Http\Controllers\BattleController;
use Illuminate\Support\Facades\Route;

// The root path now redirects to the main battle log page.
Route::get('/', function () {
    return redirect()->route('battles.index');
});

// Route for the battle log page.
Route::get('/battles', [BattleController::class, 'index'])->name('battles.index');

// Placeholder routes for other sections.
// These will be implemented later.
Route::get('/decks', function () {
    // Implement deck management view
    return 'Deck Management Page';
})->name('decks.index');

Route::get('/statistics', function () {
    // Implement statistics view
    return 'Statistics Page';
})->name('statistics.index');