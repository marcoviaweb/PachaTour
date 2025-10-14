<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBookingDates extends Command
{
    protected $signature = 'update:booking-dates';
    protected $description = 'Update booking dates to future dates';

    public function handle()
    {
        $this->info('🔄 Actualizando fechas de reservas...');
        
        // Actualizar schedule 1 (usado por reserva 46)
        $schedule1 = \App\Features\Tours\Models\TourSchedule::find(1);
        if ($schedule1) {
            $oldDate = $schedule1->date;
            $schedule1->date = '2025-10-20';
            $schedule1->save();
            $this->info("✅ Schedule 1: {$oldDate} → 2025-10-20");
        }

        // Actualizar schedule 2 (usado por reserva 47)  
        $schedule2 = \App\Features\Tours\Models\TourSchedule::find(2);
        if ($schedule2) {
            $oldDate = $schedule2->date;
            $schedule2->date = '2025-10-25';
            $schedule2->save();
            $this->info("✅ Schedule 2: {$oldDate} → 2025-10-25");
        }
        
        $this->newLine();
        $this->info('✅ Fechas actualizadas. Ahora las reservas serán futuras.');
    }
}