<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Poll\Controllers\PollController;
use Poll\Models\Poll;

Route::model('group', Group::class);
Route::model('poll', Poll::class);

Route::middleware('auth.basic')->prefix('groups/{group}/polls')->name('group.polls.')->group(function () {
    $controller = PollController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{poll}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{poll}', $controller . '@update')->name('update');
    Route::delete('{poll}', $controller . '@destroy')->name('destroy');
});
