<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Tour;
use App\Services\OfferPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with(['counterparty', 'reviewedBy']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->counterparty_id) {
            $query->where('counterparty_id', $request->counterparty_id);
        }
        if ($request->offer_type) {
            $query->where('offer_type', $request->offer_type);
        }
        if ($request->search) {
            $query->where('offer_name', 'like', "%{$request->search}%");
        }

        $offers = $query->orderByDesc('created_at')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $offers->items(),
            'meta' => ['total' => $offers->total(), 'per_page' => $offers->perPage(), 'current_page' => $offers->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'offer_name' => 'required|string|max:255',
            'offer_type' => 'required|in:inbound,outbound,package,custom',
        ]);

        $data = $request->all();
        $data['received_date'] = $data['received_date'] ?? now()->toDateString();

        $offer = Offer::create($data);

        return response()->json([
            'success' => true,
            'data' => $offer->load('counterparty'),
            'message' => 'Offer qo\'shildi.',
        ], 201);
    }

    public function show(Offer $offer)
    {
        return response()->json([
            'success' => true,
            'data' => $offer->load(['counterparty', 'reviewedBy']),
        ]);
    }

    public function update(Request $request, Offer $offer)
    {
        $offer->update($request->all());
        return response()->json(['success' => true, 'data' => $offer->fresh(), 'message' => 'Offer yangilandi.']);
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return response()->json(['success' => true, 'message' => 'Offer o\'chirildi.']);
    }

    public function generatePdf(Request $request, Offer $offer)
    {
        $lang = in_array($request->query('lang'), ['ru', 'en']) ? $request->query('lang') : 'ru';
        $service = new OfferPdfService;
        $pdf = $service->generate($offer->id, $lang);
        return $pdf->download("offer-{$offer->id}-{$lang}.pdf");
    }

    public function accept(Request $request, Offer $offer)
    {
        $offer->update([
            'status' => 'accepted',
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $offer->fresh(),
            'message' => 'Offer qabul qilindi.',
        ]);
    }

    public function reject(Request $request, Offer $offer)
    {
        $request->validate(['reason' => 'nullable|string']);

        $offer->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'notes' => $request->reason ? ($offer->notes . "\n" . 'Rad etish sababi: ' . $request->reason) : $offer->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $offer->fresh(),
            'message' => 'Offer rad etildi.',
        ]);
    }

    public function convertToTour(Request $request, Offer $offer)
    {
        DB::beginTransaction();
        try {
            $year = date('Y');
            $lastTour = Tour::where('tour_code', 'like', "SGD-{$year}-%")->orderByDesc('id')->first();
            $num = $lastTour ? (int)explode('-', $lastTour->tour_code)[2] + 1 : 1;
            $tourCode = sprintf("SGD-%s-%03d", $year, $num);

            $countries = is_array($offer->destination_countries) ? implode(', ', $offer->destination_countries) : $offer->destination_countries;

            $tour = Tour::create([
                'tour_name' => $offer->offer_name,
                'tour_code' => $tourCode,
                'country' => $countries,
                'counterparty_id' => $offer->counterparty_id,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'pax_count' => $offer->pax_min,
                'pax_adults' => $offer->pax_min,
                'status' => 'draft',
                'total_price_usd' => $offer->total_price_usd,
                'currency' => $offer->currency,
                'notes' => "Offer #{$offer->id} dan yaratildi: {$offer->offer_name}",
                'created_by' => $request->user()->id,
            ]);

            $offer->update(['status' => 'accepted', 'reviewed_by' => $request->user()->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => ['tour' => $tour, 'offer' => $offer],
                'message' => 'Offerdan tur yaratildi.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
