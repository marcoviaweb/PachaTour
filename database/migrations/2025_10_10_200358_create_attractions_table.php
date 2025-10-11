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
        Schema::create('attractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name', 200); // Nombre del atractivo
            $table->string('slug', 200)->unique(); // URL amigable
            $table->text('description'); // Descripción completa
            $table->text('short_description')->nullable(); // Descripción corta
            $table->enum('type', [
                'natural', 'cultural', 'historical', 'adventure', 
                'religious', 'archaeological', 'urban', 'gastronomic'
            ]); // Tipo de atractivo
            $table->decimal('latitude', 10, 8)->nullable(); // Coordenadas GPS
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable(); // Dirección específica
            $table->string('city', 100)->nullable(); // Ciudad/municipio
            $table->decimal('entry_price', 8, 2)->nullable(); // Precio de entrada
            $table->string('currency', 3)->default('BOB'); // Moneda
            $table->json('opening_hours')->nullable(); // Horarios de atención
            $table->json('contact_info')->nullable(); // Teléfono, email, web
            $table->string('difficulty_level')->nullable(); // Fácil, Moderado, Difícil
            $table->integer('estimated_duration')->nullable(); // Duración en minutos
            $table->json('amenities')->nullable(); // Servicios disponibles
            $table->json('restrictions')->nullable(); // Restricciones de acceso
            $table->string('best_season')->nullable(); // Mejor época para visitar
            $table->decimal('rating', 3, 2)->default(0); // Calificación promedio
            $table->integer('reviews_count')->default(0); // Número de reseñas
            $table->integer('visits_count')->default(0); // Contador de visitas
            $table->boolean('is_featured')->default(false); // Destacado
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->timestamps();
            
            // Índices
            $table->index(['department_id', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['is_featured', 'rating']);
            $table->index('slug');
            $table->index(['latitude', 'longitude']); // Para búsquedas geográficas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attractions');
    }
};
