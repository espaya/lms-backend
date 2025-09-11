<?php

use App\Http\Controllers\Api\Admin\ExportReportController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\QuestionManagerController;
use App\Http\Controllers\API\Admin\UserController;
use App\Http\Controllers\API\Student\StudentController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AttendanceTardinessController;
use App\Http\Controllers\ConfidentialityOfInformation;
use App\Http\Controllers\CriminalHistorySearch;
use App\Http\Controllers\DisclaimerWaiverLiabilityController;
use App\Http\Controllers\DrugTestingPolicyController;
use App\Http\Controllers\EmployeeConductController;
use App\Http\Controllers\EmployeeDressCodeController;
use App\Http\Controllers\EmployeeOrientationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
// Route::get('/view-question-file/{filename}', function(){
//     return "Hello World!";
// });

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/admin/dashboard/get-subjects', [QuestionManagerController::class, 'index']); //
    Route::get('/topics/{topic}/questions', [QuestionManagerController::class, 'getQuestions']);
    Route::get('/answers/summary/{topicId}', [QuestionManagerController::class, 'summary']);
    Route::get('/answers/all/{topic}', [QuestionManagerController::class, 'showByTopic']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin']));
    // Add other API endpoints here
    Route::post('/admin/dashboard/upload-questions', [QuestionManagerController::class, 'store']);
    Route::post('/admin/dashboard/upload-questions/update/{id}', [QuestionManagerController::class, 'update']);
    Route::get('/subjects', [QuestionManagerController::class, 'getSubject']);
    Route::get('/topics', [QuestionManagerController::class, 'getTopic']);
    Route::get('/topics/single/{id}', [QuestionManagerController::class, 'getTopicById']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/add', [UserController::class, 'store']);
    Route::post('/users/update/{id}', [UserController::class, 'updateUser']);
    Route::get('/users/single/{username}', [UserController::class, 'view']);
    Route::get('/users/single/{username}/quizzes', [UserController::class, 'getUserAnswers']);
    Route::delete('delete-topic-subject-question/{id}', [QuestionManagerController::class, 'destroy']);
    // get report by topic id
    Route::get('/get-report-by-topic/{id}', [QuestionManagerController::class, 'getReportByTopic']);

    Route::post('/export-pdf', [ExportReportController::class, 'exportPDF']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::get('/user/dashboard', fn() => response()->json(['message' => 'Welcome User']));
    // Add other API endpoints here;
    Route::post('/answers', [QuestionManagerController::class, 'storeAnswer']);
    Route::post('/user/update-my-profile/{id}', [StudentController::class, 'update']);
    Route::get('/user/my-profile/{username}', [StudentController::class, 'index']);
    Route::get('/user/is-answered/{topic_id}', [QuestionManagerController::class, 'isAnswered']);

    // forms route
    Route::post('/user/application-forms', [ApplicationController::class, 'store']);
    Route::get('/user/application-forms/get', [ApplicationController::class, 'index']);

    Route::get('/user/attendance-forms/get', [AttendanceTardinessController::class, 'index']);
    Route::post('/user/attendance-forms', [AttendanceTardinessController::class, 'store']);

    Route::get('/user/confidentiality-forms/get', [ConfidentialityOfInformation::class, 'index']);
    Route::post('/user/confidentiality-forms', [ConfidentialityOfInformation::class, 'store']);

    Route::get('/user/criminal-history-search-forms/get', [CriminalHistorySearch::class, 'index']);
    Route::post('/user/criminal-history-search-forms', [CriminalHistorySearch::class, 'store']);

    Route::get('/user/disclaimer-forms/get', [DisclaimerWaiverLiabilityController::class, 'index']);
    Route::post('/user/disclaimer-forms', [DisclaimerWaiverLiabilityController::class, 'store']);

    Route::get('/user/drug-testing-policy-forms/get', [DrugTestingPolicyController::class, 'index']);
    Route::post('/user/drug-testing-policy-forms', [DrugTestingPolicyController::class, 'store']);

    Route::get('/user/employee-conduct-controller-forms/get', [EmployeeConductController::class, 'index']);
    Route::post('/user/employee-conduct-controller-forms', [EmployeeConductController::class, 'store']);

    Route::get('/user/employee-dress-code-form/get', [EmployeeDressCodeController::class, 'index']);
    Route::post('/user/employee-dress-code-form', [EmployeeDressCodeController::class, 'store']);

    Route::get('/user/employee-orientation-form/get', [EmployeeOrientationController::class, 'index']);
    Route::post('/user/employee-orientation-form', [EmployeeOrientationController::class, 'store']);
});
