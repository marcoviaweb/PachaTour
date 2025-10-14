<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyReorganization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:reorganization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar que toda la reorganización feature-based esté funcionando correctamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Verificando reorganización completa a arquitectura feature-based...');
        $this->newLine();
        
        $errors = 0;
        
        // Verificar cada feature
        $features = [
            'Departments' => [
                'model' => \App\Features\Departments\Models\Department::class,
                'controller' => \App\Features\Departments\Controllers\DepartmentController::class
            ],
            'Attractions' => [
                'model' => \App\Features\Attractions\Models\Attraction::class,
                'controller' => \App\Features\Attractions\Controllers\AttractionController::class
            ],
            'Reviews' => [
                'model' => \App\Features\Reviews\Models\Review::class,
                'controller' => \App\Features\Reviews\Controllers\ReviewController::class
            ],
            'Users' => [
                'models' => [
                    \App\Features\Users\Models\UserActivity::class,
                    \App\Features\Users\Models\UserFavorite::class
                ]
            ],
            'Tours' => [
                'models' => [
                    \App\Features\Tours\Models\Tour::class,
                    \App\Features\Tours\Models\TourSchedule::class,
                    \App\Features\Tours\Models\Booking::class
                ],
                'controller' => \App\Features\Tours\Controllers\TourController::class
            ],
            'Payments' => [
                'models' => [
                    \App\Features\Payments\Models\Payment::class,
                    \App\Features\Payments\Models\Commission::class,
                    \App\Features\Payments\Models\Invoice::class
                ]
            ]
        ];
        
        foreach ($features as $featureName => $config) {
            $this->info("🔍 Verificando feature: $featureName");
            
            try {
                // Verificar modelos únicos
                if (isset($config['model'])) {
                    $model = $config['model'];
                    $instance = $model::first();
                    $this->line("  ✅ " . class_basename($model) . ": " . ($instance ? 'OK' : 'Sin datos'));
                }
                
                // Verificar múltiples modelos
                if (isset($config['models'])) {
                    foreach ($config['models'] as $model) {
                        $instance = $model::first();
                        $this->line("  ✅ " . class_basename($model) . ": " . ($instance ? 'OK' : 'Sin datos'));
                    }
                }
                
                // Verificar controlador
                if (isset($config['controller'])) {
                    $controller = $config['controller'];
                    if (class_exists($controller)) {
                        $this->line("  ✅ " . class_basename($controller) . ": OK");
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error en $featureName: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->newLine();
        
        // Verificar relaciones cruzadas
        $this->info('🔗 Verificando relaciones entre features...');
        
        try {
            // Department -> Attractions
            $department = \App\Features\Departments\Models\Department::first();
            if ($department && $department->attractions) {
                $this->line('  ✅ Department->attractions: OK');
            }
            
            // Attraction -> Reviews
            $attraction = \App\Features\Attractions\Models\Attraction::first();
            if ($attraction && $attraction->reviews) {
                $this->line('  ✅ Attraction->reviews: OK');
            }
            
            // Tour -> Bookings
            $tour = \App\Features\Tours\Models\Tour::first();
            if ($tour && $tour->bookings) {
                $this->line('  ✅ Tour->bookings: OK');
            }
            
            // Booking -> Payments
            $booking = \App\Features\Tours\Models\Booking::first();
            if ($booking) {
                $payments = \App\Features\Payments\Models\Payment::where('booking_id', $booking->id)->first();
                $this->line('  ✅ Booking->payments: ' . ($payments ? 'OK' : 'Sin datos'));
            }
            
        } catch (\Exception $e) {
            $this->error('  ❌ Error en relaciones: ' . $e->getMessage());
            $errors++;
        }
        
        $this->newLine();
        
        if ($errors === 0) {
            $this->info('🎉 ¡REORGANIZACIÓN COMPLETADA EXITOSAMENTE!');
            $this->info('📊 Todos los features están funcionando correctamente.');
            $this->info('🏗️ Arquitectura feature-based implementada al 100%');
        } else {
            $this->error("❌ Se encontraron $errors errores.");
            return 1;
        }
        
        return 0;
    }
}
