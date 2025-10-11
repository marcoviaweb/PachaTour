<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Health check route to test the API
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Pacha Tour API is running',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// Test routes for feature structure
Route::get('/departments', function () {
    try {
        $departmentService = new \App\Features\Departments\Services\DepartmentService();
        $departments = $departmentService->getAllDepartments();
        
        return response()->json([
            'success' => true,
            'message' => 'Departamentos obtenidos correctamente',
            'data' => $departments
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/departments/{slug}', function ($slug) {
    try {
        $departmentService = new \App\Features\Departments\Services\DepartmentService();
        $department = $departmentService->getDepartmentBySlug($slug);
        
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => "Departamento {$slug} encontrado",
            'data' => $department
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});