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
        Route::get('/{chat?}', [ChatController::class, 'show'])->name('show');

        Route::prefix('/{chat}')->name('messages.')->group(function () {
            Route::get('/messages', [MessageController::class, 'index'])->name('index');
            Route::post('/messages', [MessageController::class, 'store'])->name('store');
            Route::post('/read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/store', [MessageController::class, 'store'])->name('store');
        });
    });

    require __DIR__ . '/settings.php';
});
require __DIR__ . '/auth.php';
