<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TopicController;
use App\Http\Middleware\AutoLoginLocalUser;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// This app's own features are local/single-user and need no login (PRD FR14) — auto-authenticate
// here only, so the starter kit's real login/register/reset-password flows stay untouched.
Route::middleware([AutoLoginLocalUser::class])->group(function () {
    Route::get('topics', [TopicController::class, 'index'])->name('topics.index');
    Route::get('topics/{topic:slug}/quiz', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('questions/{question}/attempts', [AttemptController::class, 'store'])->name('attempts.store');
    Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');

    Route::get('questions/manage', [QuestionController::class, 'index'])->name('questions.manage');
    Route::post('questions/generate', [QuestionController::class, 'generate'])->name('questions.generate');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::patch('questions/{question}/approve', [QuestionController::class, 'approve'])->name('questions.approve');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
