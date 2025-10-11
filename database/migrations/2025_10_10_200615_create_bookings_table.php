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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 20)->unique(); // Número de reserva único
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tour_schedule_id')->constrained()->onDelete('cascade');
            $table->integer('participants_count'); // Número de participantes
            $table->decimal('total_amount', 10, 2); // Monto total
            $table->string('currency', 3)->default('BOB'); // Moneda
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Tasa de comisión %
            $table->decimal('commission_amount', 10, 2); // Monto de comisión
            $table->enum('status', [
                'pending', 'confirmed', 'paid', 'cancelled', 
                'completed', 'refunded', 'no_show'
            ])->default('pending');
            $table->enum('payment_status', [
                'pending', 'partial', 'paid', 'refunded', 'failed'
            ])->default('pending');
            $table->string('payment_method')->nullable(); // Método de pago
            $table->string('payment_reference')->nullable(); // Referencia de pago
            $table->json('participant_details')->nullable(); // Detalles de participantes
            $table->json('special_requests')->nullable(); // Solicitudes especiales
            $table->text('notes')->nullable(); // Notas adicionales
            $table->string('contact_name'); // Nombre de contacto
            $table->string('contact_email'); // Email de contacto
            $table->string('contact_phone')->nullable(); // Teléfono de contacto
            $table->string('emergency_contact_name')->nullable(); // Contacto de emergencia
            $table->string('emergency_contact_phone')->nullable(); // Teléfono de emergencia
            $table->timestamp('confirmed_at')->nullable(); // Fecha de confirmación
            $table->timestamp('cancelled_at')->nullable(); // Fecha de cancelación
            $table->string('cancellation_reason')->nullable(); // Razón de cancelación
            $table->decimal('refund_amount', 10, 2)->nullable(); // Monto reembolsado
            $table->timestamp('refunded_at')->nullable(); // Fecha de reembolso
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['tour_schedule_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index('booking_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
