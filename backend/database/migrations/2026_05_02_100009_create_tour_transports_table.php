<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_destination_id')->nullable()->constrained('tour_destinations')->nullOnDelete();
            $table->foreignId('transport_id')->nullable()->constrained('transports')->nullOnDelete();
            $table->foreignId('counterparty_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->string('route_from');
            $table->string('route_to');
            $table->date('transport_date');
            $table->string('departure_time')->nullable();
            $table->string('arrival_time')->nullable();
            $table->string('transport_type')->nullable();
            $table->string('vehicle_description')->nullable();
            $table->decimal('price_usd', 10, 2)->default(0);
            $table->boolean('is_own_fleet')->default(false);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_transports');
    }
};
