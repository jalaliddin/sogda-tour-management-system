<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntranceTicket;
use App\Models\Meal;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourHotel;
use App\Models\TourTransport;
use App\Models\Visa;
use App\Services\TourPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::with(['counterparty', 'assignedStaff', 'destinations']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }
        if ($request->country) {
            $query->where('country', $request->country);
        }
        if ($request->assigned_to) {
            $query->where('assigned_staff_id', $request->assigned_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('tour_name', 'like', "%{$request->search}%")
                    ->orWhere('tour_code', 'like', "%{$request->search}%");
            });
        }

        $tours = $query->orderByDesc('created_at')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $tours->items(),
            'meta' => [
                'total' => $tours->total(),
                'per_page' => $tours->perPage(),
                'current_page' => $tours->currentPage(),
                'last_page' => $tours->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tour_name' => 'required|string|max:255',
            'country' => 'required|string',
            'pax_count' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['destinations', 'hotels', 'transports', 'meals', 'tickets', 'visas']);
            $data['tour_code'] = $this->generateTourCode();
            $data['created_by'] = $request->user()->id;
            $data['duration_days'] = (int) ((new \DateTime($data['end_date']))->diff(new \DateTime($data['start_date']))->days) + 1;

            $tour = Tour::create($data);

            $savedDestinations = $this->syncDestinations($tour, $request->destinations ?? []);
            $this->syncHotels($tour, $request->hotels ?? [], $savedDestinations);
            $this->syncTransports($tour, $request->transports ?? []);
            $this->syncMeals($tour, $request->meals ?? [], $request->pax_count ?? 1);
            $this->syncTickets($tour, $request->tickets ?? [], $request->pax_count ?? 1);
            $this->syncVisas($tour, $request->visas ?? []);
            $this->recalcTourTotal($tour);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $tour->load(['destinations', 'hotels.hotel', 'transports', 'meals', 'entranceTickets', 'visas', 'counterparty', 'assignedStaff']),
                'message' => 'Tur muvaffaqiyatli yaratildi.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Tour $tour)
    {
        $tour->load([
            'counterparty', 'assignedStaff', 'createdBy', 'confirmedBy',
            'destinations',
            'hotels.hotel', 'hotels.destination',
            'transports.transport', 'transports.counterparty',
            'meals.restaurant', 'meals.destination',
            'entranceTickets.destination',
            'visas',
            'documents',
        ]);

        return response()->json(['success' => true, 'data' => $tour]);
    }

    public function update(Request $request, Tour $tour)
    {
        $request->validate([
            'tour_name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['tour_code', 'created_by', 'destinations', 'hotels', 'transports', 'meals', 'tickets', 'visas']);

            if (isset($data['start_date']) && isset($data['end_date'])) {
                $data['duration_days'] = (int) ((new \DateTime($data['end_date']))->diff(new \DateTime($data['start_date']))->days) + 1;
            }

            $tour->update($data);

            if ($request->has('destinations')) {
                $tour->destinations()->delete();
                $savedDestinations = $this->syncDestinations($tour, $request->destinations);
            } else {
                $savedDestinations = $tour->destinations()->orderBy('order_index')->get()->all();
            }

            if ($request->has('hotels')) {
                $tour->hotels()->delete();
                $this->syncHotels($tour, $request->hotels, $savedDestinations);
            }

            if ($request->has('transports')) {
                $tour->transports()->delete();
                $this->syncTransports($tour, $request->transports);
            }

            if ($request->has('meals')) {
                $tour->meals()->delete();
                $this->syncMeals($tour, $request->meals, $tour->pax_count ?? 1);
            }

            if ($request->has('tickets')) {
                $tour->entranceTickets()->delete();
                $this->syncTickets($tour, $request->tickets, $tour->pax_count ?? 1);
            }

            if ($request->has('visas')) {
                $tour->visas()->delete();
                $this->syncVisas($tour, $request->visas);
            }

            $this->recalcTourTotal($tour);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $tour->load(['destinations', 'hotels.hotel', 'transports', 'meals', 'entranceTickets', 'visas', 'counterparty', 'assignedStaff']),
                'message' => 'Tur yangilandi.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Tour $tour)
    {
        if ($tour->status !== 'draft') {
            return response()->json(['success' => false, 'message' => "Faqat draft holatdagi turlarni o'chirish mumkin."], 422);
        }

        $tour->delete();

        return response()->json(['success' => true, 'message' => "Tur o'chirildi."]);
    }

    public function confirm(Request $request, Tour $tour)
    {
        if ($tour->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Faqat draft turlarni tasdiqlash mumkin.'], 422);
        }

        $tour->update([
            'status' => 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $tour->fresh(),
            'message' => 'Tur tasdiqlandi.',
        ]);
    }

    public function duplicate(Tour $tour)
    {
        DB::beginTransaction();
        try {
            $newTour = $tour->replicate();
            $newTour->tour_code = $this->generateTourCode();
            $newTour->status = 'draft';
            $newTour->confirmed_by = null;
            $newTour->confirmed_at = null;
            $newTour->save();

            $destMap = [];
            foreach ($tour->destinations as $dest) {
                $newDest = $dest->replicate();
                $newDest->tour_id = $newTour->id;
                $newDest->save();
                $destMap[$dest->id] = $newDest->id;
            }

            foreach ($tour->hotels as $hotel) {
                $newHotel = $hotel->replicate();
                $newHotel->tour_id = $newTour->id;
                $newHotel->tour_destination_id = $hotel->tour_destination_id ? ($destMap[$hotel->tour_destination_id] ?? null) : null;
                $newHotel->status = 'pending';
                $newHotel->hotel_confirmation_number = null;
                $newHotel->save();
            }

            foreach ($tour->transports as $tr) {
                $newTr = $tr->replicate();
                $newTr->tour_id = $newTour->id;
                $newTr->tour_destination_id = $tr->tour_destination_id ? ($destMap[$tr->tour_destination_id] ?? null) : null;
                $newTr->save();
            }

            foreach ($tour->meals as $meal) {
                $newMeal = $meal->replicate();
                $newMeal->tour_id = $newTour->id;
                $newMeal->tour_destination_id = $meal->tour_destination_id ? ($destMap[$meal->tour_destination_id] ?? null) : null;
                $newMeal->save();
            }

            foreach ($tour->entranceTickets as $ticket) {
                $newTicket = $ticket->replicate();
                $newTicket->tour_id = $newTour->id;
                $newTicket->tour_destination_id = $ticket->tour_destination_id ? ($destMap[$ticket->tour_destination_id] ?? null) : null;
                $newTicket->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $newTour->load(['destinations', 'hotels', 'transports', 'meals', 'entranceTickets']),
                'message' => 'Tur nusxalandi.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function generatePdf(Request $request, Tour $tour)
    {
        $lang = in_array($request->query('lang'), ['ru', 'en']) ? $request->query('lang') : 'ru';

        $service = new TourPdfService;
        $pdf = $service->generateTourDocument($tour->id, $lang);

        return $pdf->download("tour-{$tour->tour_code}-{$lang}.pdf");
    }

    public function timeline(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->endOfMonth()->toDateString();

        $tours = Tour::whereBetween('start_date', [$from, $to])
            ->orWhereBetween('end_date', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'tour_code', 'tour_name', 'country', 'start_date', 'end_date', 'status', 'pax_count']);

        return response()->json(['success' => true, 'data' => $tours]);
    }

    /** @param array<int, array<string, mixed>> $destinationsData */
    private function syncDestinations(Tour $tour, array $destinationsData): array
    {
        $saved = [];
        foreach ($destinationsData as $index => $dest) {
            $arrivalDate = $dest['arrival_date'] ?? null;
            $departureDate = $dest['departure_date'] ?? null;
            $nights = ($arrivalDate && $departureDate)
                ? (int) ((new \DateTime($departureDate))->diff(new \DateTime($arrivalDate))->days)
                : ($dest['nights_count'] ?? 0);

            $saved[] = TourDestination::create([
                'tour_id' => $tour->id,
                'city' => $dest['city'] ?? 'other',
                'custom_city_name' => $dest['custom_city_name'] ?? null,
                'arrival_date' => $arrivalDate,
                'departure_date' => $departureDate,
                'day_number' => $index + 1,
                'nights_count' => $nights,
                'order_index' => $index,
                'notes' => $dest['notes'] ?? null,
            ]);
        }

        return $saved;
    }

    /** @param array<int, array<string, mixed>> $hotelsData */
    private function syncHotels(Tour $tour, array $hotelsData, array $savedDestinations): void
    {
        foreach ($hotelsData as $index => $hotel) {
            if (empty($hotel['hotel_id'])) {
                continue;
            }

            $dest = $savedDestinations[$index] ?? null;
            $nights = $dest ? ($dest->nights_count ?: 1) : 1;
            $roomCount = (int) ($hotel['room_count'] ?? 1);
            $pricePerNight = (float) ($hotel['price_per_night_usd'] ?? 0);
            $total = $pricePerNight * $roomCount * $nights;

            TourHotel::create([
                'tour_id' => $tour->id,
                'tour_destination_id' => $dest?->id ?? null,
                'hotel_id' => $hotel['hotel_id'],
                'room_type' => $hotel['room_type'] ?? 'Standard',
                'room_count' => $roomCount,
                'price_per_night_usd' => $pricePerNight,
                'total_price_usd' => $total,
                'check_in_date' => $dest?->arrival_date ?? null,
                'check_out_date' => $dest?->departure_date ?? null,
                'status' => 'pending',
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $transportsData */
    private function syncTransports(Tour $tour, array $transportsData): void
    {
        foreach ($transportsData as $tr) {
            TourTransport::create([
                'tour_id' => $tour->id,
                'transport_type' => $tr['transport_type'] ?? 'bus',
                'route_from' => $tr['route_from'] ?? '',
                'route_to' => $tr['route_to'] ?? '',
                'transport_date' => $tr['transport_date'] ?? null,
                'departure_time' => $tr['departure_time'] ?? null,
                'price_usd' => $tr['price_usd'] ?? 0,
                'is_own_fleet' => (bool) ($tr['is_own_fleet'] ?? false),
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $mealsData */
    private function syncMeals(Tour $tour, array $mealsData, int $paxCount): void
    {
        foreach ($mealsData as $meal) {
            $pricePerPerson = (float) ($meal['price_per_person_usd'] ?? 0);
            $pax = (int) ($meal['pax_count'] ?? $paxCount);
            Meal::create([
                'tour_id' => $tour->id,
                'meal_type' => $meal['meal_type'] ?? 'lunch',
                'meal_date' => $meal['meal_date'] ?? null,
                'meal_time' => $meal['meal_time'] ?? null,
                'restaurant_id' => $meal['restaurant_id'] ?? null,
                'menu_type' => $meal['menu_type'] ?? 'standard',
                'price_per_person_usd' => $pricePerPerson,
                'total_price_usd' => $pricePerPerson * $pax,
                'pax_count' => $pax,
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $ticketsData */
    private function syncTickets(Tour $tour, array $ticketsData, int $paxCount): void
    {
        foreach ($ticketsData as $ticket) {
            if (empty($ticket['attraction_name'])) {
                continue;
            }

            $pricePerPerson = (float) ($ticket['price_per_person_usd'] ?? 0);
            $pax = (int) ($ticket['pax_count'] ?? $paxCount);
            EntranceTicket::create([
                'tour_id' => $tour->id,
                'attraction_name' => $ticket['attraction_name'],
                'city' => $ticket['city'] ?? '',
                'visit_date' => $ticket['visit_date'] ?? null,
                'visit_time' => $ticket['visit_time'] ?? null,
                'pax_count' => $pax,
                'price_per_person_usd' => $pricePerPerson,
                'total_price_usd' => $pricePerPerson * $pax,
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $visasData */
    private function syncVisas(Tour $tour, array $visasData): void
    {
        foreach ($visasData as $visa) {
            if (empty($visa['applicant_name'])) {
                continue;
            }

            Visa::create([
                'tour_id' => $tour->id,
                'applicant_name' => $visa['applicant_name'],
                'passport_number' => $visa['passport_number'] ?? '',
                'passport_expiry' => $visa['passport_expiry'] ?: null,
                'nationality' => $visa['nationality'] ?? '',
                'visa_type' => $visa['visa_type'] ?? 'tourist',
                'status' => 'pending',
            ]);
        }
    }

    private function recalcTourTotal(Tour $tour): void
    {
        $tour->refresh();

        $hotelCost = $tour->hotels()->sum('total_price_usd');
        $transportCost = $tour->transports()->sum('price_usd');
        $mealCost = $tour->meals()->sum('total_price_usd');
        $ticketCost = $tour->entranceTickets()->sum('total_price_usd');

        $total = (float)$hotelCost + (float)$transportCost + (float)$mealCost + (float)$ticketCost;

        $tour->update(['total_price_usd' => $total]);
    }

    public function finance(Tour $tour)
    {
        $hotelsCost    = (float) $tour->hotels()->sum('total_price_usd');
        $transportCost = (float) $tour->transports()->sum('price_usd');
        $mealsCost     = (float) $tour->meals()->sum('total_price_usd');
        $ticketsCost   = (float) $tour->entranceTickets()->sum('total_price_usd');
        $totalCost     = $hotelsCost + $transportCost + $mealsCost + $ticketsCost;
        $revenue       = (float) $tour->total_price_usd;
        $profit        = $revenue - $totalCost;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'hotels_cost'    => $hotelsCost,
                    'transport_cost' => $transportCost,
                    'meals_cost'     => $mealsCost,
                    'tickets_cost'   => $ticketsCost,
                    'total_cost'     => $totalCost,
                    'revenue'        => $revenue,
                    'profit'         => $profit,
                    'profit_margin'  => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0,
                ],
                'hotels' => $tour->hotels()->with('hotel')->get()->map(fn($h) => [
                    'hotel_name'         => $h->hotel?->name ?? '—',
                    'room_type'          => $h->room_type,
                    'room_count'         => $h->room_count,
                    'nights_count'       => $h->nights_count,
                    'price_per_night_usd' => (float)$h->price_per_night_usd,
                    'total_price_usd'    => (float)$h->total_price_usd,
                ]),
                'transports' => $tour->transports()->get()->map(fn($t) => [
                    'route'          => ($t->route_from ?? '') . ' → ' . ($t->route_to ?? ''),
                    'transport_date' => $t->transport_date,
                    'total_price_usd' => (float)$t->price_usd,
                ]),
                'meals' => $tour->meals()->get()->map(fn($m) => [
                    'meal_date'           => $m->meal_date,
                    'meal_type'           => $m->meal_type,
                    'pax_count'           => $m->pax_count,
                    'price_per_person_usd' => (float)$m->price_per_person_usd,
                    'total_price_usd'     => (float)$m->total_price_usd,
                ]),
                'tickets' => $tour->entranceTickets()->get()->map(fn($t) => [
                    'attraction_name'      => $t->attraction_name,
                    'visit_date'           => $t->visit_date,
                    'pax_count'            => $t->pax_count,
                    'price_per_person_usd' => (float)$t->price_per_person_usd,
                    'total_price_usd'      => (float)$t->total_price_usd,
                ]),
            ],
        ]);
    }

    private function generateTourCode(): string
    {
        $year = date('Y');
        $lastTour = Tour::where('tour_code', 'like', "SGD-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastTour) {
            $parts = explode('-', $lastTour->tour_code);
            $num = (int) end($parts) + 1;
        } else {
            $num = 1;
        }

        return sprintf('SGD-%s-%03d', $year, $num);
    }
}
