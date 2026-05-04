<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::where('type', 'headquarters')->first();
        $khiva = Branch::where('city', 'khiva')->first();
        $samarkand = Branch::where('city', 'samarkand')->first();
        $bukhara = Branch::where('city', 'bukhara')->first();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@sogdatour.uz',
                'password' => Hash::make('Admin123!'),
                'department' => 'management',
                'position' => 'Tizim Administratori',
                'branch_id' => $hq?->id,
                'role' => 'super_admin',
            ],
            [
                'name' => 'Asosiy Menejer',
                'email' => 'manager@sogdatour.uz',
                'password' => Hash::make('Manager123!'),
                'department' => 'management',
                'position' => 'Tur Menejeri',
                'branch_id' => $hq?->id,
                'role' => 'manager',
            ],
            [
                'name' => 'Xiva Mehmonxona',
                'email' => 'hotel.khiva@sogdatour.uz',
                'password' => Hash::make('Hotel123!'),
                'department' => 'hotel_khiva',
                'position' => 'Mehmonxona Menejer',
                'branch_id' => $khiva?->id,
                'role' => 'hotel_khiva',
            ],
            [
                'name' => 'Samarkand Mehmonxona',
                'email' => 'hotel.samarkand@sogdatour.uz',
                'password' => Hash::make('Hotel123!'),
                'department' => 'hotel_samarkand',
                'position' => 'Mehmonxona Menejer',
                'branch_id' => $samarkand?->id,
                'role' => 'hotel_samarkand',
            ],
            [
                'name' => 'Buxoro Mehmonxona',
                'email' => 'hotel.bukhara@sogdatour.uz',
                'password' => Hash::make('Hotel123!'),
                'department' => 'hotel_bukhara',
                'position' => 'Mehmonxona Menejer',
                'branch_id' => $bukhara?->id,
                'role' => 'hotel_bukhara',
            ],
            [
                'name' => 'Transport Menejer',
                'email' => 'transport@sogdatour.uz',
                'password' => Hash::make('Transport123!'),
                'department' => 'transport',
                'position' => 'Transport Bo\'limi Boshlig\'i',
                'branch_id' => $hq?->id,
                'role' => 'transport_manager',
            ],
            [
                'name' => 'Viza Xodim',
                'email' => 'visa@sogdatour.uz',
                'password' => Hash::make('Visa123!'),
                'department' => 'visa',
                'position' => 'Viza Mutaxassisi',
                'branch_id' => $hq?->id,
                'role' => 'visa_officer',
            ],
            [
                'name' => 'Buxgalter',
                'email' => 'accountant@sogdatour.uz',
                'password' => Hash::make('Account123!'),
                'department' => 'accounting',
                'position' => 'Bosh Buxgalter',
                'branch_id' => $hq?->id,
                'role' => 'accountant',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles([$role]);
        }
    }
}
