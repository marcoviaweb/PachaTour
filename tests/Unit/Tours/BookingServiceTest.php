<?php

namespace Tests\Unit\Tours;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Features\Tours\Services\BookingService;
use App\Models\User;
use App\Features\Tours\Models\Tour;
use App\Features\Tours\Models\TourSchedule;
use App\Features\Tours\Models\Booking;
use App\Features\Attractions\Models\Attraction;
use App\Features\Departments\Models\Department;
use Carbon\Carbon;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $bookingService;
    protected User $user;
    protected TourSchedule $schedule;
    protected Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = new BookingService();
        
        // Create test data
        $this->user = User::factory()->create(['role' => 'tourist']);
        $this->tour = Tour::factory()->create([
            'price_per_person' => 150.00
        ]);
        $this->schedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(3)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);
    }

    /** @test */
    public function can_create_booking_with_valid_data()
    {
        $bookingData = [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '+591 70123456'
        ];

        $booking = $this->bookingService->createBooking($bookingData);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($this->user->id, $booking->user_id);
        $this->assertEquals($this->schedule->id, $booking->tour_schedule_id);
        $this->assertEquals(2, $booking->participants_count);
        $this->assertEquals(300.00, $booking->total_amount); // 2 * 150.00
        $this->assertEquals(30.00, $booking->commission_amount); // 10% of 300.00
        $this->assertEquals(Booking::STATUS_PENDING, $booking->status);
        $this->assertNotNull($booking->booking_number);
    }

    /** @test */
    public function throws_exception_when_no_spots_available()
    {
        // Fill all spots
        $this->schedule->update([
            'booked_spots' => 10,
            'status' => TourSchedule::STATUS_FULL
        ]);

        $bookingData = [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Este horario no está disponible para reservas');

        $this->bookingService->createBooking($bookingData);
    }

    /** @test */
    public function throws_exception_for_past_date()
    {
        $pastSchedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->subDays(1)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        $bookingData = [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $pastSchedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se pueden hacer reservas para fechas pasadas');

        $this->bookingService->createBooking($bookingData);
    }

    /** @test */
    public function can_update_booking_with_valid_changes()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'participants_count' => 2,
            'total_amount' => 300.00
        ]);

        $updateData = [
            'participants_count' => 3,
            'contact_phone' => '+591 70987654',
            'notes' => 'Updated booking'
        ];

        $updatedBooking = $this->bookingService->updateBooking($booking, $updateData);

        $this->assertEquals(3, $updatedBooking->participants_count);
        $this->assertEquals(450.00, $updatedBooking->total_amount); // 3 * 150.00
        $this->assertEquals(45.00, $updatedBooking->commission_amount); // 10% of 450.00
        $this->assertEquals('+591 70987654', $updatedBooking->contact_phone);
        $this->assertEquals('Updated booking', $updatedBooking->notes);
    }

    /** @test */
    public function cannot_update_cancelled_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CANCELLED
        ]);

        $updateData = [
            'participants_count' => 3
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Esta reserva no puede ser modificada');

        $this->bookingService->updateBooking($booking, $updateData);
    }

    /** @test */
    public function can_cancel_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CONFIRMED,
            'participants_count' => 2
        ]);

        // Reserve spots first
        $this->schedule->update(['booked_spots' => 2]);

        $success = $this->bookingService->cancelBooking($booking, 'Cambio de planes');

        $this->assertTrue($success);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertEquals('Cambio de planes', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);

        // Check that spots were released
        $this->schedule->refresh();
        $this->assertEquals(0, $this->schedule->booked_spots);
    }

    /** @test */
    public function can_confirm_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'participants_count' => 2
        ]);

        $success = $this->bookingService->confirmBooking($booking);

        $this->assertTrue($success);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertNotNull($booking->confirmed_at);

        // Check that spots were reserved
        $this->schedule->refresh();
        $this->assertEquals(2, $this->schedule->booked_spots);
    }

    /** @test */
    public function cannot_confirm_booking_without_available_spots()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'participants_count' => 2
        ]);

        // Fill all spots
        $this->schedule->update(['booked_spots' => 10]);

        $success = $this->bookingService->confirmBooking($booking);

        $this->assertFalse($success);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PENDING, $booking->status);
    }

    /** @test */
    public function can_process_payment()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_PENDING
        ]);

        $success = $this->bookingService->processPayment($booking, 'Tarjeta de crédito', 'TXN123456');

        $this->assertTrue($success);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAID, $booking->status);
        $this->assertEquals(Booking::PAYMENT_PAID, $booking->payment_status);
        $this->assertEquals('Tarjeta de crédito', $booking->payment_method);
        $this->assertEquals('TXN123456', $booking->payment_reference);
    }

    /** @test */
    public function cannot_process_payment_for_already_paid_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'payment_status' => Booking::PAYMENT_PAID
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Esta reserva ya está pagada');

        $this->bookingService->processPayment($booking, 'Tarjeta de crédito', 'TXN123456');
    }

    /** @test */
    public function validates_availability_correctly()
    {
        // Should not throw exception for valid availability
        $this->bookingService->validateAvailability($this->schedule->id, 5);

        // Should throw exception when requesting more spots than available
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Solo quedan 10 cupos disponibles para este horario');

        $this->bookingService->validateAvailability($this->schedule->id, 15);
    }

    /** @test */
    public function calculates_amounts_correctly()
    {
        $amounts = $this->bookingService->calculateAmounts($this->schedule->id, 3);

        $this->assertEquals(450.00, $amounts['total']); // 3 * 150.00
        $this->assertEquals(10.00, $amounts['commission_rate']);
        $this->assertEquals(45.00, $amounts['commission']); // 10% of 450.00
        $this->assertEquals(150.00, $amounts['price_per_person']);
    }

    /** @test */
    public function can_check_if_booking_can_be_modified()
    {
        // Booking for future date should be modifiable
        $futureBooking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING
        ]);

        $this->assertTrue($this->bookingService->canBeModified($futureBooking));

        // Cancelled booking should not be modifiable
        $cancelledBooking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CANCELLED
        ]);

        $this->assertFalse($this->bookingService->canBeModified($cancelledBooking));

        // Booking close to tour date should not be modifiable
        $nearSchedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addHours(12)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        $nearBooking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $nearSchedule->id,
            'status' => Booking::STATUS_PENDING
        ]);

        $this->assertFalse($this->bookingService->canBeModified($nearBooking));
    }

    /** @test */
    public function can_get_user_bookings_with_filters()
    {
        // Create bookings with different statuses
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CONFIRMED
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CANCELLED
        ]);

        // Get all bookings
        $allBookings = $this->bookingService->getUserBookings($this->user->id);
        $this->assertEquals(3, $allBookings->total());

        // Filter by status
        $pendingBookings = $this->bookingService->getUserBookings($this->user->id, [
            'status' => Booking::STATUS_PENDING
        ]);
        $this->assertEquals(1, $pendingBookings->total());
    }

    /** @test */
    public function can_get_booking_statistics()
    {
        // Create test bookings
        Booking::factory()->count(3)->create([
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PAID,
            'payment_status' => Booking::PAYMENT_PAID,
            'total_amount' => 300.00,
            'commission_amount' => 30.00
        ]);

        Booking::factory()->count(2)->create([
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_PENDING,
            'total_amount' => 150.00,
            'commission_amount' => 15.00
        ]);

        $statistics = $this->bookingService->getBookingStatistics();

        $this->assertEquals(5, $statistics['total_bookings']);
        $this->assertEquals(900.00, $statistics['total_revenue']); // 3 * 300.00
        $this->assertEquals(90.00, $statistics['total_commissions']); // 3 * 30.00
        $this->assertEquals(180.00, $statistics['average_booking_value']); // 900 / 5
        $this->assertArrayHasKey('status_counts', $statistics);
        $this->assertArrayHasKey('payment_status_counts', $statistics);
    }
}