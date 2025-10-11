<?php

namespace Tests\Feature\Tours;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class TourScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $tour;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->tour = Tour::factory()->create([
            'max_participants' => 10,
            'min_participants' => 2
        ]);
    }

    /** @test */
    public function admin_can_view_tour_schedules()
    {
        Sanctum::actingAs($this->adminUser);
        
        TourSchedule::factory()->count(3)->create(['tour_id' => $this->tour->id]);

        $response = $this->getJson("/api/admin/tours/{$this->tour->id}/schedules");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'date',
                                'start_time',
                                'available_spots',
                                'booked_spots',
                                'status'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function admin_can_create_schedule()
    {
        Sanctum::actingAs($this->adminUser);

        $scheduleData = [
            'date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'available_spots' => 8,
            'guide_name' => 'Juan Pérez',
            'guide_contact' => 'juan@example.com',
            'notes' => 'Tour especial de temporada alta'
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", $scheduleData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'tour_id',
                        'date',
                        'start_time',
                        'available_spots'
                    ]
                ]);

        // Check that the schedule was created
        $schedule = TourSchedule::where('tour_id', $this->tour->id)
                                ->where('available_spots', 8)
                                ->where('guide_name', 'Juan Pérez')
                                ->first();
        
        $this->assertNotNull($schedule);
        $this->assertEquals(now()->addDays(7)->toDateString(), $schedule->date->toDateString());
    }

    /** @test */
    public function schedule_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'date',
                    'start_time',
                    'available_spots'
                ]);
    }

    /** @test */
    public function schedule_creation_validates_date_is_future()
    {
        Sanctum::actingAs($this->adminUser);

        $pastDate = [
            'date' => now()->subDays(1)->toDateString(),
            'start_time' => '09:00',
            'available_spots' => 5
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", $pastDate);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function schedule_creation_validates_available_spots_within_tour_limits()
    {
        Sanctum::actingAs($this->adminUser);

        // Test exceeding max participants
        $tooManySpots = [
            'date' => now()->addDays(1)->toDateString(),
            'start_time' => '09:00',
            'available_spots' => 15 // Exceeds tour max of 10
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", $tooManySpots);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['available_spots']);
    }

    /** @test */
    public function schedule_creation_prevents_duplicate_time_slots()
    {
        Sanctum::actingAs($this->adminUser);

        $date = now()->addDays(1)->toDateString();
        
        // Create first schedule
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $date,
            'start_time' => '09:00'
        ]);

        // Try to create duplicate
        $duplicateData = [
            'date' => $date,
            'start_time' => '09:00',
            'available_spots' => 5
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", $duplicateData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Ya existe un horario para esta fecha y hora'
                ]);
    }

    /** @test */
    public function admin_can_update_schedule()
    {
        Sanctum::actingAs($this->adminUser);

        $schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'available_spots' => 5,
            'guide_name' => 'Original Guide'
        ]);

        $updateData = [
            'available_spots' => 8,
            'guide_name' => 'Updated Guide',
            'notes' => 'Updated notes'
        ];

        $response = $this->putJson("/api/admin/tours/{$this->tour->id}/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('tour_schedules', [
            'id' => $schedule->id,
            'available_spots' => 8,
            'guide_name' => 'Updated Guide'
        ]);
    }

    /** @test */
    public function cannot_update_schedule_with_confirmed_bookings()
    {
        Sanctum::actingAs($this->adminUser);

        $schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(1)->toDateString(),
            'start_time' => '09:00'
        ]);

        // Create a confirmed booking
        Booking::factory()->create([
            'tour_schedule_id' => $schedule->id,
            'status' => 'confirmed'
        ]);

        $updateData = [
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00'
        ];

        $response = $this->putJson("/api/admin/tours/{$this->tour->id}/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_cancel_schedule()
    {
        Sanctum::actingAs($this->adminUser);

        $schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'status' => TourSchedule::STATUS_AVAILABLE
        ]);

        $response = $this->patchJson("/api/admin/tours/{$this->tour->id}/schedules/{$schedule->id}/cancel", [
            'reason' => 'Mal clima'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Horario cancelado exitosamente'
                ]);

        $this->assertDatabaseHas('tour_schedules', [
            'id' => $schedule->id,
            'status' => TourSchedule::STATUS_CANCELLED
        ]);
    }

    /** @test */
    public function admin_can_mark_schedule_as_completed()
    {
        Sanctum::actingAs($this->adminUser);

        $schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'status' => TourSchedule::STATUS_AVAILABLE
        ]);

        $response = $this->patchJson("/api/admin/tours/{$this->tour->id}/schedules/{$schedule->id}/complete");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Horario marcado como completado'
                ]);

        $this->assertDatabaseHas('tour_schedules', [
            'id' => $schedule->id,
            'status' => TourSchedule::STATUS_COMPLETED
        ]);
    }

    /** @test */
    public function admin_can_bulk_create_schedules()
    {
        Sanctum::actingAs($this->adminUser);

        $bulkData = [
            'date_from' => now()->addDays(1)->toDateString(),
            'date_to' => now()->addDays(7)->toDateString(),
            'days_of_week' => [1, 3, 5], // Monday, Wednesday, Friday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'available_spots' => 8,
            'guide_name' => 'Bulk Guide'
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules/bulk", $bulkData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'created_count',
                        'date_range'
                    ]
                ]);

        // Should create schedules for Monday, Wednesday, Friday within the date range
        $this->assertTrue($response->json('data.created_count') > 0);
    }

    /** @test */
    public function admin_can_get_schedule_statistics()
    {
        Sanctum::actingAs($this->adminUser);

        TourSchedule::factory()->count(5)->create([
            'tour_id' => $this->tour->id,
            'status' => TourSchedule::STATUS_AVAILABLE
        ]);
        
        TourSchedule::factory()->count(2)->create([
            'tour_id' => $this->tour->id,
            'status' => TourSchedule::STATUS_COMPLETED
        ]);

        $response = $this->getJson("/api/admin/tours/{$this->tour->id}/schedules/statistics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_schedules',
                        'available_schedules',
                        'completed_schedules',
                        'cancelled_schedules',
                        'occupancy_rate'
                    ]
                ]);
    }

    /** @test */
    public function schedule_filtering_works_correctly()
    {
        Sanctum::actingAs($this->adminUser);

        $today = now();
        
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $today->addDays(1)->toDateString(),
            'status' => TourSchedule::STATUS_AVAILABLE
        ]);
        
        TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => $today->addDays(2)->toDateString(),
            'status' => TourSchedule::STATUS_CANCELLED
        ]);

        // Filter by status
        $response = $this->getJson("/api/admin/tours/{$this->tour->id}/schedules?status=available");
        $response->assertStatus(200);

        // Filter by date range
        $dateFrom = $today->addDays(1)->toDateString();
        $dateTo = $today->addDays(1)->toDateString();
        
        $response = $this->getJson("/api/admin/tours/{$this->tour->id}/schedules?date_from={$dateFrom}&date_to={$dateTo}");
        $response->assertStatus(200);
    }

    /** @test */
    public function cannot_delete_schedule_with_active_bookings()
    {
        Sanctum::actingAs($this->adminUser);

        $schedule = TourSchedule::factory()->create(['tour_id' => $this->tour->id]);
        
        // Create active booking
        Booking::factory()->create([
            'tour_schedule_id' => $schedule->id,
            'status' => 'confirmed'
        ]);

        $response = $this->deleteJson("/api/admin/tours/{$this->tour->id}/schedules/{$schedule->id}");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'No se puede eliminar el horario porque tiene reservas activas'
                ]);
    }

    /** @test */
    public function schedule_validates_end_time_after_start_time()
    {
        Sanctum::actingAs($this->adminUser);

        $invalidTimeData = [
            'date' => now()->addDays(1)->toDateString(),
            'start_time' => '17:00',
            'end_time' => '09:00', // Before start time
            'available_spots' => 5
        ];

        $response = $this->postJson("/api/admin/tours/{$this->tour->id}/schedules", $invalidTimeData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_time']);
    }
}