<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop the foreign key constraint
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_schedule_id']);
        });
        
        // Step 2: Make tour_schedule_id nullable using raw SQL to avoid Doctrine issues
        DB::statement('ALTER TABLE bookings ALTER COLUMN tour_schedule_id DROP NOT NULL');
        
        // Step 3: Re-add the foreign key constraint allowing nulls
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('tour_schedule_id')
                  ->references('id')
                  ->on('tour_schedules')
                  ->onDelete('cascade');
        });
        
        // Step 4: Add planning_data column
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('planning_data')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('planning_data');
            $table->dropForeign(['tour_schedule_id']);
        });
        
        DB::statement('ALTER TABLE bookings ALTER COLUMN tour_schedule_id SET NOT NULL');
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('tour_schedule_id')
                  ->references('id')
                  ->on('tour_schedules')
                  ->onDelete('cascade');
        });
    }
};