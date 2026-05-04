<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['bus', 'minibus', 'car', 'train', 'internal_flight', 'transfer']);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('plate_number')->nullable();
            $table->integer('capacity')->default(0);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_license')->nullable();
            $table->boolean('is_own')->default(true);
            $table->foreignId('counterparty_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->enum('status', ['available', 'in_use', 'maintenance'])->default('available');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
