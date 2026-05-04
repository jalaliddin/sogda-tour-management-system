<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_destination_id')->nullable()->constrained('tour_destinations')->nullOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->string('check_in_time')->default('14:00');
            $table->date('check_out_date');
            $table->string('check_out_time')->default('12:00');
            $table->string('room_type')->nullable();
            $table->integer('room_count')->default(1);
            $table->integer('pax_per_room')->default(2);
            $table->decimal('price_per_night_usd', 10, 2)->default(0);
            $table->decimal('total_price_usd', 12, 2)->default(0);
            $table->enum('status', ['pending', 'waiting_list', 'ok', 'confirmed', 'cancelled'])->default('pending');
            $table->string('hotel_confirmation_number')->nullable();
            $table->text('notes')->nullable();
            $table->date('booking_date')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_hotels');
    }
};
