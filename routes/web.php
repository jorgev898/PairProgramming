<?php

use App\Http\Controllers\SessionController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class , 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class , 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class , 'login']);
    Route::get('/register', [RegisterController::class , 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class , 'register']);
});

Route::post('/logout', [LoginController::class , 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/challenges', [ChallengeController::class , 'index'])->name('challenges.index');
    Route::get('/challenges/{id}', [ChallengeController::class , 'show'])->name('challenges.show');
    Route::get('/challenges/{id}/lesson/{lesson}', [ChallengeController::class , 'lesson'])->name('challenges.lesson');
    Route::post('/challenges/{id}/enroll', [ChallengeController::class , 'enroll'])->name('challenges.enroll');
    Route::post('/challenges/{id}/unenroll', [ChallengeController::class , 'unenroll'])->name('challenges.unenroll');
    
    // Teacher course management
    Route::resource('courses', App\Http\Controllers\CourseController::class)->except(['show']);
    Route::get('/lessons/{id}/edit', [App\Http\Controllers\LessonController::class, 'edit'])->name('lessons.edit');
    Route::put('/lessons/{id}/content', [App\Http\Controllers\LessonController::class, 'updateContent'])->name('lessons.updateContent');
    Route::get('/app', [SessionController::class , 'index'])->name('pair.index');
    Route::post('/session/create', [SessionController::class , 'create'])->name('pair.create');
    Route::post('/session/join', [SessionController::class , 'join'])->name('pair.join');

    Route::match (['post', 'patch'], '/room/{code}/swap', [SessionController::class , 'swap'])->name('pair.swap');
    Route::get('/room/{code}', [SessionController::class , 'room'])->name('pair.room');

    // Editor & execution endpoints
    Route::post('/room/{code}/code', [SessionController::class , 'saveCode'])->name('pair.code.save');
    Route::post('/room/{code}/run', [SessionController::class , 'executeCode'])->name('pair.code.run');
    Route::post('/room/{code}/preview', [SessionController::class , 'previewCode'])->name('pair.code.preview');
    Route::post('/room/{code}/cursor', [SessionController::class , 'saveCursor'])->name('pair.cursor.save');

    // Polling & chat
    Route::get('/room/{code}/poll', [SessionController::class , 'poll'])->name('pair.poll');
    Route::get('/room/{code}/cursors', [SessionController::class , 'pollCursors'])->name('pair.cursors');
    Route::post('/room/{code}/thread', [SessionController::class , 'saveThread'])->name('pair.thread');
    Route::post('/room/{code}/chat', [SessionController::class , 'saveChat'])->name('pair.chat.save');
    Route::post('/room/{code}/participant-chat', [SessionController::class , 'saveParticipantChat'])->name('pair.participant-chat.save');
    Route::get('/room/{code}/chat', [SessionController::class , 'loadChat'])->name('pair.chat.load');

    // Teacher grading system
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/{courseId}', [GradeController::class, 'show'])->name('grades.show');
    Route::post('/grades/store', [GradeController::class, 'store'])->name('grades.store');
    Route::post('/grades/{id}/sync', [GradeController::class, 'sync'])->name('grades.sync');
    Route::post('/grades/sync-all/{courseId}', [GradeController::class, 'syncAll'])->name('grades.syncAll');
});

Route::get('/seed-mvvm-secret-123', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\MvvmCourseSeeder',
        '--force' => true
    ]);
    return '¡Seeder de MVVM ejecutado con exito en produccion!';
});

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'es'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');
