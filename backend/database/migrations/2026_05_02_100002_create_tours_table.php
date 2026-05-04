<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('tour_name');
            $table->string('tour_code')->unique();
            $table->string('country')->nullable();
            $table->foreignId('counterparty_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->default(1);
            $table->integer('pax_count')->default(0);
            $table->integer('pax_adults')->default(0);
            $table->integer('pax_children')->default(0);
            $table->enum('arrival_type', ['airport', 'kpp'])->nullable();
            $table->string('arrival_flight_number')->nullable();
            $table->string('arrival_flight_time')->nullable();
            $table->string('arrival_terminal')->nullable();
            $table->enum('departure_type', ['airport', 'kpp'])->nullable();
            $table->string('departure_flight_number')->nullable();
            $table->string('departure_flight_time')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->decimal('total_price_usd', 12, 2)->default(0);
            $table->decimal('total_price_uzs', 16, 2)->default(0);
            $table->enum('currency', ['USD', 'EUR', 'UZS', 'RUB'])->default('USD');
            $table->text('notes')->nullable();
            $table->text('special_requests')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
