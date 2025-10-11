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
        Schema::create('tour_attraction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('attraction_id')->constrained()->onDelete('cascade');
            $table->integer('visit_order')->default(1); // Orden de visita en el tour
            $table->integer('duration_minutes')->nullable(); // Tiempo estimado en este atractivo
            $table->text('notes')->nullable(); // Notas específicas para este atractivo en el tour
            $table->boolean('is_optional')->default(false); // Visita opcional
            $table->time('arrival_time')->nullable(); // Hora estimada de llegada
            $table->time('departure_time')->nullable(); // Hora estimada de salida
            $table->timestamps();
            
            // Índices y constraints
            $table->unique(['tour_id', 'attraction_id']); // Un atractivo no puede repetirse en un tour
            $table->index(['tour_id', 'visit_order']);
            $table->index('attraction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_attraction');
    }
};
