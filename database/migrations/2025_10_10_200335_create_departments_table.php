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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // Nombre del departamento
            $table->string('slug', 100)->unique(); // URL amigable
            $table->string('capital', 100); // Ciudad capital
            $table->text('description'); // Descripción del departamento
            $table->text('short_description')->nullable(); // Descripción corta
            $table->decimal('latitude', 10, 8)->nullable(); // Coordenadas GPS
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('image_path')->nullable(); // Imagen principal
            $table->json('gallery')->nullable(); // Galería de imágenes
            $table->integer('population')->nullable(); // Población aproximada
            $table->decimal('area_km2', 10, 2)->nullable(); // Área en km²
            $table->string('climate')->nullable(); // Tipo de clima
            $table->json('languages')->nullable(); // Idiomas hablados
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->timestamps();
            
            // Índices
            $table->index(['is_active', 'sort_order']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
