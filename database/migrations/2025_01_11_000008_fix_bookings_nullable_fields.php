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
        // Make commission_amount nullable for planning bookings
        DB::statement('ALTER TABLE bookings ALTER COLUMN commission_amount DROP NOT NULL');
        
        // Set default value for existing records
        DB::statement('UPDATE bookings SET commission_amount = 0 WHERE commission_amount IS NULL');
        
        // Make tour_schedule_id nullable if not already done
        try {
            DB::statement('ALTER TABLE bookings ALTER COLUMN tour_schedule_id DROP NOT NULL');
        } catch (Exception $e) {
            // Already nullable, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set commission_amount back to NOT NULL
        DB::statement('UPDATE bookings SET commission_amount = 0 WHERE commission_amount IS NULL');
        DB::statement('ALTER TABLE bookings ALTER COLUMN commission_amount SET NOT NULL');
        
        // Set tour_schedule_id back to NOT NULL (only if no null values exist)
        $nullCount = DB::table('bookings')->whereNull('tour_schedule_id')->count();
        if ($nullCount === 0) {
            DB::statement('ALTER TABLE bookings ALTER COLUMN tour_schedule_id SET NOT NULL');
        }
    }
};