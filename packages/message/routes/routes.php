<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Message\Controllers\MessageController;
use Message\Models\Message;

Route::model('group', Group::class);

Route::middleware('auth.basic')->prefix('group/{group}/messages')->name('group.messages.')->group(function () {
    $controller = MessageController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::post('', $controller . '@store')->name('store');
});
