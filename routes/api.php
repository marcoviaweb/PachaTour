<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Features\Departments\Controllers\DepartmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Test routes for feature structure
Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{slug}', [DepartmentController::class, 'show']);
});

// Health check route to test the API
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Pacha Tour API is running',
        'timestamp' => now()->toISOString()
    ]);
});