<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_destination_id')->nullable()->constrained('tour_destinations')->nullOnDelete();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->date('meal_date');
            $table->string('meal_time')->nullable();
            $table->foreignId('restaurant_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->enum('menu_type', ['standard', 'national', 'european', 'vegetarian', 'custom'])->default('standard');
            $table->text('menu_description')->nullable();
            $table->integer('pax_count')->default(1);
            $table->decimal('price_per_person_usd', 10, 2)->default(0);
            $table->decimal('total_price_usd', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
