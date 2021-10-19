<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Event\Controllers\EventController;
use Event\Models\Event;

Route::model('group', Group::class);
Route::model('event', Event::class);

Route::middleware('auth.basic')->prefix('group/{group}/events')->name('group.events.')->group(function () {
    $controller = EventController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{event}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{event}', $controller . '@update')->name('update');
    Route::delete('{event}', $controller . '@destroy')->name('destroy');
});
