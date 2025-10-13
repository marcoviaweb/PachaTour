<?php

use Illuminate\Support\Facades\Route;
use App\Features\Admin\Controllers\AdminController;
use App\Features\Admin\Controllers\ReportController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta súper simple para verificar que Laravel funciona
Route::get('/simple', function () {
    return 'Pacha Tour - Laravel funcionando! Fecha: ' . date('Y-m-d H:i:s');
});

// Test route básico
Route::get('/test', function () {
    return response()->json([
        'message' => 'Pacha Tour Laravel está funcionando correctamente!',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'status' => 'OK'
    ]);
});

// Test departments API
Route::get('/test-departments', function () {
    try {
        $departments = \App\Models\Department::all();
        return response()->json([
            'status' => 'success',
            'count' => $departments->count(),
            'departments' => $departments->take(3)
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Test attractions API
Route::get('/test-attractions', function () {
    try {
        $attractions = \App\Models\Attraction::all();
        $featured = \App\Models\Attraction::where('is_featured', true)->count();
        return response()->json([
            'status' => 'success',
            'total_count' => $attractions->count(),
            'featured_count' => $featured,
            'sample_attractions' => $attractions->take(3)
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Ruta de prueba directa para reviews
Route::get('/test-reviews-direct', function () {
    try {
        $userId = 24; // Juan Pérez
        $reviews = \App\Models\Review::where('user_id', $userId)
            ->with(['reviewable'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $mappedReviews = $reviews->map(function ($review) {
            $reviewable = $review->reviewable;
            
            return [
                'id' => $review->id,
                'rating' => (float) $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'status_name' => ucfirst($review->status),
                'attraction_name' => $reviewable?->name ?? 'Atracción no disponible',
                'attraction_slug' => $reviewable?->slug,
                'department_name' => $reviewable?->department?->name,
                'helpful_count' => $review->helpful_votes ?? 0,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ];
        });
        
        return response()->json(['data' => $mappedReviews]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Ruta principal usando Inertia.js y Vue.js
// Authentication routes (web views)
Route::get('/login', function () {
    return \Inertia\Inertia::render('Auth/Login');
})->name('login')->middleware('guest');

Route::get('/register', function () {
    return \Inertia\Inertia::render('Auth/Register');
})->name('register')->middleware('guest');

Route::get('/', function () {
    return \Inertia\Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Search page route
Route::get('/buscar', function () {
    return \Inertia\Inertia::render('Search');
})->name('search');

// Department routes
Route::get('/departamentos', function () {
    return \Inertia\Inertia::render('Departments/Index');
})->name('departments.index');

Route::get('/departamentos/{slug}', function ($slug) {
    return \Inertia\Inertia::render('Departments/Show', [
        'departmentSlug' => $slug
    ]);
})->name('departments.show');

// Attraction routes
Route::get('/atractivos', function () {
    return \Inertia\Inertia::render('Attractions/Index');
})->name('attractions.index');

Route::get('/atractivos/{slug}', function ($slug) {
    try {
        $attraction = \App\Models\Attraction::where('slug', $slug)
            ->with([
                'department',
                'media' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('created_at');
                },
                'tours.schedules',
                'reviews' => function ($query) {
                    $query->with('user:id,name')
                        ->where('status', 'approved')
                        ->latest()
                        ->limit(10);
                }
            ])
            ->firstOrFail();

        // Increment visit count
        $attraction->increment('visits_count');

        return \Inertia\Inertia::render('Attractions/Show', [
            'attraction' => $attraction
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Atractivo no encontrado');
    }
})->name('attractions.show');

// User Dashboard route (requires authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/mis-viajes', function () {
        return \Inertia\Inertia::render('User/Dashboard');
    })->name('user.dashboard');
    
    Route::get('/perfil', function () {
        $user = Auth::user();
        return \Inertia\Inertia::render('User/Profile', [
            'user' => $user
        ]);
    })->name('user.profile');
    
    // Planning route using web authentication
    Route::post('/planificar-visita', function (\Illuminate\Http\Request $request) {
        $controller = new \App\Features\Tours\Controllers\BookingController(
            new \App\Features\Tours\Services\BookingService()
        );
        
        return $controller->storePlanning($request);
    })->name('planning.store');
});

// Temporary test route for profile (without authentication)
Route::get('/test-perfil', function () {
    return \Inertia\Inertia::render('User/Profile');
})->name('test.profile');

// Ruta para probar base de datos directamente
Route::get('/test-db', function () {
    try {
        $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=pacha_tour_db', 'postgres', $_ENV['DB_PASSWORD'] ?? '');
        
        // Obtener información de las tablas principales
        $tables = $pdo->query("
            SELECT 
                table_name,
                (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) as column_count
            FROM information_schema.tables t 
            WHERE table_schema = 'public' 
            AND table_name NOT IN ('migrations', 'personal_access_tokens', 'test_connection')
            ORDER BY table_name
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Base de datos Pacha Tour configurada correctamente',
            'tables_count' => count($tables),
            'main_tables' => $tables,
            'features' => [
                'departments' => 'Gestión de 9 departamentos de Bolivia',
                'attractions' => 'Atractivos turísticos con geolocalización',
                'tours' => 'Tours con horarios y reservas',
                'users' => 'Sistema de usuarios con roles',
                'bookings' => 'Sistema de reservas y pagos',
                'reviews' => 'Sistema de reseñas y calificaciones',
                'media' => 'Gestión de archivos multimedia'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Ruta para probar modelos y relaciones Eloquent
Route::get('/test-models', function () {
    try {
        $results = [];
        
        // Probar creación de modelos
        $models = [
            'Department' => new App\Models\Department(),
            'Attraction' => new App\Models\Attraction(),
            'Tour' => new App\Models\Tour(),
            'TourSchedule' => new App\Models\TourSchedule(),
            'Booking' => new App\Models\Booking(),
            'Review' => new App\Models\Review(),
            'Media' => new App\Models\Media(),
            'User' => new App\Models\User(),
        ];
        
        foreach ($models as $name => $model) {
            $results['models'][$name] = 'OK';
        }
        
        // Probar relaciones (definición, no ejecución)
        $department = new App\Models\Department();
        $results['relations']['Department->attractions'] = method_exists($department, 'attractions');
        $results['relations']['Department->media'] = method_exists($department, 'media');
        
        $attraction = new App\Models\Attraction();
        $results['relations']['Attraction->department'] = method_exists($attraction, 'department');
        $results['relations']['Attraction->tours'] = method_exists($attraction, 'tours');
        $results['relations']['Attraction->reviews'] = method_exists($attraction, 'reviews');
        
        $tour = new App\Models\Tour();
        $results['relations']['Tour->schedules'] = method_exists($tour, 'schedules');
        $results['relations']['Tour->attractions'] = method_exists($tour, 'attractions');
        $results['relations']['Tour->bookings'] = method_exists($tour, 'bookings');
        
        $booking = new App\Models\Booking();
        $results['relations']['Booking->user'] = method_exists($booking, 'user');
        $results['relations']['Booking->tourSchedule'] = method_exists($booking, 'tourSchedule');
        
        // Probar constantes
        $results['constants'] = [
            'attraction_types' => count(App\Models\Attraction::TYPES),
            'tour_types' => count(App\Models\Tour::TYPES),
            'booking_statuses' => count(App\Models\Booking::STATUSES),
            'review_statuses' => count(App\Models\Review::STATUSES),
            'media_types' => count(App\Models\Media::TYPES),
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'Modelos Eloquent funcionando correctamente',
            'models_count' => count($models),
            'relations_working' => array_sum($results['relations']),
            'relations_total' => count($results['relations']),
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [AdminController::class, 'overview'])->name('overview');
    
    // Report routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/attractions', [ReportController::class, 'attractions'])->name('attractions');
        Route::get('/commissions', [ReportController::class, 'commissions'])->name('commissions');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
});

require __DIR__.'/auth.php';

// Debug route for admin dashboard
Route::get('/debug-admin', function () {
    try {
        $controller = new \App\Features\Admin\Controllers\AdminController();
        return $controller->dashboard();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});