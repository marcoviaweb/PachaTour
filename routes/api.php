<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Features\Users\Controllers\AuthController;
use App\Features\Users\Controllers\SocialAuthController;

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
        'timestamp' => now()->toISOString(),
        'server' => 'Laravel ' . app()->version()
    ]);
});

// Test endpoint para verificar que la API funciona
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now(),
        'user' => auth('web')->user() ? [
            'id' => auth('web')->user()->id,
            'name' => auth('web')->user()->name,
            'email' => auth('web')->user()->email
        ] : null
    ]);
});

// ENDPOINT TEMPORAL PARA REVIEWS SIN AUTENTICACIÓN
Route::get('/reviews-temp', function () {
    try {
        $userId = 24; // Juan Pérez
        $reviews = \App\Features\Reviews\Models\Review::where('user_id', $userId)
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
            'error' => $e->getMessage()
        ], 500);
    }
});

// Production API routes start here

// Temporary test routes for profile (without authentication)
Route::prefix('test')->group(function () {
    Route::get('/user/profile', function () {
        return response()->json([
            'user' => [
                'id' => 1,
                'name' => 'Usuario de Prueba',
                'last_name' => 'Apellido Test',
                'email' => 'test@example.com',
                'phone' => '+591 70123456',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'nationality' => 'Boliviana',
                'country' => 'Bolivia',
                'city' => 'La Paz',
                'preferred_language' => 'es',
                'bio' => 'Esta es una biografía de prueba para el usuario de test.',
                'newsletter_subscription' => true,
                'marketing_emails' => false,
                'created_at' => '2024-01-01T00:00:00.000000Z'
            ]
        ]);
    });
    
    Route::put('/user/profile', function () {
        return response()->json([
            'message' => 'Perfil actualizado exitosamente (modo test)',
            'user' => [
                'id' => 1,
                'name' => 'Usuario Actualizado',
                'email' => 'test@example.com'
            ]
        ]);
    });
    
    Route::post('/user/change-password', function () {
        return response()->json([
            'message' => 'Contraseña cambiada exitosamente (modo test)'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [SocialAuthController::class, 'socialLogin']);
});

// Protected authentication routes
Route::middleware(['auth:sanctum', 'auth.api'])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/link-social', [SocialAuthController::class, 'linkSocialAccount']);
    Route::delete('/unlink-social', [SocialAuthController::class, 'unlinkSocialAccount']);
});

/*
|--------------------------------------------------------------------------
| Department Routes
|--------------------------------------------------------------------------
*/

use App\Features\Departments\Controllers\DepartmentApiController;

// Public department routes
Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentApiController::class, 'index']);
    Route::get('/list', [DepartmentApiController::class, 'list']);
    Route::get('/{slug}', [DepartmentApiController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Attraction Routes
|--------------------------------------------------------------------------
*/

use App\Features\Attractions\Controllers\AttractionController;
use App\Features\Attractions\Controllers\AttractionApiController;
use App\Features\Attractions\Controllers\MediaController;
use App\Features\Search\Controllers\SearchController;
use App\Features\Search\Controllers\FilterController;

// Public attraction routes
Route::prefix('attractions')->group(function () {
    Route::get('/', [AttractionApiController::class, 'index']);
    Route::get('/featured', [AttractionApiController::class, 'featured']);
    Route::get('/search', [AttractionApiController::class, 'search']);
    Route::get('/types', [AttractionApiController::class, 'types']);
    Route::get('/department/{departmentSlug}', [AttractionApiController::class, 'byDepartment']);
    Route::get('/{slug}', [AttractionApiController::class, 'show']);
});

// Protected attraction management routes (Admin only) - COMMENTED OUT TO AVOID CONFLICTS WITH WEB ROUTES
/*
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/attractions')->group(function () {
        Route::get('/', [AttractionController::class, 'index']);
        Route::post('/', [AttractionController::class, 'store']);
        Route::get('/statistics', [AttractionController::class, 'statistics']);
        Route::get('/{attraction}', [AttractionController::class, 'show']);
        Route::put('/{attraction}', [AttractionController::class, 'update']);
        Route::delete('/{attraction}', [AttractionController::class, 'destroy']);
        Route::patch('/{attraction}/toggle-status', [AttractionController::class, 'toggleStatus']);
        Route::patch('/{attraction}/toggle-featured', [AttractionController::class, 'toggleFeatured']);
        
        // Media management routes
        Route::prefix('{attraction}/media')->group(function () {
            Route::get('/', [MediaController::class, 'index']);
            Route::post('/', [MediaController::class, 'store']);
            Route::get('/statistics', [MediaController::class, 'statistics']);
            Route::patch('/order', [MediaController::class, 'updateOrder']);
            Route::get('/{media}', [MediaController::class, 'show']);
            Route::put('/{media}', [MediaController::class, 'update']);
            Route::delete('/{media}', [MediaController::class, 'destroy']);
            Route::patch('/{media}/featured', [MediaController::class, 'setFeatured']);
        });
    });
});
*/

/*
|--------------------------------------------------------------------------
| Search Routes
|--------------------------------------------------------------------------
*/

// Public search routes
Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'search']);
    Route::get('/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/advanced', [SearchController::class, 'advancedSearch']);
    Route::get('/tourism-types', [SearchController::class, 'tourismTypes']);
    Route::get('/location', [SearchController::class, 'searchByLocation']);
});

