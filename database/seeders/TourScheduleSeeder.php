<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\TourSchedule;
use Carbon\Carbon;

class TourScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tours = Tour::all();
        
        foreach ($tours as $tour) {
            // Crear horarios para los próximos 3 meses
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addMonths(3);
            
            // Determinar frecuencia según el tipo de tour
            $frequency = $this->getTourFrequency($tour);
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Verificar si este tour opera en este día
                if ($this->shouldOperateOnDay($tour, $currentDate, $frequency)) {
                    // Crear 1-3 horarios por día según el tour
                    $schedulesPerDay = $this->getSchedulesPerDay($tour);
                    
                    for ($i = 0; $i < $schedulesPerDay; $i++) {
                        $startTime = $this->getStartTime($tour, $i);
                        $endTime = $this->getEndTime($tour, $startTime);
                        
                        // Verificar si ya existe este horario
                        $exists = TourSchedule::where('tour_id', $tour->id)
                            ->where('date', $currentDate->toDateString())
                            ->where('start_time', $startTime)
                            ->exists();
                            
                        if (!$exists) {
                            TourSchedule::create([
                                'tour_id' => $tour->id,
                                'date' => $currentDate->toDateString(),
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'available_spots' => $tour->max_participants,
                                'booked_spots' => fake()->numberBetween(0, min(3, $tour->max_participants)),
                                'status' => fake()->randomElement(['available', 'available', 'available', 'full']),
                                'notes' => fake()->optional(0.2)->sentence(),
                                'guide_name' => fake()->optional(0.8)->name(),
                                'guide_contact' => fake()->optional(0.6)->phoneNumber(),
                                'is_private' => fake()->boolean(10), // 10% privados
                                'weather_forecast' => fake()->randomFloat(1, 6.0, 9.5),
                                'weather_conditions' => fake()->randomElement([
                                    'Soleado', 'Parcialmente nublado', 'Nublado', 'Lluvia ligera'
                                ]),
                            ]);
                        }
                    }
                }
                
                $currentDate->addDay();
            }
        }

        $this->command->info('✅ Creados ' . TourSchedule::count() . ' horarios de tours');
    }

    /**
     * Determine tour frequency based on tour type and popularity
     */
    private function getTourFrequency(Tour $tour): string
    {
        if ($tour->is_featured) {
            return 'daily'; // Tours destacados operan diariamente
        }
        
        if (in_array($tour->type, ['day_trip', 'cultural'])) {
            return 'frequent'; // 4-5 días por semana
        }
        
        if (in_array($tour->type, ['adventure', 'multi_day'])) {
            return 'weekly'; // 2-3 días por semana
        }
        
        return 'occasional'; // 1-2 días por semana
    }

    /**
     * Determine if tour should operate on given day
     */
    private function shouldOperateOnDay(Tour $tour, Carbon $date, string $frequency): bool
    {
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        switch ($frequency) {
            case 'daily':
                return true; // Todos los días
                
            case 'frequent':
                return !in_array($dayOfWeek, [0, 1]); // No domingos ni lunes
                
            case 'weekly':
                return in_array($dayOfWeek, [5, 6, 0]); // Viernes, sábado, domingo
                
            case 'occasional':
                return in_array($dayOfWeek, [6, 0]); // Solo fines de semana
                
            default:
                return fake()->boolean(30); // 30% de probabilidad
        }
    }

    /**
     * Get number of schedules per day for a tour
     */
    private function getSchedulesPerDay(Tour $tour): int
    {
        if ($tour->duration_days > 1) {
            return 1; // Tours de varios días: 1 horario por día
        }
        
        if ($tour->is_featured) {
            return fake()->numberBetween(2, 3); // Tours destacados: 2-3 horarios
        }
        
        return fake()->numberBetween(1, 2); // Tours normales: 1-2 horarios
    }

    /**
     * Get start time for tour schedule
     */
    private function getStartTime(Tour $tour, int $scheduleIndex): string
    {
        $baseTimes = ['08:00', '10:00', '14:00', '16:00'];
        
        if ($tour->departure_time) {
            $baseTime = Carbon::parse($tour->departure_time);
        } else {
            $baseTime = Carbon::parse($baseTimes[$scheduleIndex] ?? '08:00');
        }
        
        // Agregar variación de ±30 minutos
        $variation = fake()->numberBetween(-30, 30);
        $finalTime = $baseTime->addMinutes($variation);
        
        return $finalTime->format('H:i');
    }

    /**
     * Get end time based on tour duration
     */
    private function getEndTime(Tour $tour, string $startTime): ?string
    {
        if ($tour->duration_days > 1) {
            return null; // Tours de varios días no tienen hora de fin específica
        }
        
        $start = Carbon::parse($startTime);
        
        if ($tour->duration_hours) {
            $end = $start->addHours($tour->duration_hours);
        } else {
            // Duración por defecto según tipo
            $defaultHours = match($tour->type) {
                'day_trip' => 8,
                'cultural' => 4,
                'adventure' => 6,
                'gastronomic' => 3,
                default => 5
            };
            $end = $start->addHours($defaultHours);
        }
        
        return $end->format('H:i');
    }
}