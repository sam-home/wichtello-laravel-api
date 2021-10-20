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
    Route::get('{group}/invites', $controller . '@invites')->name('invites');
    Route::get('{group}/users', $controller . '@users')->name('users');

    Route::post('{group}/start', $controller . '@start')->name('start');
    Route::post('{group}/end', $controller . '@end')->name('end');
    Route::post('{group}/reset', $controller . '@reset')->name('reset');

    Route::post('{group}/code', $controller . '@generateCode')->name('generate.code');
    Route::delete('{group}/code', $controller . '@resetCode')->name('reset.code');
});

Route::middleware('auth.basic')->prefix('users/me')->name('users.me.')->group(function () {
    $controller = GroupController::class;

    Route::get('invites', $controller . '@userInvites')->name('userInvites');
});
