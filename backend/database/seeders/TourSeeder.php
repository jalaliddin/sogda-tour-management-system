<?php

namespace Database\Seeders;

use App\Models\Counterparty;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourHotel;
use App\Models\User;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'manager@sogdatour.uz')->first();
        $chinaTour = Counterparty::where('type', 'foreign_tour')->where('country', 'China')->first();
        $germanyTour = Counterparty::where('type', 'foreign_tour')->where('country', 'Germany')->first();
        $koreaTour = Counterparty::where('type', 'foreign_tour')->where('country', 'South Korea')->first();

        $khivaHotel = Hotel::where('city', 'khiva')->where('is_own', true)->first();
        $samarkandHotel = Hotel::where('city', 'samarkand')->where('is_own', true)->first();
        $bukharaHotel = Hotel::where('city', 'bukhara')->where('is_own', true)->first();

        $tours = [
            [
                'tour_name' => 'China Group - Uzbekistan Discovery',
                'tour_code' => 'SGD-2025-001',
                'country' => 'China',
                'counterparty_id' => $chinaTour?->id,
                'assigned_staff_id' => $manager?->id,
                'start_date' => '2025-06-10',
                'end_date' => '2025-06-20',
                'duration_days' => 10,
                'pax_count' => 25,
                'pax_adults' => 22,
                'pax_children' => 3,
                'arrival_type' => 'airport',
                'arrival_flight_number' => 'HU-7931',
                'arrival_flight_time' => '10:30',
                'arrival_terminal' => 'T2',
                'departure_type' => 'airport',
                'departure_flight_number' => 'HU-7932',
                'departure_flight_time' => '15:00',
                'status' => 'confirmed',
                'total_price_usd' => 18750.00,
                'currency' => 'USD',
                'created_by' => $manager?->id,
                'confirmed_by' => $manager?->id,
                'confirmed_at' => now()->subDays(5),
            ],
            [
                'tour_name' => 'Germany Kulturreise Usbekistan',
                'tour_code' => 'SGD-2025-002',
                'country' => 'Germany',
                'counterparty_id' => $germanyTour?->id,
                'assigned_staff_id' => $manager?->id,
                'start_date' => '2025-07-05',
                'end_date' => '2025-07-14',
                'duration_days' => 9,
                'pax_count' => 18,
                'pax_adults' => 18,
                'pax_children' => 0,
                'arrival_type' => 'airport',
                'arrival_flight_number' => 'LH-9341',
                'arrival_flight_time' => '08:15',
                'departure_type' => 'airport',
                'departure_flight_number' => 'LH-9342',
                'departure_flight_time' => '19:30',
                'status' => 'draft',
                'total_price_usd' => 14400.00,
                'currency' => 'USD',
                'created_by' => $manager?->id,
            ],
            [
                'tour_name' => 'Korea Silk Road Tour',
                'tour_code' => 'SGD-2025-003',
                'country' => 'South Korea',
                'counterparty_id' => $koreaTour?->id,
                'assigned_staff_id' => $manager?->id,
                'start_date' => '2025-05-20',
                'end_date' => '2025-05-28',
                'duration_days' => 8,
                'pax_count' => 30,
                'pax_adults' => 28,
                'pax_children' => 2,
                'arrival_type' => 'airport',
                'arrival_flight_number' => 'KE-873',
                'arrival_flight_time' => '06:45',
                'departure_type' => 'airport',
                'departure_flight_number' => 'KE-874',
                'departure_flight_time' => '22:00',
                'status' => 'in_progress',
                'total_price_usd' => 22500.00,
                'currency' => 'USD',
                'created_by' => $manager?->id,
                'confirmed_by' => $manager?->id,
                'confirmed_at' => now()->subDays(15),
            ],
            [
                'tour_name' => 'China Spring Tour',
                'tour_code' => 'SGD-2025-004',
                'country' => 'China',
                'counterparty_id' => $chinaTour?->id,
                'assigned_staff_id' => $manager?->id,
                'start_date' => '2025-04-10',
                'end_date' => '2025-04-19',
                'duration_days' => 9,
                'pax_count' => 20,
                'pax_adults' => 20,
                'pax_children' => 0,
                'arrival_type' => 'airport',
                'arrival_flight_number' => 'CA-1234',
                'arrival_flight_time' => '12:00',
                'departure_type' => 'airport',
                'departure_flight_number' => 'CA-1235',
                'departure_flight_time' => '14:00',
                'status' => 'completed',
                'total_price_usd' => 15000.00,
                'currency' => 'USD',
                'created_by' => $manager?->id,
                'confirmed_by' => $manager?->id,
                'confirmed_at' => now()->subDays(40),
            ],
            [
                'tour_name' => 'Germany Summer Group',
                'tour_code' => 'SGD-2025-005',
                'country' => 'Germany',
                'counterparty_id' => $germanyTour?->id,
                'assigned_staff_id' => $manager?->id,
                'start_date' => '2025-08-15',
                'end_date' => '2025-08-24',
                'duration_days' => 9,
                'pax_count' => 22,
                'pax_adults' => 20,
                'pax_children' => 2,
                'arrival_type' => 'airport',
                'arrival_flight_number' => 'LH-9343',
                'arrival_flight_time' => '09:00',
                'departure_type' => 'airport',
                'departure_flight_number' => 'LH-9344',
                'departure_flight_time' => '20:00',
                'status' => 'draft',
                'total_price_usd' => 17600.00,
                'currency' => 'USD',
                'created_by' => $manager?->id,
            ],
        ];

        foreach ($tours as $tourData) {
            $tour = Tour::firstOrCreate(['tour_code' => $tourData['tour_code']], $tourData);

            if ($tour->destinations()->count() === 0) {
                $dest1 = TourDestination::create([
                    'tour_id' => $tour->id,
                    'city' => 'toshkent',
                    'arrival_date' => $tour->start_date,
                    'departure_date' => $tour->start_date->addDays(1),
                    'day_number' => 1,
                    'nights_count' => 1,
                    'order_index' => 0,
                ]);

                $dest2 = TourDestination::create([
                    'tour_id' => $tour->id,
                    'city' => 'samarkand',
                    'arrival_date' => $tour->start_date->addDays(1),
                    'departure_date' => $tour->start_date->addDays(4),
                    'day_number' => 2,
                    'nights_count' => 3,
                    'order_index' => 1,
                ]);

                $dest3 = TourDestination::create([
                    'tour_id' => $tour->id,
                    'city' => 'bukhara',
                    'arrival_date' => $tour->start_date->addDays(4),
                    'departure_date' => $tour->start_date->addDays(7),
                    'day_number' => 5,
                    'nights_count' => 3,
                    'order_index' => 2,
                ]);

                if ($samarkandHotel) {
                    TourHotel::create([
                        'tour_id' => $tour->id,
                        'tour_destination_id' => $dest2->id,
                        'hotel_id' => $samarkandHotel->id,
                        'check_in_date' => $dest2->arrival_date,
                        'check_out_date' => $dest2->departure_date,
                        'room_type' => 'Standard',
                        'room_count' => (int)ceil($tour->pax_count / 2),
                        'pax_per_room' => 2,
                        'price_per_night_usd' => 80,
                        'total_price_usd' => 80 * 3 * ceil($tour->pax_count / 2),
                        'status' => $tour->status === 'confirmed' || $tour->status === 'in_progress' || $tour->status === 'completed' ? 'confirmed' : 'pending',
                    ]);
                }

                if ($bukharaHotel) {
                    TourHotel::create([
                        'tour_id' => $tour->id,
                        'tour_destination_id' => $dest3->id,
                        'hotel_id' => $bukharaHotel->id,
                        'check_in_date' => $dest3->arrival_date,
                        'check_out_date' => $dest3->departure_date,
                        'room_type' => 'Standard',
                        'room_count' => (int)ceil($tour->pax_count / 2),
                        'pax_per_room' => 2,
                        'price_per_night_usd' => 75,
                        'total_price_usd' => 75 * 3 * ceil($tour->pax_count / 2),
                        'status' => $tour->status === 'confirmed' || $tour->status === 'in_progress' || $tour->status === 'completed' ? 'confirmed' : 'pending',
                    ]);
                }
            }
        }
    }
}
