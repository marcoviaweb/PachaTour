<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\TourSchedule;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', User::ROLE_ADMIN)->get();
        $schedules = TourSchedule::where('date', '>=', now()->subDays(30))->get();

        if ($users->isEmpty() || $schedules->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios o horarios disponibles para crear reservas');
            return;
        }

        // Crear reservas confirmadas (pasadas)
        $pastSchedules = $schedules->where('date', '<', now()->toDateString());
        foreach ($pastSchedules->random(min(20, $pastSchedules->count())) as $schedule) {
            $user = $users->random();
            
            Booking::factory()
                ->completed()
                ->create([
                    'user_id' => $user->id,
                    'tour_schedule_id' => $schedule->id,
                ]);
        }

        // Crear reservas futuras confirmadas
        $futureSchedules = $schedules->where('date', '>=', now()->toDateString());
        foreach ($futureSchedules->random(min(15, $futureSchedules->count())) as $schedule) {
            $user = $users->random();
            
            Booking::factory()
                ->confirmed()
                ->create([
                    'user_id' => $user->id,
                    'tour_schedule_id' => $schedule->id,
                ]);
        }

        // Crear algunas reservas pendientes
        foreach ($futureSchedules->random(min(8, $futureSchedules->count())) as $schedule) {
            $user = $users->random();
            
            Booking::factory()
                ->pending()
                ->create([
                    'user_id' => $user->id,
                    'tour_schedule_id' => $schedule->id,
                ]);
        }

        // Crear algunas reservas canceladas
        foreach ($schedules->random(min(5, $schedules->count())) as $schedule) {
            $user = $users->random();
            
            Booking::factory()
                ->cancelled()
                ->create([
                    'user_id' => $user->id,
                    'tour_schedule_id' => $schedule->id,
                ]);
        }

        // Actualizar booked_spots en los schedules
        $this->updateScheduleBookedSpots();

        $this->command->info('✅ Creadas ' . Booking::count() . ' reservas de prueba');
    }

    /**
     * Update booked_spots count in tour schedules
     */
    private function updateScheduleBookedSpots(): void
    {
        $schedules = TourSchedule::withCount([
            'bookings' => function ($query) {
                $query->whereIn('status', ['confirmed', 'paid', 'completed']);
            }
        ])->get();

        foreach ($schedules as $schedule) {
            $schedule->update([
                'booked_spots' => $schedule->bookings_count,
                'status' => $schedule->bookings_count >= $schedule->available_spots ? 'full' : 'available'
            ]);
        }
    }
}