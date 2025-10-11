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
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_provider')->nullable()->after('remember_token');
            $table->string('social_id')->nullable()->after('social_provider');
            
            // Add index for social authentication
            $table->index(['social_provider', 'social_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['social_provider', 'social_id']);
            $table->dropColumn(['social_provider', 'social_id']);
        });
    }
};