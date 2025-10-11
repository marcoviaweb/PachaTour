<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('rate', 5, 4); // Porcentaje como decimal (0.1000 = 10%)
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'period_year', 'period_month']);
            $table->index(['tour_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};