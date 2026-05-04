<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $query = Hotel::with(['counterparty', 'branch']);

        if ($request->city) {
            $query->where('city', $request->city);
        }
        if ($request->is_own !== null) {
            $query->where('is_own', $request->boolean('is_own'));
        }
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $hotels = $query->orderBy('name')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $hotels->items(),
            'meta' => ['total' => $hotels->total(), 'per_page' => $hotels->perPage(), 'current_page' => $hotels->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string',
        ]);

        $hotel = Hotel::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $hotel->load(['counterparty', 'branch']),
            'message' => 'Mehmonxona qo\'shildi.',
        ], 201);
    }

    public function show(Hotel $hotel)
    {
        return response()->json([
            'success' => true,
            'data' => $hotel->load(['counterparty', 'branch', 'bookings.tour']),
        ]);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $hotel->update($request->all());
        return response()->json(['success' => true, 'data' => $hotel->fresh(), 'message' => 'Mehmonxona yangilandi.']);
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return response()->json(['success' => true, 'message' => 'Mehmonxona o\'chirildi.']);
    }
}
