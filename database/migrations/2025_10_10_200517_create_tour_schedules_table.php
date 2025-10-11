<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tour_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Fecha del tour
            $table->time('start_time'); // Hora de inicio
            $table->time('end_time')->nullable(); // Hora de finalización
            $table->integer('available_spots'); // Cupos disponibles
            $table->integer('booked_spots')->default(0); // Cupos reservados
            $table->decimal('price_override', 10, 2)->nullable(); // Precio especial para esta fecha
            $table->enum('status', ['available', 'full', 'cancelled', 'completed'])->default('available');
            $table->text('notes')->nullable(); // Notas especiales para esta fecha
            $table->json('special_conditions')->nullable(); // Condiciones especiales
            $table->string('guide_name')->nullable(); // Nombre del guía asignado
            $table->string('guide_contact')->nullable(); // Contacto del guía
            $table->boolean('is_private')->default(false); // Tour privado
            $table->decimal('weather_forecast', 3, 1)->nullable(); // Pronóstico del clima
            $table->string('weather_conditions')->nullable(); // Condiciones climáticas
            $table->timestamps();
            
            // Índices
            $table->index(['tour_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['tour_id', 'status']);
            $table->unique(['tour_id', 'date', 'start_time']); // Un tour no puede tener dos horarios iguales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_schedules');
    }
};
