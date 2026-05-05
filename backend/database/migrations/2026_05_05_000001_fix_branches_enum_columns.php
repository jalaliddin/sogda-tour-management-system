<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE branches MODIFY city VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE branches MODIFY type VARCHAR(50) NOT NULL DEFAULT 'branch'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE branches MODIFY city ENUM('toshkent','samarkand','bukhara','khiva') NOT NULL");
        DB::statement("ALTER TABLE branches MODIFY type ENUM('headquarters','hotel','office') NOT NULL DEFAULT 'office'");
    }
};
