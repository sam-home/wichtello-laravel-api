<?php

use Group\Models\Group;
use Illuminate\Support\Facades\Route;
use Group\Controllers\GroupController;

Route::model('group', Group::class);

Route::middleware('auth.basic')->prefix('groups')->name('groups.')->group(function () {
    $controller = GroupController::class;

    Route::get('', $controller . '@index')->name('index');
    Route::get('{group}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{group}', $controller . '@update')->name('update');
    Route::delete('{group}', $controller . '@destroy')->name('destroy');

    Route::get('{group}/partner', $controller . '@partner')->name('partner');
});

Route::middleware('auth.basic')->prefix('users/me')->name('users.me.')->group(function () {
    $controller = GroupController::class;

    Route::get('invites', $controller . '@invites')->name('invites');
});
