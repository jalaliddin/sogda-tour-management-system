<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change city from ENUM to VARCHAR(255) to accept any city name
        DB::statement("ALTER TABLE tour_destinations MODIFY city VARCHAR(255) NOT NULL DEFAULT 'other'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tour_destinations SET city = 'other'
            WHERE city NOT IN ('toshkent', 'samarkand', 'bukhara', 'khiva', 'other')");
        DB::statement("ALTER TABLE tour_destinations MODIFY city ENUM('toshkent','samarkand','bukhara','khiva','other') NOT NULL DEFAULT 'toshkent'");
    }
};
