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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->enum('role', ['visitor', 'tourist', 'admin'])->default('visitor');
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('preferred_language', 5)->default('es');
            $table->json('interests')->nullable(); // Intereses turísticos
            $table->text('bio')->nullable(); // Biografía/descripción
            $table->string('avatar_path')->nullable(); // Foto de perfil
            $table->boolean('newsletter_subscription')->default(false);
            $table->boolean('marketing_emails')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // Índices
            $table->index('role');
            $table->index(['is_active', 'role']);
            $table->index('nationality');
            $table->index('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
