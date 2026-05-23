<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\SubmissionController as StudentSubmission;
use App\Http\Controllers\Professor\DashboardController as ProfessorDashboard;
use App\Http\Controllers\Professor\ExamController;
use App\Http\Controllers\Professor\SubmissionController as ProfessorSubmission;
use App\Http\Controllers\Professor\AnalysisController;

// Auth
Route::get('/', function () { return redirect()->route('login'); });
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Espace Étudiant
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    Route::get('/submit/{exam}', [StudentSubmission::class, 'create'])->name('submission.create');
    Route::post('/submit', [StudentSubmission::class, 'store'])->name('submission.store');
    Route::get('/history', [StudentSubmission::class, 'history'])->name('submission.history');
});

// Espace Professeur
Route::middleware(['auth', 'role:professor'])->prefix('professor')->name('professor.')->group(function () {
    Route::get('/dashboard', [ProfessorDashboard::class, 'index'])->name('dashboard');

    // Examens
    Route::get('/exam/create', [ExamController::class, 'create'])->name('exam.create');
    Route::post('/exam', [ExamController::class, 'store'])->name('exam.store');
    Route::get('/exam/{exam}', [ExamController::class, 'show'])->name('exam.show');
    Route::get('/exam/{exam}/edit', [ExamController::class, 'edit'])->name('exam.edit');
    Route::put('/exam/{exam}', [ExamController::class, 'update'])->name('exam.update');
    Route::delete('/exam/{exam}', [ExamController::class, 'destroy'])->name('exam.destroy');

    // Soumissions
    Route::get('/exam/{exam}/submissions', [ProfessorSubmission::class, 'index'])->name('submissions.index');
    Route::get('/submission/{submission}', [ProfessorSubmission::class, 'show'])->name('submission.show');

    // Analyse
    Route::post('/exam/{exam}/analyze', [AnalysisController::class, 'launch'])->name('analysis.launch');
    Route::get('/exam/{exam}/results', [AnalysisController::class, 'results'])->name('analysis.results');
    Route::get('/exam/{exam}/compare/{submissionA}/{submissionB}', [AnalysisController::class, 'compare'])
        ->name('analysis.compare');
});