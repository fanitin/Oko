<?php

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Pages\HomeController;
use App\Http\Controllers\Profile\OtherProfileController;
use App\Http\Controllers\Profile\SelfProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::prefix('/profile')->controller(SelfProfileController::class)->name('profile.')->group(function () {
        Route::get('', 'index')->name('index');

        Route::prefix('/settings')->name('settings.')->group(function () {
            Route::post('/upload-avatar', 'uploadAvatar')->name('upload-avatar');
            Route::post('/set-as-main', 'setAsMain')->name('set-as-main');
            Route::delete('/delete-avatar', 'deleteAvatar')->name('delete-avatar');
            Route::post('/update-username', 'updateUsername')->name('update-username');
        });
    });

    Route::prefix('/profile/{user}')->controller(OtherProfileController::class)->name('otherProfile.')->group(function () {
        Route::get('', 'index')->name('index');
    });

    Route::prefix('/chat')->name('chat.')->group(function () {
        Route::prefix('/{chat?}')->group(function () {
            Route::get('', [ChatController::class, 'show'])->name('show');
            Route::get('/toggle-pin', [ChatController::class, 'togglePin'])->name('toggle-pin');
            Route::get('/toggle-mute', [ChatController::class, 'toggleMute'])->name('toggle-mute');
        });

        Route::prefix('/{chat}')->name('messages.')->group(function () {
            Route::prefix('/messages')->group(function () {
                Route::get('', [MessageController::class, 'index'])->name('index');
                Route::post('', [MessageController::class, 'store'])->name('store');
                Route::post('/{message}/update', [MessageController::class, 'update'])->name('update');
                Route::delete('/{message}', [MessageController::class, 'delete'])->name('delete');
                Route::get('/{message}/context', [MessageController::class, 'context'])->name('context');
                Route::post('/read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
            });
        });
    });

    require __DIR__ . '/settings.php';
});
require __DIR__ . '/auth.php';