/*
|--------------------------------------------------------------------------
| Filter Routes
|--------------------------------------------------------------------------
*/

// Public filter routes
Route::prefix('filters')->group(function () {
    Route::get('/', [FilterController::class, 'filter']);
    Route::get('/dynamic', [FilterController::class, 'dynamicFilter']);
    Route::get('/options', [FilterController::class, 'options']);
    Route::get('/price', [FilterController::class, 'filterByPrice']);
    Route::get('/rating', [FilterController::class, 'filterByRating']);
    Route::get('/distance', [FilterController::class, 'filterByDistance']);
    Route::get('/amenities', [FilterController::class, 'filterByAmenities']);
    Route::get('/difficulty', [FilterController::class, 'filterByDifficulty']);
});

/*
|--------------------------------------------------------------------------
| Tour Routes
|--------------------------------------------------------------------------
*/

use App\Features\Tours\Controllers\TourController;
use App\Features\Tours\Controllers\TourScheduleController;
use App\Features\Tours\Controllers\AvailabilityController;
use App\Features\Tours\Controllers\BookingController;

// Public tour routes
Route::prefix('tours')->group(function () {
    Route::get('/', [TourController::class, 'publicIndex']);
    Route::get('/{tour}', [TourController::class, 'show']);
    Route::get('/{tour}/availability/date', [AvailabilityController::class, 'checkDate']);
    Route::get('/{tour}/availability/range', [AvailabilityController::class, 'checkRange']);
    Route::get('/{tour}/availability/next', [AvailabilityController::class, 'nextAvailable']);
    Route::get('/{tour}/availability/spots', [AvailabilityController::class, 'checkSpots']);
    Route::get('/{tour}/availability/calendar', [AvailabilityController::class, 'calendar']);
    Route::post('/availability/multiple', [AvailabilityController::class, 'checkMultipleTours']);
});

// Protected tour management routes (Admin only)
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/tours')->group(function () {
        Route::get('/', [TourController::class, 'index']);
        Route::post('/', [TourController::class, 'store']);
        Route::get('/statistics', [TourController::class, 'statistics']);
        Route::get('/{tour}', [TourController::class, 'show']);
        Route::put('/{tour}', [TourController::class, 'update']);
        Route::delete('/{tour}', [TourController::class, 'destroy']);
        Route::patch('/{tour}/toggle-status', [TourController::class, 'toggleStatus']);
        Route::patch('/{tour}/toggle-featured', [TourController::class, 'toggleFeatured']);
        Route::post('/{tour}/duplicate', [TourController::class, 'duplicate']);
        
        // Tour schedule management routes
        Route::prefix('{tour}/schedules')->group(function () {
            Route::get('/', [TourScheduleController::class, 'index']);
            Route::post('/', [TourScheduleController::class, 'store']);
            Route::post('/bulk', [TourScheduleController::class, 'bulkCreate']);
            Route::get('/statistics', [TourScheduleController::class, 'statistics']);
            Route::get('/{schedule}', [TourScheduleController::class, 'show']);
            Route::put('/{schedule}', [TourScheduleController::class, 'update']);
            Route::delete('/{schedule}', [TourScheduleController::class, 'destroy']);
            Route::patch('/{schedule}/cancel', [TourScheduleController::class, 'cancel']);
            Route::patch('/{schedule}/complete', [TourScheduleController::class, 'complete']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/

// Protected booking routes (Authenticated users)
Route::middleware(['auth:sanctum', 'auth.api'])->group(function () {
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::post('/plan', [BookingController::class, 'storePlanning']);
        Route::get('/summary', [BookingController::class, 'summary']);
        Route::get('/{booking}', [BookingController::class, 'show']);
        Route::put('/{booking}', [BookingController::class, 'update']);
        Route::patch('/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::patch('/{booking}/confirm', [BookingController::class, 'confirm']);
        Route::patch('/{booking}/payment', [BookingController::class, 'processPayment']);
    });
});

// Admin booking management routes
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/bookings')->group(function () {
        Route::get('/statistics', [BookingController::class, 'statistics']);
    });
});

/*
|--------------------------------------------------------------------------
| Review Routes
|--------------------------------------------------------------------------
*/

use App\Features\Reviews\Controllers\ReviewController;

