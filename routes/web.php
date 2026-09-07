<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LearningController;

Route::get('/', function () {
    return view('welcome');
});

// 1. หน้าเลือกภาษา / คอร์ส
Route::get('/languages', [LearningController::class, 'indexLanguages']);

// 2. หน้าเลือก Unit ของแต่ละ Course
Route::get('/courses/{course}/units', [LearningController::class, 'showUnits']);

// 3. หน้าเลือก Lesson และเนื้อหาการเรียน
Route::get('/units/{unit}/lessons', [LearningController::class, 'showLessons']);
Route::get('/lessons/{lesson}', [LearningController::class, 'showLessonContent']);