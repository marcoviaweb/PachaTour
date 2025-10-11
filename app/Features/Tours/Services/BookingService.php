<?php

namespace App\Features\Tours\Services;

use App\Models\Booking;
use App\Models\TourSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingService
{
    /**
     * Create a new booking
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Validate availability
            $this->validateAvailability($data['tour_schedule_id'], $data['participants_count']);
            
            // Calculate amounts
            $amounts = $this->calculateAmounts($data['tour_schedule_id'], $data['participants_count']);
            
            // Create booking
            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'tour_schedule_id' => $data['tour_schedule_id'],
                'participants_count' => $data['participants_count'],
                'total_amount' => $amounts['total'],
                'currency' => $data['currency'] ?? 'BOB',
                'commission_rate' => $amounts['commission_rate'],
                'commission_amount' => $amounts['commission'],
                'status' => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_PENDING,
                'participant_details' => $data['participant_details'] ?? [],
                'special_requests' => $data['special_requests'] ?? [],
                'notes' => $data['notes'] ?? null,
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ]);
            
            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'user_id' => $booking->user_id,
                'tour_schedule_id' => $booking->tour_schedule_id,
                'participants_count' => $booking->participants_count,
                'total_amount' => $booking->total_amount
            ]);
            
            return $booking;
        });
    }
    
    /**
     * Update an existing booking
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            // Check if booking can be modified
            if (!$this->canBeModified($booking)) {
                throw new \Exception('Esta reserva no puede ser modificada');
            }
            
            $originalParticipants = $booking->participants_count;
            $originalScheduleId = $booking->tour_schedule_id;
            
            // If changing schedule or participants, validate new availability
            if (isset($data['tour_schedule_id']) && $data['tour_schedule_id'] != $originalScheduleId) {
                $this->validateAvailability($data['tour_schedule_id'], $data['participants_count'] ?? $originalParticipants);
            } elseif (isset($data['participants_count']) && $data['participants_count'] != $originalParticipants) {
                $this->validateAvailability($originalScheduleId, $data['participants_count'], $booking->id);
            }
            
            // Recalculate amounts if participants or schedule changed
            if (isset($data['participants_count']) || isset($data['tour_schedule_id'])) {
                $scheduleId = $data['tour_schedule_id'] ?? $originalScheduleId;
                $participants = $data['participants_count'] ?? $originalParticipants;
                $amounts = $this->calculateAmounts($scheduleId, $participants);
                
                $data['total_amount'] = $amounts['total'];
                $data['commission_rate'] = $amounts['commission_rate'];
                $data['commission_amount'] = $amounts['commission'];
            }
            
            $booking->update($data);
            
            Log::info('Booking updated', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'changes' => $data
            ]);
            
            return $booking->fresh();
        });
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, string $reason = null): bool
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!$booking->can_be_cancelled) {
                throw new \Exception('Esta reserva no puede ser cancelada');
            }
            
            $success = $booking->cancel($reason);
            
            if ($success) {
                Log::info('Booking cancelled', [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'reason' => $reason
                ]);
            }
            
            return $success;
        });
    }
    
    /**
     * Confirm a booking
     */
    public function confirmBooking(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            $success = $booking->confirm();
            
            if ($success) {
                Log::info('Booking confirmed', [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number
                ]);
                
                // Send confirmation email
                $booking->sendConfirmationEmail();
            }
            
            return $success;
        });
    }
    
    /**
     * Process payment for a booking
     */
    public function processPayment(Booking $booking, string $paymentMethod, string $reference = null): bool
    {
        return DB::transaction(function () use ($booking, $paymentMethod, $reference) {
            if ($booking->payment_status === Booking::PAYMENT_PAID) {
                throw new \Exception('Esta reserva ya está pagada');
            }
            
            $booking->markAsPaid($paymentMethod, $reference);
            
            Log::info('Booking payment processed', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'payment_method' => $paymentMethod,
                'reference' => $reference
            ]);
            
            return true;
        });
    }
    
    /**
     * Validate availability for a tour schedule
     */
    public function validateAvailability(int $tourScheduleId, int $participantsCount, int $excludeBookingId = null): void
    {
        $schedule = TourSchedule::findOrFail($tourScheduleId);
        
        // Check if schedule is in the future
        if ($schedule->start_date_time->isPast()) {
            throw new \Exception('No se pueden hacer reservas para fechas pasadas');
        }
        
        // Check if schedule is active
        if (!$schedule->canBeBooked()) {
            throw new \Exception('Este horario no está disponible para reservas');
        }
        
        // Check available spots
        $remainingSpots = $schedule->getRemainingSpots($excludeBookingId);
        if ($remainingSpots < $participantsCount) {
            throw new \Exception("Solo quedan {$remainingSpots} cupos disponibles para este horario");
        }
    }
    
    /**
     * Calculate booking amounts
     */
    public function calculateAmounts(int $tourScheduleId, int $participantsCount): array
    {
        $schedule = TourSchedule::with('tour')->findOrFail($tourScheduleId);
        $tour = $schedule->tour;
        
        $pricePerPerson = $tour->price_per_person;
        $totalAmount = $pricePerPerson * $participantsCount;
        
        // Commission rate (could be configurable per tour or globally)
        $commissionRate = 10.00; // 10% default
        $commissionAmount = ($totalAmount * $commissionRate) / 100;
        
        return [
            'total' => $totalAmount,
            'commission_rate' => $commissionRate,
            'commission' => $commissionAmount,
            'price_per_person' => $pricePerPerson
        ];
    }
    
    /**
     * Check if booking can be modified
     */
    public function canBeModified(Booking $booking): bool
    {
        // Cannot modify cancelled, completed, or refunded bookings
        if (in_array($booking->status, [
            Booking::STATUS_CANCELLED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_REFUNDED,
            Booking::STATUS_NO_SHOW
        ])) {
            return false;
        }
        
        // Cannot modify if tour is in less than 24 hours
        $tourDateTime = $booking->tourSchedule->start_date_time;
        return $tourDateTime->gt(now()->addHours(24));
    }
    
    /**
     * Get user bookings with filters
     */
    public function getUserBookings(int $userId, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Booking::with(['tourSchedule.tour', 'user'])
            ->forUser($userId);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->withStatus($filters['status']);
        }
        
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereHas('tourSchedule', function ($q) use ($filters) {
                $q->where('date', '>=', $filters['date_from']);
            });
        }
        
        if (isset($filters['date_to'])) {
            $query->whereHas('tourSchedule', function ($q) use ($filters) {
                $q->where('date', '<=', $filters['date_to']);
            });
        }
        
        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
    
    /**
     * Get booking statistics for admin
     */
    public function getBookingStatistics(array $filters = []): array
    {
        $query = Booking::query();
        
        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        $totalBookings = $query->count();
        $totalRevenue = $query->where('payment_status', Booking::PAYMENT_PAID)->sum('total_amount');
        $totalCommissions = $query->where('payment_status', Booking::PAYMENT_PAID)->sum('commission_amount');
        
        $statusCounts = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $paymentStatusCounts = $query->select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();
        
        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'total_commissions' => $totalCommissions,
            'status_counts' => $statusCounts,
            'payment_status_counts' => $paymentStatusCounts,
            'average_booking_value' => $totalBookings > 0 ? $totalRevenue / $totalBookings : 0,
        ];
    }
    
    /**
     * Send booking reminders for upcoming tours
     */
    public function sendUpcomingReminders(): int
    {
        $bookings = Booking::with(['tourSchedule', 'user'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PAID])
            ->whereHas('tourSchedule', function ($q) {
                $q->whereBetween('start_date_time', [
                    now()->addHours(24),
                    now()->addHours(48)
                ]);
            })
            ->get();
        
        $sent = 0;
        foreach ($bookings as $booking) {
            try {
                $booking->sendReminder();
                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send booking reminder', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info("Sent {$sent} booking reminders");
        
        return $sent;
    }
}