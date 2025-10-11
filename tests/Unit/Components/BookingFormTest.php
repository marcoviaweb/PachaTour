<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\Booking;
use App\Features\Tours\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BookingFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tour;
    protected $schedule;
    protected $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'tourist'
        ]);
        
        $this->tour = Tour::factory()->create([
            'name' => 'Test Tour',
            'price_per_person' => 100.00,
            'max_participants' => 10,
            'is_active' => true
        ]);
        
        $this->schedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'available_spots' => 8,
            'booked_spots' => 0,
            'status' => 'available'
        ]);
        
        $this->bookingService = app(BookingService::class);
    }

    /** @test */
    public function it_can_validate_availability_for_booking()
    {
        // Test valid availability
        $this->expectNotToPerformAssertions();
        $this->bookingService->validateAvailability($this->schedule->id, 2);
    }

    /** @test */
    public function it_throws_exception_when_not_enough_spots_available()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Solo quedan 8 cupos disponibles para este horario');
        
        $this->bookingService->validateAvailability($this->schedule->id, 10);
    }

    /** @test */
    public function it_throws_exception_for_past_dates()
    {
        $pastSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => Carbon::yesterday(),
            'start_time' => '09:00:00',
            'available_spots' => 5,
            'status' => 'available'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se pueden hacer reservas para fechas pasadas');
        
        $this->bookingService->validateAvailability($pastSchedule->id, 2);
    }

    /** @test */
    public function it_calculates_booking_amounts_correctly()
    {
        $amounts = $this->bookingService->calculateAmounts($this->schedule->id, 3);
        
        $this->assertEquals(300.00, $amounts['total']); // 100 * 3
        $this->assertEquals(10.00, $amounts['commission_rate']);
        $this->assertEquals(30.00, $amounts['commission']); // 10% of 300
        $this->assertEquals(100.00, $amounts['price_per_person']);
    }

    /** @test */
    public function it_can_create_booking_with_valid_data()
    {
        $bookingData = [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'John Doe',
            'contact_email' => 'john@example.com',
            'contact_phone' => '+591 12345678',
            'participant_details' => [
                ['name' => 'John Doe', 'age' => 30],
                ['name' => 'Jane Doe', 'age' => 28]
            ]
        ];

        $booking = $this->bookingService->createBooking($bookingData);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($this->user->id, $booking->user_id);
        $this->assertEquals($this->schedule->id, $booking->tour_schedule_id);
        $this->assertEquals(2, $booking->participants_count);
        $this->assertEquals(200.00, $booking->total_amount);
        $this->assertEquals('pending', $booking->status);
        $this->assertNotNull($booking->booking_number);
    }

    /** @test */
    public function it_validates_participant_count_limits()
    {
        $this->expectException(\Exception::class);
        
        $bookingData = [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 15, // Exceeds tour max_participants (10)
            'contact_name' => 'John Doe',
            'contact_email' => 'john@example.com'
        ];

        $this->bookingService->createBooking($bookingData);
    }

    /** @test */
    public function it_can_update_booking_when_allowed()
    {
        // Create a schedule far enough in the future to allow modifications
        $futureSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => Carbon::now()->addDays(3),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'available_spots' => 8,
            'booked_spots' => 0,
            'status' => 'available'
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $futureSchedule->id,
            'participants_count' => 2,
            'status' => 'pending'
        ]);

        $updateData = [
            'participants_count' => 3,
            'contact_phone' => '+591 87654321'
        ];

        $updatedBooking = $this->bookingService->updateBooking($booking, $updateData);

        $this->assertEquals(3, $updatedBooking->participants_count);
        $this->assertEquals('+591 87654321', $updatedBooking->contact_phone);
        $this->assertEquals(300.00, $updatedBooking->total_amount); // Recalculated
    }

    /** @test */
    public function it_cannot_update_completed_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => 'completed'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Esta reserva no puede ser modificada');

        $this->bookingService->updateBooking($booking, ['participants_count' => 3]);
    }

    /** @test */
    public function it_can_cancel_booking_when_allowed()
    {
        // Create a schedule far enough in the future to allow cancellation
        $futureSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => Carbon::now()->addDays(3),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'available_spots' => 8,
            'booked_spots' => 2,
            'status' => 'available'
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $futureSchedule->id,
            'status' => 'confirmed'
        ]);

        $result = $this->bookingService->cancelBooking($booking, 'Change of plans');

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals('Change of plans', $booking->fresh()->cancellation_reason);
    }

    /** @test */
    public function it_can_confirm_pending_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'status' => 'pending'
        ]);

        $result = $this->bookingService->confirmBooking($booking);

        $this->assertTrue($result);
        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->confirmed_at);
        
        // Check that spots were reserved
        $this->assertEquals(2, $this->schedule->fresh()->booked_spots);
    }

    /** @test */
    public function it_can_process_payment()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
            'payment_status' => 'pending'
        ]);

        $result = $this->bookingService->processPayment($booking, 'credit_card', 'TXN123456');

        $this->assertTrue($result);
        $this->assertEquals('paid', $booking->fresh()->status);
        $this->assertEquals('paid', $booking->fresh()->payment_status);
        $this->assertEquals('credit_card', $booking->fresh()->payment_method);
        $this->assertEquals('TXN123456', $booking->fresh()->payment_reference);
    }

    /** @test */
    public function it_gets_user_bookings_with_filters()
    {
        // Create multiple bookings
        Booking::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'confirmed'
        ]);
        
        Booking::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Test without filters
        $allBookings = $this->bookingService->getUserBookings($this->user->id);
        $this->assertEquals(5, $allBookings->total());

        // Test with status filter
        $confirmedBookings = $this->bookingService->getUserBookings($this->user->id, [
            'status' => 'confirmed'
        ]);
        $this->assertEquals(3, $confirmedBookings->total());
    }

    /** @test */
    public function it_generates_booking_statistics()
    {
        // Create test bookings
        Booking::factory()->create([
            'total_amount' => 200.00,
            'commission_amount' => 20.00,
            'payment_status' => 'paid',
            'status' => 'confirmed'
        ]);
        
        Booking::factory()->create([
            'total_amount' => 150.00,
            'commission_amount' => 15.00,
            'payment_status' => 'paid',
            'status' => 'completed'
        ]);

        $stats = $this->bookingService->getBookingStatistics();

        $this->assertEquals(2, $stats['total_bookings']);
        $this->assertEquals(350.00, $stats['total_revenue']);
        $this->assertEquals(35.00, $stats['total_commissions']);
        $this->assertEquals(175.00, $stats['average_booking_value']);
    }

    /** @test */
    public function it_validates_booking_modification_time_limit()
    {
        // Create booking for tour in less than 24 hours
        $nearSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => Carbon::now()->addHours(12)->toDateString(),
            'start_time' => Carbon::now()->addHours(12)->format('H:i:s'),
            'available_spots' => 5,
            'status' => 'available'
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $nearSchedule->id,
            'status' => 'confirmed'
        ]);

        $canModify = $this->bookingService->canBeModified($booking);
        $this->assertFalse($canModify);
    }
}