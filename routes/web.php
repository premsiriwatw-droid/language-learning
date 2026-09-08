<?php

use App\Http\Controllers\QuizController;
use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Quiz
Route::get('/quiz/{exercise}', [QuizController::class, 'show'])
    ->name('quiz.show');

Route::post('/quiz/{exercise}', [QuizController::class, 'submit'])
    ->name('quiz.submit');

// Learning
Route::get('/languages', [LearningController::class, 'indexLanguages']);

Route::get('/courses/{course}/units', [LearningController::class, 'showUnits']);

Route::get('/units/{unit}/lessons', [LearningController::class, 'showLessons']);

Route::get('/lessons/{lesson}', [LearningController::class, 'showLessonContent']);