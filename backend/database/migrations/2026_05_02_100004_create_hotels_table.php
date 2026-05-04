<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counterparty_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('city');
            $table->string('address')->nullable();
            $table->tinyInteger('stars')->default(3);
            $table->enum('category', ['standard', 'superior', 'deluxe', 'suite'])->default('standard');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('check_in_time')->default('14:00');
            $table->string('check_out_time')->default('12:00');
            $table->json('room_types')->nullable();
            $table->json('amenities')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_own')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
