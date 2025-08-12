<?php

use App\Http\Controllers\QuestionManagerController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/view-question-file/{filename}', [QuestionManagerController::class, 'viewQuestionFile']);
Route::get('/view-answer-signature/{singature}', [QuestionManagerController::class, 'viewAnswerSignature']);
