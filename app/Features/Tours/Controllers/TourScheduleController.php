<?php

namespace App\Features\Tours\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Features\Tours\Requests\StoreTourScheduleRequest;
use App\Features\Tours\Requests\UpdateTourScheduleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TourScheduleController extends Controller
{
    /**
     * Display schedules for a specific tour
     */
    public function index(Request $request, Tour $tour): JsonResponse
    {
        $query = $tour->schedules()->with(['bookings' => function ($q) {
            $q->whereIn('status', ['confirmed', 'paid', 'completed']);
        }]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->available()->withSpots();
            } elseif ($request->availability === 'full') {
                $query->where('status', TourSchedule::STATUS_FULL);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSorts = ['date', 'start_time', 'available_spots', 'booked_spots', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        if ($sortBy === 'date') {
            $query->orderBy('start_time', 'asc');
        }

        $perPage = min($request->get('per_page', 20), 100);
        $schedules = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $schedules,
            'meta' => [
                'tour_id' => $tour->id,
                'tour_name' => $tour->name,
                'total_schedules' => $tour->schedules()->count(),
                'available_schedules' => $tour->availableSchedules()->count(),
            ]
        ]);
    }

    /**
     * Store a new schedule for a tour
     */
    public function store(StoreTourScheduleRequest $request, Tour $tour): JsonResponse
    {
        $scheduleData = $request->validated();
        $scheduleData['tour_id'] = $tour->id;

        // Check for conflicts with existing schedules
        $existingSchedule = $tour->schedules()
            ->where('date', $scheduleData['date'])
            ->where('start_time', $scheduleData['start_time'])
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un horario para esta fecha y hora'
            ], 422);
        }

        $schedule = TourSchedule::create($scheduleData);
        $schedule->load(['tour', 'bookings']);

        return response()->json([
            'success' => true,
            'message' => 'Horario creado exitosamente',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display a specific schedule
     */
    public function show(Tour $tour, TourSchedule $schedule): JsonResponse
    {
        // Ensure the schedule belongs to the tour
        if ($schedule->tour_id !== $tour->id) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        $schedule->load([
            'tour',
            'bookings' => function ($query) {
                $query->with('user:id,name,email')->latest();
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    /**
     * Update a specific schedule
     */
    public function update(UpdateTourScheduleRequest $request, Tour $tour, TourSchedule $schedule): JsonResponse
    {
        // Ensure the schedule belongs to the tour
        if ($schedule->tour_id !== $tour->id) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        // Check if schedule has bookings and prevent certain changes
        $hasBookings = $schedule->bookings()->whereIn('status', ['confirmed', 'paid'])->exists();
        
        if ($hasBookings) {
            $restrictedFields = ['date', 'start_time', 'end_time'];
            $requestData = $request->validated();
            
            foreach ($restrictedFields as $field) {
                if (isset($requestData[$field]) && $requestData[$field] != $schedule->$field) {
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede modificar {$field} porque el horario tiene reservas confirmadas"
                    ], 422);
                }
            }
        }

        $schedule->update($request->validated());
        $schedule->load(['tour', 'bookings']);

        return response()->json([
            'success' => true,
            'message' => 'Horario actualizado exitosamente',
            'data' => $schedule
        ]);
    }

    /**
     * Delete a specific schedule
     */
    public function destroy(Tour $tour, TourSchedule $schedule): JsonResponse
    {
        // Ensure the schedule belongs to the tour
        if ($schedule->tour_id !== $tour->id) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        // Check if schedule has active bookings
        $activeBookings = $schedule->bookings()
                                  ->whereIn('status', ['pending', 'confirmed', 'paid'])
                                  ->count();

        if ($activeBookings > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el horario porque tiene reservas activas'
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Horario eliminado exitosamente'
        ]);
    }

    /**
     * Cancel a schedule
     */
    public function cancel(Request $request, Tour $tour, TourSchedule $schedule): JsonResponse
    {
        // Ensure the schedule belongs to the tour
        if ($schedule->tour_id !== $tour->id) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        $reason = $request->input('reason', 'Cancelado por administrador');
        $schedule->cancel($reason);

        return response()->json([
            'success' => true,
            'message' => 'Horario cancelado exitosamente',
            'data' => $schedule->fresh()
        ]);
    }

    /**
     * Mark schedule as completed
     */
    public function complete(Tour $tour, TourSchedule $schedule): JsonResponse
    {
        // Ensure the schedule belongs to the tour
        if ($schedule->tour_id !== $tour->id) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        if ($schedule->status !== TourSchedule::STATUS_AVAILABLE && 
            $schedule->status !== TourSchedule::STATUS_FULL) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden completar horarios disponibles o llenos'
            ], 422);
        }

        $schedule->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Horario marcado como completado',
            'data' => $schedule->fresh()
        ]);
    }

    /**
     * Bulk create schedules
     */
    public function bulkCreate(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after:date_from',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'available_spots' => 'required|integer|min:1|max:' . $tour->max_participants,
            'price_override' => 'nullable|numeric|min:0',
            'guide_name' => 'nullable|string|max:255',
            'guide_contact' => 'nullable|string|max:255',
        ]);

        $startDate = Carbon::parse($request->date_from);
        $endDate = Carbon::parse($request->date_to);
        $daysOfWeek = $request->days_of_week;
        
        $schedules = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if (in_array($current->dayOfWeek, $daysOfWeek)) {
                // Check if schedule already exists
                $exists = $tour->schedules()
                    ->where('date', $current->toDateString())
                    ->where('start_time', $request->start_time)
                    ->exists();
                
                if (!$exists) {
                    $schedules[] = [
                        'tour_id' => $tour->id,
                        'date' => $current->toDateString(),
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'available_spots' => $request->available_spots,
                        'booked_spots' => 0,
                        'price_override' => $request->price_override,
                        'status' => TourSchedule::STATUS_AVAILABLE,
                        'guide_name' => $request->guide_name,
                        'guide_contact' => $request->guide_contact,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $current->addDay();
        }

        if (empty($schedules)) {
            return response()->json([
                'success' => false,
                'message' => 'No se crearon horarios. Todos los horarios ya existen.'
            ], 422);
        }

        TourSchedule::insert($schedules);

        return response()->json([
            'success' => true,
            'message' => count($schedules) . ' horarios creados exitosamente',
            'data' => [
                'created_count' => count($schedules),
                'date_range' => [
                    'from' => $startDate->toDateString(),
                    'to' => $endDate->toDateString()
                ]
            ]
        ], 201);
    }

    /**
     * Get schedule statistics
     */
    public function statistics(Tour $tour): JsonResponse
    {
        $stats = [
            'total_schedules' => $tour->schedules()->count(),
            'available_schedules' => $tour->availableSchedules()->count(),
            'completed_schedules' => $tour->schedules()->where('status', TourSchedule::STATUS_COMPLETED)->count(),
            'cancelled_schedules' => $tour->schedules()->where('status', TourSchedule::STATUS_CANCELLED)->count(),
            'full_schedules' => $tour->schedules()->where('status', TourSchedule::STATUS_FULL)->count(),
            'upcoming_schedules' => $tour->schedules()->upcoming()->count(),
            'total_spots' => $tour->schedules()->sum('available_spots'),
            'booked_spots' => $tour->schedules()->sum('booked_spots'),
            'occupancy_rate' => $tour->schedules()->sum('available_spots') > 0 
                ? round(($tour->schedules()->sum('booked_spots') / $tour->schedules()->sum('available_spots')) * 100, 2)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}