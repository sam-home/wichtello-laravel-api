<?php

use Illuminate\Support\Facades\Route;
use Sam\User\Controllers\UserController;

Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    $controller = UserController::class;

    Route::post('register', ['as' => 'register', 'uses' => $controller . '@register']);
    Route::get('register-confirm', ['as' => 'register-confirm', 'uses' => $controller . '@registerConfirm']);
    Route::post('password-reset', ['as' => 'password-reset', 'uses' => $controller . '@forgetPassword']);
    Route::get('password-confirm', ['as' => 'password-confirm', 'uses' => $controller . '@forgetPasswordConfirm']);
});
