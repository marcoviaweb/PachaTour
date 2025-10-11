<?php

namespace App\Features\Tours\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TourSchedule;
use App\Features\Tours\Services\BookingService;
use App\Features\Tours\Requests\StoreBookingRequest;
use App\Features\Tours\Requests\UpdateBookingRequest;
use App\Features\Tours\Requests\CancelBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    
    /**
     * Display a listing of user's bookings
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 'payment_status', 'date_from', 'date_to', 'per_page'
        ]);
        
        $bookings = $this->bookingService->getUserBookings(
            Auth::id(),
            $filters
        );
        
        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ]
        ]);
    }
    
    /**
     * Store a newly created booking
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            
            $booking = $this->bookingService->createBooking($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'data' => $booking->load(['tourSchedule.tour', 'user'])
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reserva',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Display the specified booking
     */
    public function show(Booking $booking): JsonResponse
    {
        // Check if user owns this booking or is admin
        if ($booking->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver esta reserva'
            ], 403);
        }
        
        $booking->load([
            'tourSchedule.tour',
            'user',
            'reviews'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }
    
    /**
     * Update the specified booking
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para modificar esta reserva'
            ], 403);
        }
        
        try {
            $data = $request->validated();
            $updatedBooking = $this->bookingService->updateBooking($booking, $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente',
                'data' => $updatedBooking->load(['tourSchedule.tour', 'user'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reserva',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Cancel the specified booking
     */
    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cancelar esta reserva'
            ], 403);
        }
        
        try {
            $data = $request->validated();
            $success = $this->bookingService->cancelBooking(
                $booking, 
                $data['cancellation_reason'] ?? null
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reserva cancelada exitosamente',
                    'data' => $booking->fresh()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo cancelar la reserva'
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la reserva',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Confirm a booking (admin or payment gateway)
     */
    public function confirm(Booking $booking): JsonResponse
    {
        try {
            $success = $this->bookingService->confirmBooking($booking);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reserva confirmada exitosamente',
                    'data' => $booking->fresh()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo confirmar la reserva'
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la reserva',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Process payment for a booking
     */
    public function processPayment(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string|max:100',
            'payment_reference' => 'nullable|string|max:255'
        ]);
        
        // Check if user owns this booking or is admin
        if ($booking->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para procesar el pago de esta reserva'
            ], 403);
        }
        
        try {
            $success = $this->bookingService->processPayment(
                $booking,
                $request->payment_method,
                $request->payment_reference
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'data' => $booking->fresh()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar el pago'
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Get booking summary for checkout
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'tour_schedule_id' => 'required|exists:tour_schedules,id',
            'participants_count' => 'required|integer|min:1|max:20'
        ]);
        
        try {
            $schedule = TourSchedule::with(['tour'])->findOrFail($request->tour_schedule_id);
            
            // Validate availability
            $this->bookingService->validateAvailability(
                $request->tour_schedule_id,
                $request->participants_count
            );
            
            // Calculate amounts
            $amounts = $this->bookingService->calculateAmounts(
                $request->tour_schedule_id,
                $request->participants_count
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'tour' => [
                        'id' => $schedule->tour->id,
                        'name' => $schedule->tour->name,
                        'duration' => $schedule->tour->formatted_duration,
                        'meeting_point' => $schedule->tour->meeting_point,
                    ],
                    'schedule' => [
                        'id' => $schedule->id,
                        'date' => $schedule->date->format('d/m/Y'),
                        'start_time' => $schedule->start_time->format('H:i'),
                        'end_time' => $schedule->end_time->format('H:i'),
                        'available_spots' => $schedule->getRemainingSpots(),
                    ],
                    'booking' => [
                        'participants_count' => $request->participants_count,
                        'price_per_person' => $amounts['price_per_person'],
                        'total_amount' => $amounts['total'],
                        'currency' => 'BOB',
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen de la reserva',
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Get booking statistics (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        
        $statistics = $this->bookingService->getBookingStatistics($filters);
        
        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}