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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable'); // Polimórfico: puede ser department, attraction, tour, etc.
            $table->string('name'); // Nombre del archivo
            $table->string('file_name'); // Nombre del archivo en disco
            $table->string('mime_type'); // Tipo MIME
            $table->enum('type', ['image', 'video', 'audio', 'document']); // Tipo de media
            $table->string('disk')->default('public'); // Disco de almacenamiento
            $table->string('path'); // Ruta del archivo
            $table->string('url')->nullable(); // URL pública del archivo
            $table->unsignedBigInteger('size'); // Tamaño en bytes
            $table->json('metadata')->nullable(); // Metadatos (dimensiones, duración, etc.)
            $table->string('alt_text')->nullable(); // Texto alternativo para imágenes
            $table->text('description')->nullable(); // Descripción del archivo
            $table->string('caption')->nullable(); // Leyenda/caption
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->boolean('is_featured')->default(false); // Imagen/video destacado
            $table->boolean('is_public')->default(true); // Archivo público o privado
            $table->string('uploaded_by_type')->nullable(); // Tipo de usuario que subió
            $table->unsignedBigInteger('uploaded_by_id')->nullable(); // ID del usuario que subió
            $table->json('transformations')->nullable(); // Transformaciones aplicadas (thumbnails, etc.)
            $table->string('original_name')->nullable(); // Nombre original del archivo
            $table->string('hash')->nullable(); // Hash del archivo para evitar duplicados
            $table->timestamps();
            
            // Índices (morphs() ya crea el índice para mediable_type, mediable_id)
            $table->index(['type', 'is_public']);
            $table->index(['is_featured', 'sort_order']);
            $table->index('hash');
            $table->index(['uploaded_by_type', 'uploaded_by_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
