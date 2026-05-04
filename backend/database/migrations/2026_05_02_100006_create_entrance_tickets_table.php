<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_destination_id')->nullable()->constrained('tour_destinations')->nullOnDelete();
            $table->string('attraction_name');
            $table->string('city')->nullable();
            $table->enum('ticket_type', ['individual', 'group'])->default('individual');
            $table->date('visit_date');
            $table->string('visit_time')->nullable();
            $table->integer('pax_count')->default(1);
            $table->decimal('price_per_person_usd', 10, 2)->default(0);
            $table->decimal('total_price_usd', 12, 2)->default(0);
            $table->enum('booking_status', ['pending', 'booked', 'confirmed'])->default('pending');
            $table->string('supplier_name')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrance_tickets');
    }
};
