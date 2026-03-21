<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', [SessionController::class, 'index'])->name('pair.index');
Route::post('/session/create', [SessionController::class, 'create'])->name('pair.create');
Route::post('/session/join', [SessionController::class, 'join'])->name('pair.join');

Route::get('/room/{code}/poll', [SessionController::class, 'poll'])->name('pair.poll');
Route::post('/room/{code}/thread', [SessionController::class, 'saveThread'])->name('pair.thread');
Route::post('/room/{code}/chat', [SessionController::class, 'saveChat'])->name('pair.chat.save');
Route::get('/room/{code}/chat', [SessionController::class, 'loadChat'])->name('pair.chat.load');

Route::match(['post', 'patch'], '/room/{code}/swap', [SessionController::class, 'swap'])->name('pair.swap');
Route::get('/room/{code}', [SessionController::class, 'room'])->name('pair.room');
