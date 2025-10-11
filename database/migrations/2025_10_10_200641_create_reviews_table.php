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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('reviewable'); // Polimórfico: puede ser tour, attraction, etc.
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null'); // Reserva relacionada
            $table->decimal('rating', 3, 2); // Calificación (1.00 - 5.00)
            $table->string('title', 200)->nullable(); // Título de la reseña
            $table->text('comment'); // Comentario de la reseña
            $table->json('detailed_ratings')->nullable(); // Calificaciones detalladas (servicio, limpieza, etc.)
            $table->json('pros')->nullable(); // Aspectos positivos
            $table->json('cons')->nullable(); // Aspectos negativos
            $table->boolean('would_recommend')->nullable(); // ¿Recomendaría?
            $table->string('visit_date')->nullable(); // Fecha de la visita
            $table->string('travel_type')->nullable(); // Tipo de viaje (familia, pareja, solo, etc.)
            $table->enum('status', ['pending', 'approved', 'rejected', 'hidden'])->default('pending');
            $table->text('moderation_notes')->nullable(); // Notas de moderación
            $table->timestamp('moderated_at')->nullable(); // Fecha de moderación
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('helpful_votes')->default(0); // Votos de "útil"
            $table->integer('not_helpful_votes')->default(0); // Votos de "no útil"
            $table->boolean('is_verified')->default(false); // Reseña verificada
            $table->string('language', 5)->default('es'); // Idioma de la reseña
            $table->timestamps();
            
            // Índices
            $table->index(['reviewable_type', 'reviewable_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['rating', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