// Public review routes
Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);
});

// Protected review routes (Authenticated users)
Route::middleware(['auth:sanctum', 'auth.api'])->group(function () {
    Route::prefix('reviews')->group(function () {
        Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/{review}', [ReviewController::class, 'update']);
        Route::delete('/{review}', [ReviewController::class, 'destroy']);
        Route::post('/{review}/vote-helpful', [ReviewController::class, 'voteHelpful']);
        Route::post('/{review}/vote-not-helpful', [ReviewController::class, 'voteNotHelpful']);
    });
});

// Public review detail route (must be after protected routes to avoid conflicts)
Route::get('/reviews/{review}', [ReviewController::class, 'show']);

// Admin moderation routes
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/reviews')->group(function () {
        Route::get('/', [\App\Features\Reviews\Controllers\ModerationController::class, 'index']);
        Route::get('/pending', [\App\Features\Reviews\Controllers\ModerationController::class, 'pending']);
        Route::get('/flagged', [\App\Features\Reviews\Controllers\ModerationController::class, 'flagged']);
        Route::get('/stats', [\App\Features\Reviews\Controllers\ModerationController::class, 'stats']);
        Route::post('/bulk-approve', [\App\Features\Reviews\Controllers\ModerationController::class, 'bulkApprove']);
        Route::get('/{review}', [\App\Features\Reviews\Controllers\ModerationController::class, 'show']);
        Route::post('/{review}/approve', [\App\Features\Reviews\Controllers\ModerationController::class, 'approve']);
        Route::post('/{review}/reject', [\App\Features\Reviews\Controllers\ModerationController::class, 'reject']);
        Route::post('/{review}/hide', [\App\Features\Reviews\Controllers\ModerationController::class, 'hide']);
    });
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/

use App\Features\Payments\Controllers\PaymentController;
use App\Features\Payments\Controllers\CommissionController;
use App\Features\Payments\Controllers\InvoiceController;

// Protected payment routes (Authenticated users)
Route::middleware(['auth:sanctum', 'auth.api'])->group(function () {
    Route::prefix('payments')->group(function () {
        Route::post('/process', [PaymentController::class, 'processPayment']);
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::get('/{paymentId}/status', [PaymentController::class, 'getPaymentStatus']);
        Route::post('/{paymentId}/refund', [PaymentController::class, 'refundPayment']);
        Route::post('/booking/{booking}/confirm', [PaymentController::class, 'confirmPayment']);
    });
    
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'getInvoiceHistory']);
        Route::get('/{booking}/generate', [InvoiceController::class, 'generateInvoice']);
        Route::get('/{booking}/download', [InvoiceController::class, 'downloadInvoice'])->name('invoices.download');
        Route::post('/{booking}/email', [InvoiceController::class, 'sendInvoiceByEmail']);
    });
});

// Admin commission management routes
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/commissions')->group(function () {
        Route::get('/monthly', [CommissionController::class, 'getMonthlyReport']);
        Route::get('/yearly', [CommissionController::class, 'getYearlyReport']);
        Route::get('/date-range', [CommissionController::class, 'getDateRangeReport']);
        Route::get('/pending', [CommissionController::class, 'getPendingCommissions']);
        Route::post('/mark-paid', [CommissionController::class, 'markAsPaid']);
        Route::get('/rates', [CommissionController::class, 'getCommissionRates']);
    });
});

/*
|--------------------------------------------------------------------------
| User Management Routes (Admin)
|--------------------------------------------------------------------------
*/

use App\Features\Admin\Controllers\UserController;

// Admin user management routes
Route::middleware(['auth:sanctum', 'auth.api', 'role:admin'])->group(function () {
    Route::prefix('admin/users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/statistics', [UserController::class, 'getStatistics']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::patch('/{user}/activate', [UserController::class, 'activate']);
        Route::patch('/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('/{user}/send-password-reset', [UserController::class, 'sendPasswordResetLink']);
        Route::get('/{user}/activity', [UserController::class, 'getActivityLogs']);
    });
});
/*
|
--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
*/

use App\Features\Users\Controllers\UserDashboardController;

// ENDPOINTS TEMPORALES SIN AUTENTICACIÓN - PARA ARREGLAR TODO
Route::get('/dashboard-stats-temp', function () {
    return response()->json([
        'total_bookings' => 8,
        'total_reviews' => 4,
        'favorite_attractions' => 3,
        'booking_status' => [
            'confirmed' => 5,
            'pending' => 2,
            'cancelled' => 1
        ]
    ]);
});

Route::get('/upcoming-bookings-temp', function () {
    return response()->json(['data' => []]);
});

Route::get('/booking-history-temp', function () {
    return response()->json(['data' => []]);
});

Route::get('/user-favorites-temp', function () {
    return response()->json(['data' => []]);
});

// User dashboard routes moved to web.php for proper session handling