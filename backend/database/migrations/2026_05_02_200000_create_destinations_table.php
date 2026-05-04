<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // City/place name
            $table->string('country');             // Country
            $table->string('country_code', 3)->nullable(); // ISO code
            $table->string('region')->nullable();  // Region/province
            $table->enum('type', ['city', 'resort', 'historical', 'nature', 'border_crossing', 'other'])->default('city');
            $table->string('airport_code', 10)->nullable();
            $table->string('timezone')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('attractions')->nullable(); // Popular sights
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
