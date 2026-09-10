<?php

use App\Http\Controllers\QuizController;
use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

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


Route::get('/dev/languages', [FrontendController::class, 'languages']);
Route::get('/dev/lessons', [FrontendController::class, 'lessons']);

// Route เพิ่มเติมสำหรับ Flow การเรียนรู้
Route::get('/dev/lesson/{id}', [FrontendController::class, 'lessonShow']);
Route::get('/dev/quiz/{id}', [FrontendController::class, 'quizShow']);