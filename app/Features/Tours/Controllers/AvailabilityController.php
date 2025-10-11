<?php

namespace App\Features\Tours\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Check availability for a specific tour on a specific date
     */
    public function checkDate(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($request->date);
        
        $schedules = $tour->availableSchedules()
                         ->onDate($date)
                         ->withSpots()
                         ->orderBy('start_time')
                         ->get();

        $availability = [
            'date' => $date->toDateString(),
            'is_available' => $schedules->isNotEmpty(),
            'schedules' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'start_time' => $schedule->start_time->format('H:i'),
                    'end_time' => $schedule->end_time ? $schedule->end_time->format('H:i') : null,
                    'available_spots' => $schedule->getRemainingSpots(),
                    'total_spots' => $schedule->available_spots,
                    'price' => $schedule->effective_price,
                    'guide_name' => $schedule->guide_name,
                    'special_conditions' => $schedule->special_conditions,
                    'weather_info' => $schedule->getWeatherInfo(),
                ];
            }),
            'total_available_spots' => $schedules->sum(function ($schedule) {
                return $schedule->getRemainingSpots();
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }

    /**
     * Get availability for a date range
     */
    public function checkRange(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after:date_from|before_or_equal:' . now()->addMonths(6)->toDateString(),
        ]);

        $startDate = Carbon::parse($request->date_from);
        $endDate = Carbon::parse($request->date_to);

        // Limit range to prevent performance issues
        if ($startDate->diffInDays($endDate) > 90) {
            return response()->json([
                'success' => false,
                'message' => 'El rango de fechas no puede ser mayor a 90 días'
            ], 422);
        }

        $availability = $tour->getAvailabilityForRange($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'from' => $startDate->toDateString(),
                    'to' => $endDate->toDateString(),
                ],
                'availability' => $availability,
                'summary' => [
                    'total_days' => $startDate->diffInDays($endDate) + 1,
                    'available_days' => collect($availability)->where('available', true)->count(),
                    'total_spots' => collect($availability)->sum('total_spots'),
                ]
            ]
        ]);
    }

    /**
     * Check availability for multiple tours on a specific date
     */
    public function checkMultipleTours(Request $request): JsonResponse
    {
        $request->validate([
            'tour_ids' => 'required|array|min:1|max:10',
            'tour_ids.*' => 'integer|exists:tours,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($request->date);
        $tourIds = $request->tour_ids;

        $tours = Tour::whereIn('id', $tourIds)
                    ->active()
                    ->with(['availableSchedules' => function ($query) use ($date) {
                        $query->onDate($date)->withSpots();
                    }])
                    ->get();

        $availability = $tours->map(function ($tour) use ($date) {
            $schedules = $tour->availableSchedules;
            
            return [
                'tour_id' => $tour->id,
                'tour_name' => $tour->name,
                'tour_slug' => $tour->slug,
                'is_available' => $schedules->isNotEmpty(),
                'schedules_count' => $schedules->count(),
                'total_available_spots' => $schedules->sum(function ($schedule) {
                    return $schedule->getRemainingSpots();
                }),
                'min_price' => $schedules->min('effective_price'),
                'max_price' => $schedules->max('effective_price'),
                'earliest_time' => $schedules->min('start_time')?->format('H:i'),
                'latest_time' => $schedules->max('start_time')?->format('H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'tours' => $availability,
                'summary' => [
                    'total_tours_checked' => $tours->count(),
                    'available_tours' => $availability->where('is_available', true)->count(),
                    'total_available_spots' => $availability->sum('total_available_spots'),
                ]
            ]
        ]);
    }

    /**
     * Get next available dates for a tour
     */
    public function nextAvailable(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:30',
            'from_date' => 'date|after_or_equal:today',
        ]);

        $limit = $request->get('limit', 10);
        $fromDate = $request->filled('from_date') 
            ? Carbon::parse($request->from_date) 
            : now();

        $schedules = $tour->availableSchedules()
                         ->where('date', '>=', $fromDate->toDateString())
                         ->withSpots()
                         ->orderBy('date')
                         ->orderBy('start_time')
                         ->limit($limit)
                         ->get();

        $nextDates = $schedules->groupBy('date')->map(function ($daySchedules, $date) {
            return [
                'date' => $date,
                'day_name' => Carbon::parse($date)->locale('es')->dayName,
                'schedules_count' => $daySchedules->count(),
                'total_spots' => $daySchedules->sum(function ($schedule) {
                    return $schedule->getRemainingSpots();
                }),
                'min_price' => $daySchedules->min('effective_price'),
                'earliest_time' => $daySchedules->min('start_time')->format('H:i'),
                'latest_time' => $daySchedules->max('start_time')->format('H:i'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'tour_id' => $tour->id,
                'tour_name' => $tour->name,
                'next_available_dates' => $nextDates,
                'has_more' => $tour->availableSchedules()
                                  ->where('date', '>', $schedules->last()?->date ?? $fromDate->toDateString())
                                  ->exists(),
            ]
        ]);
    }

    /**
     * Check if specific number of spots are available
     */
    public function checkSpots(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'schedule_id' => 'required|integer|exists:tour_schedules,id',
            'spots_needed' => 'required|integer|min:1|max:' . $tour->max_participants,
        ]);

        $schedule = TourSchedule::where('id', $request->schedule_id)
                               ->where('tour_id', $tour->id)
                               ->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no encontrado para este tour'
            ], 404);
        }

        $spotsNeeded = $request->spots_needed;
        $availableSpots = $schedule->getRemainingSpots();
        $canBook = $schedule->canBeBooked() && $availableSpots >= $spotsNeeded;

        return response()->json([
            'success' => true,
            'data' => [
                'schedule_id' => $schedule->id,
                'date' => $schedule->date->toDateString(),
                'start_time' => $schedule->start_time->format('H:i'),
                'spots_needed' => $spotsNeeded,
                'available_spots' => $availableSpots,
                'can_book' => $canBook,
                'price_per_person' => $schedule->effective_price,
                'total_price' => $schedule->effective_price * $spotsNeeded,
                'status' => $schedule->status,
                'message' => $canBook 
                    ? 'Disponible para reservar'
                    : ($availableSpots < $spotsNeeded 
                        ? 'No hay suficientes cupos disponibles'
                        : 'Horario no disponible para reservas'),
            ]
        ]);
    }

    /**
     * Get availability calendar for a month
     */
    public function calendar(Request $request, Tour $tour): JsonResponse
    {
        $request->validate([
            'year' => 'integer|min:' . now()->year . '|max:' . (now()->year + 2),
            'month' => 'integer|min:1|max:12',
        ]);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Don't show past dates
        if ($startDate->lt(now()->startOfDay())) {
            $startDate = now()->startOfDay();
        }

        $schedules = $tour->availableSchedules()
                         ->betweenDates($startDate, $endDate)
                         ->withSpots()
                         ->get()
                         ->groupBy('date');

        $calendar = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $daySchedules = $schedules->get($dateStr, collect());

            $calendar[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'day_name' => $current->locale('es')->dayName,
                'is_available' => $daySchedules->isNotEmpty(),
                'schedules_count' => $daySchedules->count(),
                'total_spots' => $daySchedules->sum(function ($schedule) {
                    return $schedule->getRemainingSpots();
                }),
                'min_price' => $daySchedules->min('effective_price'),
                'is_weekend' => $current->isWeekend(),
                'is_today' => $current->isToday(),
                'is_past' => $current->isPast(),
            ];

            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->locale('es')->monthName,
                'calendar' => $calendar,
                'summary' => [
                    'total_days' => count($calendar),
                    'available_days' => collect($calendar)->where('is_available', true)->count(),
                    'total_available_spots' => collect($calendar)->sum('total_spots'),
                ]
            ]
        ]);
    }
}