<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create test data for bookings with pending payment status
        DB::table('bookings')->insert([
            [
                'booking_number' => 'BOOK-' . date('Ymd') . '-001',
                'user_id' => 1, // Assuming test user with ID 1
                'tour_schedule_id' => 1,
                'participants_count' => 2,
                'total_amount' => 350.00,
                'currency' => 'BOB',
                'commission_rate' => 10.00,
                'commission_amount' => 35.00,
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'payment_method' => null,
                'payment_reference' => null,
                'participant_details' => json_encode([
                    ['name' => 'Juan Pérez', 'document' => '12345678', 'age' => 30],
                    ['name' => 'María García', 'document' => '87654321', 'age' => 28]
                ]),
                'special_requests' => 'Dieta vegetariana para una persona',
                'contact_name' => 'Juan Pérez',
                'contact_email' => 'test@example.com',
                'contact_phone' => '+591 70123456',
                'emergency_contact_name' => 'Ana Pérez',
                'emergency_contact_phone' => '+591 75987654',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'booking_number' => 'BOOK-' . date('Ymd') . '-002',
                'user_id' => 1,
                'tour_schedule_id' => 2,
                'participants_count' => 1,
                'total_amount' => 180.00,
                'currency' => 'BOB',
                'commission_rate' => 15.00,
                'commission_amount' => 27.00,
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'payment_method' => null,
                'payment_reference' => null,
                'participant_details' => json_encode([
                    ['name' => 'Carlos López', 'document' => '11223344', 'age' => 35]
                ]),
                'contact_name' => 'Carlos López',
                'contact_email' => 'carlos@example.com',
                'contact_phone' => '+591 76543210',
                'emergency_contact_name' => 'Rosa López',
                'emergency_contact_phone' => '+591 78901234',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        DB::table('bookings')->where('booking_number', 'like', 'BOOK-' . date('Ymd') . '-%')->delete();
    }
};