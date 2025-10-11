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
        Schema::table('attractions', function (Blueprint $table) {
            // Índices para optimizar búsquedas y filtros
            $table->index(['rating', 'is_active'], 'attractions_rating_active_index');
            $table->index(['difficulty_level', 'is_active'], 'attractions_difficulty_active_index');
            $table->index(['estimated_duration', 'is_active'], 'attractions_duration_active_index');
            $table->index(['is_featured', 'is_active', 'rating'], 'attractions_featured_active_rating_index');
            
            // Índice compuesto para búsquedas por texto (si la base de datos lo soporta)
            if (config('database.default') === 'pgsql') {
                // Para PostgreSQL, crear índice GIN para búsqueda de texto completo
                DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS attractions_search_gin_index ON attractions USING gin(to_tsvector(\'spanish\', name || \' \' || description))');
            }
        });

        Schema::table('tours', function (Blueprint $table) {
            // Índices para optimizar filtros de precio
            $table->index(['price_per_person', 'is_active'], 'tours_price_active_index');
            $table->index(['difficulty_level', 'is_active'], 'tours_difficulty_active_index');
            $table->index(['type', 'is_active'], 'tours_type_active_index');
        });

        Schema::table('tour_attraction', function (Blueprint $table) {
            // Índices para optimizar joins en filtros
            $table->index(['attraction_id', 'tour_id'], 'tour_attraction_compound_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attractions', function (Blueprint $table) {
            $table->dropIndex('attractions_rating_active_index');
            $table->dropIndex('attractions_difficulty_active_index');
            $table->dropIndex('attractions_duration_active_index');
            $table->dropIndex('attractions_featured_active_rating_index');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex('tours_price_active_index');
            $table->dropIndex('tours_difficulty_active_index');
            $table->dropIndex('tours_type_active_index');
        });

        Schema::table('tour_attraction', function (Blueprint $table) {
            $table->dropIndex('tour_attraction_compound_index');
        });

        // Eliminar índice GIN si existe
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS attractions_search_gin_index');
        }
    }
};