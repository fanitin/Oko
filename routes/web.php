<?php

use App\Http\Controllers\Pages\HomeController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Profile\SettingsController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::prefix('/profile')->controller(ProfileController::class)->name('profile.')->group(function () {
        Route::get('/', 'index')->name('index');

        Route::prefix('/settings')->controller(SettingsController::class)->name('settings.')->group(function () {
            Route::post('/upload-avatar', 'uploadAvatar')->name('upload-avatar');
        });
    });

    require __DIR__.'/settings.php';
});
require __DIR__.'/auth.php';
