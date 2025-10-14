<?php

namespace Tests\Feature\Tours;

use Tests\TestCase;
use App\Features\Tours\Models\Tour;
use App\Features\Tours\Models\TourSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $tour;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tour = Tour::factory()->create([
            'max_participants' => 10,
            'is_active' => true
        ]);
        
        $this->user = User::factory()->create();
    }

    /** @test */
    public function can_check_availability_for_specific_date()
    {
        $date = now()->addDays(3)->toDateString();
        
        // Create available schedules for the date
        TourSchedule::create([
            'tour_id' => $this->tour->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 5,
            'booked_spots' => 2
        ]);
        
        TourSchedule::create([
            'tour_id' => $this->tour->id,
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '18:00',
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 5,
            'booked_spots' => 2
        ]);

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/date?date={$date}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'date',
                        'is_available',
                        'schedules' => [
                            '*' => [
                                'id',
                                'start_time',
                                'end_time',
                                'available_spots',
                                'total_spots',
                                'price'
                            ]
                        ],
                        'total_available_spots'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'date' => $date,
                        'is_available' => true,
                        'total_available_spots' => 6 // (5-2) + (5-2) = 6
                    ]
                ]);
    }

    /** @test */
    public function availability_check_validates_date_format()
    {
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/date?date=invalid-date");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function availability_check_requires_future_date()
    {
        $pastDate = now()->subDays(1)->toDateString();
        
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/date?date={$pastDate}");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function can_check_availability_for_date_range()
    {
        $startDate = now()->addDays(1)->toDateString();
        $endDate = now()->addDays(7)->toDateString();

        // Create schedules for some days in the range
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(2)->toDateString(),
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 8,
            'booked_spots' => 0
        ]);

        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(5)->toDateString(),
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 6,
            'booked_spots' => 2
        ]);

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/range?date_from={$startDate}&date_to={$endDate}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'date_range' => [
                            'from',
                            'to'
                        ],
                        'availability',
                        'summary' => [
                            'total_days',
                            'available_days',
                            'total_spots'
                        ]
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(7, $data['summary']['total_days']);
        $this->assertEquals(2, $data['summary']['available_days']);
    }

    /** @test */
    public function date_range_availability_limits_range_to_90_days()
    {
        $startDate = now()->addDays(1)->toDateString();
        $endDate = now()->addDays(100)->toDateString(); // More than 90 days

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/range?date_from={$startDate}&date_to={$endDate}");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'El rango de fechas no puede ser mayor a 90 días'
                ]);
    }

    /** @test */
    public function can_check_availability_for_multiple_tours()
    {
        $tour2 = Tour::factory()->create(['is_active' => true]);
        $date = now()->addDays(3)->toDateString();

        // Create schedules for both tours
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $date,
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 5,
            'booked_spots' => 1
        ]);

        TourSchedule::factory()->create([
            'tour_id' => $tour2->id,
            'date' => $date,
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 8,
            'booked_spots' => 0
        ]);

        $tourIds = [$this->tour->id, $tour2->id];
        $response = $this->postJson('/api/tours/availability/multiple', [
            'tour_ids' => $tourIds,
            'date' => $date
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'date',
                        'tours' => [
                            '*' => [
                                'tour_id',
                                'tour_name',
                                'is_available',
                                'total_available_spots'
                            ]
                        ],
                        'summary'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertCount(2, $data['tours']);
        $this->assertEquals(2, $data['summary']['available_tours']);
    }

    /** @test */
    public function multiple_tours_availability_validates_tour_ids()
    {
        $response = $this->postJson('/api/tours/availability/multiple', [
            'tour_ids' => [999, 1000], // Non-existent tours
            'date' => now()->addDays(1)->toDateString()
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['tour_ids.0', 'tour_ids.1']);
    }

    /** @test */
    public function can_get_next_available_dates()
    {
        // Create schedules for various future dates
        $dates = [
            now()->addDays(2),
            now()->addDays(5),
            now()->addDays(8),
            now()->addDays(12)
        ];

        foreach ($dates as $date) {
            TourSchedule::factory()->create([
                'tour_id' => $this->tour->id,
                'date' => $date->toDateString(),
                'status' => TourSchedule::STATUS_AVAILABLE,
                'available_spots' => 6,
                'booked_spots' => 2
            ]);
        }

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/next?limit=3");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'tour_id',
                        'tour_name',
                        'next_available_dates' => [
                            '*' => [
                                'date',
                                'day_name',
                                'schedules_count',
                                'total_spots',
                                'min_price',
                                'earliest_time'
                            ]
                        ],
                        'has_more'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertCount(3, $data['next_available_dates']);
    }

    /** @test */
    public function can_check_specific_spots_availability()
    {
        $schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(3)->toDateString(),
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 8,
            'booked_spots' => 3
        ]);

        // Check if 4 spots are available (should be true: 8-3=5 available)
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/spots?schedule_id={$schedule->id}&spots_needed=4");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'schedule_id' => $schedule->id,
                        'spots_needed' => 4,
                        'available_spots' => 5,
                        'can_book' => true,
                        'total_price' => $schedule->effective_price * 4
                    ]
                ]);

        // Check if 6 spots are available (should be false: only 5 available)
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/spots?schedule_id={$schedule->id}&spots_needed=6");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'can_book' => false,
                        'message' => 'No hay suficientes cupos disponibles'
                    ]
                ]);
    }

    /** @test */
    public function spots_availability_validates_schedule_belongs_to_tour()
    {
        $otherTour = Tour::factory()->create();
        $schedule = TourSchedule::factory()->create(['tour_id' => $otherTour->id]);

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/spots?schedule_id={$schedule->id}&spots_needed=2");

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Horario no encontrado para este tour'
                ]);
    }

    /** @test */
    public function can_get_availability_calendar()
    {
        $year = now()->year;
        $month = now()->month;

        // Create schedules for various days in the month
        $daysWithSchedules = [5, 10, 15, 20, 25];
        
        foreach ($daysWithSchedules as $day) {
            $date = Carbon::create($year, $month, $day);
            if ($date->isFuture()) {
                TourSchedule::factory()->create([
                    'tour_id' => $this->tour->id,
                    'date' => $date->toDateString(),
                    'status' => TourSchedule::STATUS_AVAILABLE,
                    'available_spots' => 6,
                    'booked_spots' => 2
                ]);
            }
        }

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/calendar?year={$year}&month={$month}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'year',
                        'month',
                        'month_name',
                        'calendar' => [
                            '*' => [
                                'date',
                                'day',
                                'day_name',
                                'is_available',
                                'schedules_count',
                                'total_spots',
                                'is_weekend',
                                'is_today',
                                'is_past'
                            ]
                        ],
                        'summary'
                    ]
                ]);
    }

    /** @test */
    public function calendar_validates_year_and_month()
    {
        // Test invalid year (too far in future)
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/calendar?year=2030&month=1");
        $response->assertStatus(422);

        // Test invalid month
        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/calendar?year=" . now()->year . "&month=13");
        $response->assertStatus(422);
    }

    /** @test */
    public function availability_excludes_inactive_tours()
    {
        $inactiveTour = Tour::factory()->create(['is_active' => false]);
        $date = now()->addDays(3)->toDateString();

        TourSchedule::factory()->create([
            'tour_id' => $inactiveTour->id,
            'date' => $date,
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 5
        ]);

        $response = $this->getJson("/api/tours/{$inactiveTour->id}/availability/date?date={$date}");

        // Should still work but return no availability since tour is inactive
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'is_available' => false,
                        'schedules' => []
                    ]
                ]);
    }

    /** @test */
    public function availability_excludes_past_schedules()
    {
        $pastDate = now()->subDays(1)->toDateString();
        
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $pastDate,
            'status' => TourSchedule::STATUS_AVAILABLE,
            'available_spots' => 5
        ]);

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/date?date={$pastDate}");

        $response->assertStatus(422); // Should fail validation for past date
    }

    /** @test */
    public function availability_excludes_cancelled_schedules()
    {
        $date = now()->addDays(3)->toDateString();
        
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $date,
            'status' => TourSchedule::STATUS_CANCELLED,
            'available_spots' => 5
        ]);

        $response = $this->getJson("/api/tours/{$this->tour->id}/availability/date?date={$date}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'is_available' => false,
                        'schedules' => []
                    ]
                ]);
    }
}