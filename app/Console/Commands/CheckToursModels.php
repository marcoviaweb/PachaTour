<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckToursModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:tours-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar que los modelos reorganizados funcionan correctamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando modelos reorganizados...');
        
        try {
            // Probar Tour
            $tour = \App\Features\Tours\Models\Tour::first();
            $this->info('✅ Tour model: ' . ($tour ? 'OK' : 'No hay datos'));
            
            // Probar TourSchedule
            $schedule = \App\Features\Tours\Models\TourSchedule::first();
            $this->info('✅ TourSchedule model: ' . ($schedule ? 'OK' : 'No hay datos'));
            
            // Probar Booking
            $booking = \App\Features\Tours\Models\Booking::first();
            $this->info('✅ Booking model: ' . ($booking ? 'OK' : 'No hay datos'));
            
            // Probar relaciones
            if ($tour && $tour->schedules) {
                $this->info('✅ Tour->schedules relationship: OK');
            }
            
            if ($tour && $tour->attractions) {
                $this->info('✅ Tour->attractions relationship: OK');
            }
            
            $this->info('🎉 ¡Reorganización exitosa!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
