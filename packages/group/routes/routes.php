<?php

use Group\Models\Group;
use Group\Models\GroupUser;
use Illuminate\Support\Facades\Route;
use Group\Controllers\GroupController;
use User\Models\User;

Route::model('user', User::class);
Route::model('group', Group::class);
Route::model('groupUser', GroupUser::class);

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
    Route::get('{group}/users/{user}', $controller . '@getUser')->name('users.get');
    Route::put('{group}/users/{user}', $controller . '@updateUser')->name('users.update');
    Route::delete('{group}/users/{user}', $controller . '@removeUser')->name('users.remove');

    Route::post('{group}/start', $controller . '@start')->name('start');
    Route::post('{group}/end', $controller . '@end')->name('end');
    Route::post('{group}/reset', $controller . '@reset')->name('reset');

    Route::post('{group}/code', $controller . '@generateCode')->name('generate.code');
    Route::delete('{group}/code', $controller . '@resetCode')->name('reset.code');
    Route::post('{group}/leave', $controller . '@leave')->name('leave');
    Route::post('{group}/accept', $controller . '@accept')->name('accept');
    Route::post('{group}/deny', $controller . '@deny')->name('deny');

    Route::post('{group}/invites', $controller . '@inviteUser')->name('users.invites.add');
    Route::delete('{group}/invites/{groupUser}', $controller . '@removeInvite')->name('users.invites.remove');
});

Route::middleware('auth.basic')->prefix('users')->name('users.')->group(function () {
    $controller = GroupController::class;

    Route::post('join', $controller . '@join')->name('join');
});

Route::middleware('auth.basic')->prefix('users/me')->name('users.me.')->group(function () {
    $controller = GroupController::class;

    Route::get('invites', $controller . '@userInvites')->name('userInvites');
});
