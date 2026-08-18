<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TopicController;
use App\Http\Middleware\AutoLoginLocalUser;
use Illuminate\Support\Facades\Route;

// Local/single-user app (PRD FR14) — there's no one to market to, so '/' skips
// the stock starter-kit marketing page and goes straight into the app.
Route::redirect('/', '/topics')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// This app's own features are local/single-user and need no login (PRD FR14) — auto-authenticate
// here only, so the starter kit's real login/register/reset-password flows stay untouched.
Route::middleware([AutoLoginLocalUser::class])->group(function () {
    Route::get('topics', [TopicController::class, 'index'])->name('topics.index');
    Route::get('topics/{topic:slug}/quiz', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('questions/{question}/attempts', [AttemptController::class, 'store'])->name('attempts.store');
    Route::post('questions/{question}/attempts/{attempt}/feedback', [AttemptController::class, 'feedback'])->name('attempts.feedback');
    Route::post('questions/{question}/run', [QuizController::class, 'run'])->name('quiz.run');
    Route::get('questions/{question}/back', [QuizController::class, 'back'])->name('quiz.back');
    Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');

    Route::get('questions/manage', [QuestionController::class, 'index'])->name('questions.manage');
    Route::post('questions/generate', [QuestionController::class, 'generate'])->name('questions.generate');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::post('questions/verify', [QuestionController::class, 'verify'])->name('questions.verify');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    // Literal /bulk routes must come before the {question} wildcard routes below, or a
    // request to /questions/bulk would match questions/{question} with id "bulk" instead.
    Route::patch('questions/bulk/approve', [QuestionController::class, 'bulkApprove'])->name('questions.bulk-approve');
    Route::delete('questions/bulk', [QuestionController::class, 'bulkReject'])->name('questions.bulk-destroy');
    Route::patch('questions/{question}/approve', [QuestionController::class, 'approve'])->name('questions.approve');
    Route::patch('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
