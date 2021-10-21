<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Poll\Controllers\PollController;
use Poll\Models\Poll;
use Poll\Models\PollOption;

Route::model('group', Group::class);
Route::model('poll', Poll::class);
Route::model('pollOption', PollOption::class);

Route::middleware('auth.basic')->prefix('groups/{group}/polls')->name('group.polls.')->group(function () {
    $controller = PollController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{poll}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{poll}', $controller . '@update')->name('update');
    Route::delete('{poll}', $controller . '@destroy')->name('destroy');
    Route::post('{poll}/select/{pollOption}', $controller . '@select')->name('select');
    Route::post('{poll}/unselect/{pollOption}', $controller . '@unselect')->name('unselect');
});
