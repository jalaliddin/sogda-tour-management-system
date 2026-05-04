<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->enum('city', ['toshkent', 'samarkand', 'bukhara', 'khiva', 'other'])->default('toshkent');
            $table->string('custom_city_name')->nullable();
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->integer('day_number')->default(1);
            $table->integer('nights_count')->default(1);
            $table->integer('order_index')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_destinations');
    }
};
