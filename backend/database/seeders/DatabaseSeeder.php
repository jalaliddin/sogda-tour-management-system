<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
            CounterpartySeeder::class,
            HotelSeeder::class,
            TransportSeeder::class,
            DestinationSeeder::class,
            TourSeeder::class,
        ]);
    }
}
