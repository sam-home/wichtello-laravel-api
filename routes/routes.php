<?php

/*use App\Models\Group;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Task;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Support\Facades\Route;

Route::model('group', Group::class);
Route::model('task', Task::class);
Route::model('poll', Poll::class);
Route::model('wish', Wish::class);
Route::model('option', PollOption::class);
Route::model('user', User::class);


Route::group([], function () {
    $controller = \App\Http\Controllers\AppController::class;

    Route::get('', ['as' => 'home', 'uses' => $controller . '@home']);
    Route::get('impressum', ['as' => 'impressum', 'uses' => $controller . '@impressum']);
    Route::get('agb', ['as' => 'agb', 'uses' => $controller . '@agb']);
    Route::get('datenschutz', ['as' => 'datenschutz', 'uses' => $controller . '@datenschutz']);
});

Route::group([], function () {
    $controller = \App\Http\Controllers\GroupController::class;

    Route::get('join/{hash}', ['as' => 'join.show', 'uses' => $controller . '@showJoin'])->where('hash', '[a-z0-9]+');
    Route::post('join', ['as' => 'join', 'uses' => $controller . '@join']);
});

// User
Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    $controller = \App\Http\Controllers\UserController::class;

    Route::post('register', ['as' => 'register', 'uses' => $controller. '@register']);
    Route::get('register/confirm', ['as' => 'register.confirm', 'uses' => $controller. '@registerConfirm']);
    Route::post('forget_password', ['as' => 'forget_password', 'uses' => $controller. '@forgetPassword']);
    Route::get('forget_password/confirm', ['as' => 'forget_password.confirm', 'uses' => $controller. '@forgetPasswordConfirm']);
});

Route::group(['middleware' => ['auth.basic', 'user-active']], function () {
    // User
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        $controller = \App\Http\Controllers\UserController::class;

        Route::post('authenticate', ['as' => 'authenticate', 'uses' => $controller . '@authenticate']);
        Route::put('', ['as' => 'update', 'uses' => $controller . '@update']);
    });

    $controller = \App\Http\Controllers\BroadcastController::class;

    Route::get('broadcasts', ['as' => 'broadcasts.index', 'uses' => $controller . '@index']);

    // Groups

    Route::group(['prefix' => 'groups', 'as' => 'groups.'], function () {
        $controller = \App\Http\Controllers\GroupController::class;

        Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
        Route::post('', ['as' => 'store', 'uses' => $controller . '@store']);

        Route::group(['prefix' => '{group}'], function () {
            $controller = \App\Http\Controllers\GroupController::class;

            Route::get('', ['as' => 'single', 'uses' => $controller . '@single']);
            Route::put('', ['as' => 'update', 'uses' => $controller . '@update']);
            Route::get('users', ['as' => 'users', 'uses' => $controller . '@users']);
            Route::get('users/{user}', ['as' => 'user', 'uses' => $controller . '@user']);
            Route::post('admin/{user}/set', ['as' => 'admin.set', 'uses' => $controller . '@setAdmin']);
            Route::post('admin/{user}/reset', ['as' => 'admin.reset', 'uses' => $controller . '@resetAdmin']);
            Route::get('partner', ['as' => 'partner', 'uses' => $controller . '@partner']);
            Route::post('toggle_join', ['as' => 'toggle_join', 'uses' => $controller . '@toggleJoin']);
            Route::post('invite', ['as' => 'invite', 'uses' => $controller . '@invite']);
            Route::post('leave', ['as' => 'leave', 'uses' => $controller . '@leave']);
            Route::post('accept', ['as' => 'accept', 'uses' => $controller . '@accept']);
            Route::post('start', ['as' => 'start', 'uses' => $controller . '@start']);
            Route::post('stop', ['as' => 'stop', 'uses' => $controller . '@stop']);
            Route::post('decline', ['as' => 'decline', 'uses' => $controller . '@decline']);
            Route::post('kick_user/{user}', ['as' => 'kick_user', 'uses' => $controller . '@kickUser']);
            Route::delete('', ['as' => 'delete', 'uses' => $controller . '@delete']);

            // Messages

            Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
                $controller = \App\Http\Controllers\MessageController::class;

                Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
                Route::post('', ['as' => 'store', 'uses' => $controller . '@store']);
            });

            // Tasks

            Route::group(['prefix' => 'tasks', 'as' => 'tasks.'], function () {
                $controller = \App\Http\Controllers\TaskController::class;

                Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
                Route::get('{task}', ['as' => 'get', 'uses' => $controller . '@get']);
                Route::post('', ['as' => 'store', 'uses' => $controller . '@store']);
                Route::put('{task}', ['as' => 'update', 'uses' => $controller . '@update']);
                Route::delete('{task}', ['as' => 'remove', 'uses' => $controller . '@remove']);
                Route::post('{task}/sign-in', ['as' => 'sign-in', 'uses' => $controller . '@signIn']);
                Route::post('{task}/sign-out', ['as' => 'sign-out', 'uses' => $controller . '@signOut']);
            });

            // Polls

            Route::group(['prefix' => 'polls', 'as' => 'polls.'], function () {
                $controller = \App\Http\Controllers\PollController::class;

                Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
                Route::get('{poll}', ['as' => 'get', 'uses' => $controller . '@get']);
                Route::put('{poll}', ['as' => 'update', 'uses' => $controller . '@update']);
                Route::post('', ['as' => 'store', 'uses' => $controller . '@store']);
                Route::post('{poll}/vote/{option}', ['as' => 'vote', 'uses' => $controller . '@vote']);
                Route::post('{poll}/reset', ['as' => 'reset', 'uses' => $controller . '@reset']);
                Route::delete('{poll}', ['as' => 'remove', 'uses' => $controller . '@remove']);
            });

            // Wishes

            Route::group(['prefix' => 'wishes', 'as' => 'wishes.'], function () {
                $controller = \App\Http\Controllers\WishController::class;

                Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
                Route::get('{wish}', ['as' => 'get', 'uses' => $controller . '@get']);
                Route::post('', ['as' => 'store', 'uses' => $controller . '@store']);
                Route::put('{wish}', ['as' => 'update', 'uses' => $controller . '@update']);
                Route::delete('{wish}', ['as' => 'remove', 'uses' => $controller . '@remove']);
            });

        });
    });

    // Events

    Route::group(['prefix' => 'events', 'as' => 'events.'], function () {
        $controller = \App\Http\Controllers\EventController::class;

        Route::get('', ['as' => 'index', 'uses' => $controller . '@index']);
        Route::get('long-polling/{event_id}', ['as' => 'long-polling', 'uses' => $controller . '@longPolling'])->where('event_id', '[0-9]+');
        Route::get('polling/{event_id}', ['as' => 'polling', 'uses' => $controller . '@polling'])->where('event_id', '[0-9]+');
        Route::get('last', ['as' => 'last', 'uses' => $controller . '@getLast']);
    });
});

// Route::get('events/server-sent-events/{event_id}', ['as' => 'events.server-sent-events', 'uses' => 'EventController@serverSentEvents'])->where('event_id', '[0-9]+');*/
