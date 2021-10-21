<?php

use Illuminate\Support\Facades\Route;
use User\Controllers\UserController;
use User\Models\User;


Route::model('group', User::class);

Route::middleware('auth.basic')->prefix('users')->name('user.')->group(function () {
    $controller = UserController::class;

    Route::put('me', $controller . '@me')->name('me');
    Route::post('premium', $controller . '@premium')->name('premium');
});

Route::prefix('users')->name('user.')->group(function () {
    $controller = UserController::class;

    Route::post('authenticate', $controller . '@authenticate')->name('authenticate');
    Route::post('register', $controller . '@register')->name('register');
    Route::post('reset', $controller . '@reset')->name('reset');
    Route::post('change', $controller . '@change')->name('change');
    Route::post('verify', $controller . '@verify')->name('verify');
});
