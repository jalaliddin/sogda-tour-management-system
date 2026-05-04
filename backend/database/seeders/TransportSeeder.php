<?php

namespace Database\Seeders;

use App\Models\Transport;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        $transports = [
            ['type' => 'bus', 'brand' => 'Mercedes', 'model' => 'Travego', 'plate_number' => '01 A 123 AA', 'capacity' => 50, 'driver_name' => 'Murod Holmatov', 'driver_phone' => '+998 90 123 4567', 'is_own' => true, 'status' => 'available'],
            ['type' => 'bus', 'brand' => 'Hyundai', 'model' => 'Universe', 'plate_number' => '01 A 456 BB', 'capacity' => 45, 'driver_name' => 'Sherzod Qodirov', 'driver_phone' => '+998 91 234 5678', 'is_own' => true, 'status' => 'available'],
            ['type' => 'minibus', 'brand' => 'Mercedes', 'model' => 'Sprinter', 'plate_number' => '01 B 789 CC', 'capacity' => 16, 'driver_name' => 'Behruz Usmonov', 'driver_phone' => '+998 93 345 6789', 'is_own' => true, 'status' => 'available'],
            ['type' => 'minibus', 'brand' => 'Ford', 'model' => 'Transit', 'plate_number' => '01 B 012 DD', 'capacity' => 14, 'driver_name' => 'Sanjar Ergashev', 'driver_phone' => '+998 94 456 7890', 'is_own' => true, 'status' => 'available'],
            ['type' => 'car', 'brand' => 'Toyota', 'model' => 'Camry', 'plate_number' => '01 C 345 EE', 'capacity' => 4, 'driver_name' => 'Dilshod Nazarov', 'driver_phone' => '+998 95 567 8901', 'is_own' => true, 'status' => 'available'],
        ];

        foreach ($transports as $transport) {
            Transport::firstOrCreate(['plate_number' => $transport['plate_number']], $transport);
        }
    }
}
