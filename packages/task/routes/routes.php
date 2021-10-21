<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Task\Controllers\TaskController;
use Task\Models\Task;

Route::model('group', Group::class);
Route::model('task', Task::class);

Route::middleware('auth.basic')->prefix('groups/{group}/tasks')->name('group.tasks.')->group(function () {
    $controller = TaskController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{task}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{task}', $controller . '@update')->name('update');
    Route::delete('{task}', $controller . '@destroy')->name('destroy');

    Route::post('{task}/join', $controller . '@join')->name('join');
    Route::post('{task}/leave', $controller . '@leave')->name('leave');
});
