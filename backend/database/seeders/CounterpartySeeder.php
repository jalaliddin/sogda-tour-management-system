<?php

namespace Database\Seeders;

use App\Models\Counterparty;
use Illuminate\Database\Seeder;

class CounterpartySeeder extends Seeder
{
    public function run(): void
    {
        $counterparties = [
            ['company_name' => 'China Travel International', 'type' => 'foreign_tour', 'country' => 'China', 'city' => 'Beijing', 'contact_person' => 'Wang Lei', 'phone' => '+86 10 6789 0123', 'email' => 'wang@cti.com', 'currency' => 'USD', 'rating' => 5],
            ['company_name' => 'German Tour GmbH', 'type' => 'foreign_tour', 'country' => 'Germany', 'city' => 'Berlin', 'contact_person' => 'Hans Mueller', 'phone' => '+49 30 1234 5678', 'email' => 'hans@germantour.de', 'currency' => 'EUR', 'rating' => 4],
            ['company_name' => 'Russia Tour LLC', 'type' => 'foreign_tour', 'country' => 'Russia', 'city' => 'Moscow', 'contact_person' => 'Ivan Petrov', 'phone' => '+7 495 123 4567', 'email' => 'ivan@russiatour.ru', 'currency' => 'RUB', 'rating' => 4],
            ['company_name' => 'Silk Road Travel Korea', 'type' => 'foreign_tour', 'country' => 'South Korea', 'city' => 'Seoul', 'contact_person' => 'Kim Jae-won', 'phone' => '+82 2 1234 5678', 'email' => 'kim@silkroad.kr', 'currency' => 'USD', 'rating' => 5],
            ['company_name' => 'France Voyages SARL', 'type' => 'foreign_tour', 'country' => 'France', 'city' => 'Paris', 'contact_person' => 'Pierre Dubois', 'phone' => '+33 1 2345 6789', 'email' => 'pierre@francevoyages.fr', 'currency' => 'EUR', 'rating' => 3],
            ['company_name' => 'Samarkand Registon Restoran', 'type' => 'restaurant', 'country' => 'Uzbekistan', 'city' => 'Samarkand', 'contact_person' => 'Akbar Umarov', 'phone' => '+998 66 234 5678', 'email' => 'registon@rest.uz', 'currency' => 'USD', 'rating' => 5],
            ['company_name' => 'Buxoro Osh Restaurant', 'type' => 'restaurant', 'country' => 'Uzbekistan', 'city' => 'Bukhara', 'contact_person' => 'Zulfiya Nazarova', 'phone' => '+998 65 234 5678', 'email' => 'osh@buxoro.uz', 'currency' => 'USD', 'rating' => 4],
            ['company_name' => 'Xiva National Food', 'type' => 'restaurant', 'country' => 'Uzbekistan', 'city' => 'Khiva', 'contact_person' => 'Rustam Yuldashev', 'phone' => '+998 62 234 5678', 'email' => 'food@xiva.uz', 'currency' => 'USD', 'rating' => 4],
            ['company_name' => 'UzTransport LLC', 'type' => 'transport', 'country' => 'Uzbekistan', 'city' => 'Tashkent', 'contact_person' => 'Bobur Tashmatov', 'phone' => '+998 71 234 5678', 'email' => 'info@uztransport.uz', 'currency' => 'USD', 'rating' => 4],
            ['company_name' => 'Tourist Guide Samarkand', 'type' => 'guide', 'country' => 'Uzbekistan', 'city' => 'Samarkand', 'contact_person' => 'Jasur Mirzaev', 'phone' => '+998 90 123 4567', 'email' => 'guide@samarkand.uz', 'currency' => 'USD', 'rating' => 5],
            ['company_name' => 'Tashkent Folklore Show', 'type' => 'folklore', 'country' => 'Uzbekistan', 'city' => 'Tashkent', 'contact_person' => 'Malika Rajabova', 'phone' => '+998 71 345 6789', 'email' => 'folklore@tashkent.uz', 'currency' => 'USD', 'rating' => 4],
            ['company_name' => 'Orient Star Hotels', 'type' => 'hotel', 'country' => 'Uzbekistan', 'city' => 'Samarkand', 'contact_person' => 'Dilshod Hamidov', 'phone' => '+998 66 345 6789', 'email' => 'info@orientstar.uz', 'currency' => 'USD', 'rating' => 4],
        ];

        foreach ($counterparties as $cp) {
            Counterparty::firstOrCreate(['company_name' => $cp['company_name']], $cp);
        }
    }
}
