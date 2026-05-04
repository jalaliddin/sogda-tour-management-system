<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Sogda Tour HQ', 'city' => 'toshkent', 'address' => 'Toshkent shahar, Yunusobod tumani', 'phone' => '+998712345678', 'email' => 'info@sogdatour.uz', 'manager_name' => 'Direktor', 'type' => 'headquarters'],
            ['name' => 'Sogda Hotel Xiva', 'city' => 'khiva', 'address' => 'Xiva shahar, Ichan-Qal\'a yaqini', 'phone' => '+998623456789', 'email' => 'khiva@sogdatour.uz', 'manager_name' => 'Hamid Yusupov', 'type' => 'hotel'],
            ['name' => 'Sogda Hotel Samarkand', 'city' => 'samarkand', 'address' => 'Samarkand shahar, Registon yaqini', 'phone' => '+998662345678', 'email' => 'samarkand@sogdatour.uz', 'manager_name' => 'Nodira Karimova', 'type' => 'hotel'],
            ['name' => 'Sogda Hotel Buxoro', 'city' => 'bukhara', 'address' => 'Buxoro shahar, Ark qal\'a yaqini', 'phone' => '+998652345678', 'email' => 'bukhara@sogdatour.uz', 'manager_name' => 'Jahongir Toshmatov', 'type' => 'hotel'],
            ['name' => 'Sogda Transport Markazi', 'city' => 'toshkent', 'address' => 'Toshkent shahar, Chilonzor tumani', 'phone' => '+998712345679', 'email' => 'transport@sogdatour.uz', 'manager_name' => 'Sardor Mirzaev', 'type' => 'office'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch['name']], $branch);
        }
    }
}
