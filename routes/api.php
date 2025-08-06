<?php

use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Example protected route group
// Authentication routes
// Route::prefix('auth')->group(function () {
    
// });

Route::get('/csrf-cookie', fn() => response()->json(['csrf' => true]));
Route::post('/login', [AuthController::class, 'login']);

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Add other API endpoints here
    Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin']));
    Route::get('/user/dashboard', fn() => response()->json(['message' => 'Welcome User']));
});

// Route::middleware(['auth:sanctum', 'admin'])->group(function () {
//     Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin']));
// });

// Route::middleware(['auth:sanctum', 'user'])->group(function () {
//     Route::get('/user/dashboard', fn() => response()->json(['message' => 'Welcome User']));
// });
