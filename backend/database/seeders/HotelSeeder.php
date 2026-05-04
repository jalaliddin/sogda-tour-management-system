<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Counterparty;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $khivaBranch = Branch::where('city', 'khiva')->first();
        $samarkandBranch = Branch::where('city', 'samarkand')->first();
        $bukharaBranch = Branch::where('city', 'bukhara')->first();
        $orientStar = Counterparty::where('company_name', 'Orient Star Hotels')->first();

        $hotels = [
            [
                'name' => 'Sogda Hotel Xiva', 'city' => 'khiva', 'address' => 'Xiva, Ichan-Qal\'a',
                'stars' => 4, 'category' => 'superior', 'phone' => '+998623456789',
                'email' => 'khiva@sogdatour.uz', 'check_in_time' => '14:00', 'check_out_time' => '12:00',
                'is_own' => true, 'is_active' => true, 'branch_id' => $khivaBranch?->id,
                'room_types' => [['type' => 'Standard', 'capacity' => 2, 'count' => 20], ['type' => 'Deluxe', 'capacity' => 2, 'count' => 10], ['type' => 'Suite', 'capacity' => 3, 'count' => 5]],
            ],
            [
                'name' => 'Sogda Hotel Samarkand', 'city' => 'samarkand', 'address' => 'Samarkand, Registon yaqini',
                'stars' => 4, 'category' => 'superior', 'phone' => '+998662345678',
                'email' => 'samarkand@sogdatour.uz', 'check_in_time' => '14:00', 'check_out_time' => '12:00',
                'is_own' => true, 'is_active' => true, 'branch_id' => $samarkandBranch?->id,
                'room_types' => [['type' => 'Standard', 'capacity' => 2, 'count' => 25], ['type' => 'Superior', 'capacity' => 2, 'count' => 15], ['type' => 'Suite', 'capacity' => 4, 'count' => 5]],
            ],
            [
                'name' => 'Sogda Hotel Buxoro', 'city' => 'bukhara', 'address' => 'Buxoro, Ark qal\'a yaqini',
                'stars' => 4, 'category' => 'superior', 'phone' => '+998652345678',
                'email' => 'bukhara@sogdatour.uz', 'check_in_time' => '14:00', 'check_out_time' => '12:00',
                'is_own' => true, 'is_active' => true, 'branch_id' => $bukharaBranch?->id,
                'room_types' => [['type' => 'Standard', 'capacity' => 2, 'count' => 20], ['type' => 'Deluxe', 'capacity' => 2, 'count' => 10], ['type' => 'Suite', 'capacity' => 3, 'count' => 4]],
            ],
            [
                'name' => 'Orient Star Samarkand', 'city' => 'samarkand', 'address' => 'Samarkand, Registon ko\'chasi',
                'stars' => 5, 'category' => 'deluxe', 'phone' => '+998 66 345 6789',
                'email' => 'info@orientstar.uz', 'check_in_time' => '15:00', 'check_out_time' => '12:00',
                'is_own' => false, 'is_active' => true, 'counterparty_id' => $orientStar?->id,
                'room_types' => [['type' => 'Standard', 'capacity' => 2, 'count' => 40], ['type' => 'Deluxe', 'capacity' => 2, 'count' => 20], ['type' => 'Suite', 'capacity' => 4, 'count' => 10]],
            ],
        ];

        foreach ($hotels as $hotel) {
            Hotel::firstOrCreate(['name' => $hotel['name']], $hotel);
        }
    }
}
