<?php

use Illuminate\Support\Facades\Route;
use User\Controllers\UserController;
use User\Models\User;


Route::model('group', User::class);

Route::middleware('auth.basic')->prefix('users')->name('user.')->group(function () {
    $controller = UserController::class;

    Route::get('me', $controller . '@me')->name('me');

    Route::post('authenticate', ['as' => 'authenticate', 'uses' => $controller . '@authenticate']);
    Route::post('register', ['as' => 'register', 'uses' => $controller . '@register']);
    Route::get('register-confirm', ['as' => 'register-confirm', 'uses' => $controller . '@registerConfirm']);
    Route::post('password-reset', ['as' => 'password-reset', 'uses' => $controller . '@forgetPassword']);
    Route::get('password-confirm', ['as' => 'password-confirm', 'uses' => $controller . '@forgetPasswordConfirm']);
    Route::post('premium', ['as' => 'premium', 'uses' => $controller . '@premium']);

    /*Route::get('', $controller . '@index')->name('index');
    Route::get('{user}', $controller . '@get')->name('get');
    Route::post('', $controller . '@store')->name('store');
    Route::put('{user}', $controller . '@update')->name('update');
    Route::delete('{user}', $controller . '@destroy')->name('destroy');*/
});
