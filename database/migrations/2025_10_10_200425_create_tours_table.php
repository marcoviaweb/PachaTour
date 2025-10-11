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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200); // Nombre del tour
            $table->string('slug', 200)->unique(); // URL amigable
            $table->text('description'); // Descripción completa
            $table->text('short_description')->nullable(); // Descripción corta
            $table->enum('type', [
                'day_trip', 'multi_day', 'adventure', 'cultural', 
                'nature', 'historical', 'gastronomic', 'photography'
            ]); // Tipo de tour
            $table->integer('duration_days')->default(1); // Duración en días
            $table->integer('duration_hours')->nullable(); // Duración en horas (para tours de día)
            $table->decimal('price_per_person', 10, 2); // Precio por persona
            $table->string('currency', 3)->default('BOB'); // Moneda
            $table->integer('min_participants')->default(1); // Mínimo de participantes
            $table->integer('max_participants')->default(20); // Máximo de participantes
            $table->enum('difficulty_level', ['easy', 'moderate', 'difficult', 'extreme']); // Dificultad
            $table->json('included_services')->nullable(); // Servicios incluidos
            $table->json('excluded_services')->nullable(); // Servicios no incluidos
            $table->json('requirements')->nullable(); // Requisitos para participar
            $table->json('what_to_bring')->nullable(); // Qué traer
            $table->string('meeting_point')->nullable(); // Punto de encuentro
            $table->time('departure_time')->nullable(); // Hora de salida
            $table->time('return_time')->nullable(); // Hora de regreso
            $table->json('itinerary')->nullable(); // Itinerario detallado
            $table->string('guide_language')->default('es'); // Idioma del guía
            $table->json('available_languages')->nullable(); // Idiomas disponibles
            $table->decimal('rating', 3, 2)->default(0); // Calificación promedio
            $table->integer('reviews_count')->default(0); // Número de reseñas
            $table->integer('bookings_count')->default(0); // Número de reservas
            $table->boolean('is_featured')->default(false); // Tour destacado
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->date('available_from')->nullable(); // Disponible desde
            $table->date('available_until')->nullable(); // Disponible hasta
            $table->timestamps();
            
            // Índices
            $table->index(['type', 'is_active']);
            $table->index(['is_featured', 'rating']);
            $table->index(['price_per_person', 'is_active']);
            $table->index('slug');
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
