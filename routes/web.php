<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/test-mail', function () {
    Mail::raw('Testowa wiadomość', function ($message) {
        $message->to('test@example.com')->subject('Test Mail');
    });

    return 'Mail wysłany!';
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
