<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            // Uzbekistan
            ['name' => 'Tashkent', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Tashkent', 'type' => 'city', 'airport_code' => 'TAS', 'timezone' => 'Asia/Tashkent', 'description' => 'Capital city of Uzbekistan', 'attractions' => ['Chorsu Bazaar', 'Tashkent Tower', 'Independence Square', 'Navoi Theatre'], 'sort_order' => 1],
            ['name' => 'Samarkand', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Samarkand', 'type' => 'historical', 'airport_code' => 'SKD', 'timezone' => 'Asia/Tashkent', 'description' => 'Ancient Silk Road city', 'attractions' => ['Registan Square', 'Shah-i-Zinda', 'Gur-e-Amir', 'Bibi-Khanym Mosque'], 'sort_order' => 2],
            ['name' => 'Bukhara', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Bukhara', 'type' => 'historical', 'airport_code' => 'BHK', 'timezone' => 'Asia/Tashkent', 'description' => 'Historic city on the Silk Road', 'attractions' => ['Ark Fortress', 'Kalon Minaret', 'Lyabi-Hauz', 'Poi Kalon Complex'], 'sort_order' => 3],
            ['name' => 'Khiva', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Khorezm', 'type' => 'historical', 'airport_code' => 'UGC', 'timezone' => 'Asia/Tashkent', 'description' => 'UNESCO-listed walled city', 'attractions' => ['Ichan Kala', 'Kalta Minor', 'Islam Khoja Minaret', 'Tash-Hauli Palace'], 'sort_order' => 4],
            ['name' => 'Nukus', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Karakalpakstan', 'type' => 'city', 'airport_code' => 'NCU', 'timezone' => 'Asia/Tashkent', 'attractions' => ['Savitsky Museum', 'Aral Sea'], 'sort_order' => 5],
            ['name' => 'Fergana', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Fergana', 'type' => 'city', 'airport_code' => 'FEG', 'timezone' => 'Asia/Tashkent', 'attractions' => ['Kuva Buddhist Temple', 'Yodgorlik Silk Factory'], 'sort_order' => 6],
            ['name' => 'Termez', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Surkhandarya', 'type' => 'historical', 'airport_code' => 'TMJ', 'timezone' => 'Asia/Tashkent', 'attractions' => ['Fayaz-Tepe', 'Kara-Tepe Buddhist Monastery'], 'sort_order' => 7],
            ['name' => 'Shakhrisabz', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Kashkadarya', 'type' => 'historical', 'timezone' => 'Asia/Tashkent', 'attractions' => ['Ak-Saray Palace', 'Dorut Tilavat Complex'], 'sort_order' => 8],
            ['name' => 'Namangan', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Namangan', 'type' => 'city', 'airport_code' => 'NMA', 'timezone' => 'Asia/Tashkent', 'sort_order' => 9],
            ['name' => 'Andijan', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Andijan', 'type' => 'city', 'airport_code' => 'AZN', 'timezone' => 'Asia/Tashkent', 'sort_order' => 10],
            // Border crossings
            ['name' => 'Gisht Kuprik', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Tashkent', 'type' => 'border_crossing', 'timezone' => 'Asia/Tashkent', 'description' => 'Kazakhstan border crossing', 'sort_order' => 20],
            ['name' => 'Dustlik', 'country' => 'Uzbekistan', 'country_code' => 'UZB', 'region' => 'Fergana', 'type' => 'border_crossing', 'timezone' => 'Asia/Tashkent', 'description' => 'Kyrgyzstan border crossing', 'sort_order' => 21],
            // Kazakhstan
            ['name' => 'Almaty', 'country' => 'Kazakhstan', 'country_code' => 'KAZ', 'region' => 'Almaty', 'type' => 'city', 'airport_code' => 'ALA', 'timezone' => 'Asia/Almaty', 'attractions' => ['Medeu Ice Rink', 'Shymbulak Resort', 'Green Bazaar'], 'sort_order' => 30],
            ['name' => 'Astana', 'country' => 'Kazakhstan', 'country_code' => 'KAZ', 'region' => 'Astana', 'type' => 'city', 'airport_code' => 'TSE', 'timezone' => 'Asia/Almaty', 'attractions' => ['Bayterek Tower', 'Palace of Peace'], 'sort_order' => 31],
            // Kyrgyzstan
            ['name' => 'Bishkek', 'country' => 'Kyrgyzstan', 'country_code' => 'KGZ', 'region' => 'Bishkek', 'type' => 'city', 'airport_code' => 'FRU', 'timezone' => 'Asia/Bishkek', 'sort_order' => 40],
            ['name' => 'Osh', 'country' => 'Kyrgyzstan', 'country_code' => 'KGZ', 'region' => 'Osh', 'type' => 'historical', 'airport_code' => 'OSS', 'timezone' => 'Asia/Bishkek', 'attractions' => ['Sulayman Mountain'], 'sort_order' => 41],
            // Tajikistan
            ['name' => 'Dushanbe', 'country' => 'Tajikistan', 'country_code' => 'TJK', 'region' => 'Dushanbe', 'type' => 'city', 'airport_code' => 'DYU', 'timezone' => 'Asia/Dushanbe', 'sort_order' => 50],
            // Turkmenistan
            ['name' => 'Ashgabat', 'country' => 'Turkmenistan', 'country_code' => 'TKM', 'region' => 'Ashgabat', 'type' => 'city', 'airport_code' => 'ASB', 'timezone' => 'Asia/Ashgabat', 'sort_order' => 60],
            ['name' => 'Mary', 'country' => 'Turkmenistan', 'country_code' => 'TKM', 'region' => 'Mary', 'type' => 'historical', 'timezone' => 'Asia/Ashgabat', 'attractions' => ['Merv Ancient City'], 'sort_order' => 61],
        ];

        foreach ($destinations as $dest) {
            Destination::firstOrCreate(
                ['name' => $dest['name'], 'country' => $dest['country']],
                $dest
            );
        }
    }
}
