<?php

use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SessionController::class, 'index'])->name('pair.index');
Route::post('/session/create', [SessionController::class, 'create'])->name('pair.create');
Route::post('/session/join', [SessionController::class, 'join'])->name('pair.join');

Route::get('/room/{code}/poll',    [SessionController::class, 'poll'])->name('pair.poll');
Route::post('/room/{code}/thread', [SessionController::class, 'saveThread'])->name('pair.thread');
Route::post('/room/{code}/chat',   [SessionController::class, 'saveChat'])->name('pair.chat.save');
Route::get('/room/{code}/chat',    [SessionController::class, 'loadChat'])->name('pair.chat.load');

Route::match(['post','patch'], '/room/{code}/swap', [SessionController::class, 'swap'])->name('pair.swap');
Route::get('/room/{code}', [SessionController::class, 'room'])->name('pair.room');