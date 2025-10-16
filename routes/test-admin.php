<?php

// Temporal test route for admin dashboard
Route::get('/test-admin', function () {
    return response()->json([
        'message' => 'Admin dashboard test working!',
        'user' => auth()->user(),
        'departments' => \App\Features\Departments\Models\Department::count(),
        'attractions' => \App\Features\Attractions\Models\Attraction::count(),
        'users' => \App\Models\User::count(),
    ]);
})->middleware(['auth']);