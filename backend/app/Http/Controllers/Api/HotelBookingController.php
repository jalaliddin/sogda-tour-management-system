<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourHotel;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TourHotel::with(['tour', 'hotel', 'destination', 'confirmedBy']);

        if ($user->hasRole('hotel_khiva')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'khiva'));
        } elseif ($user->hasRole('hotel_samarkand')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'samarkand'));
        } elseif ($user->hasRole('hotel_bukhara')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'bukhara'));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }
        if ($request->date_from) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        $bookings = $query->orderByDesc('created_at')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => ['total' => $bookings->total(), 'per_page' => $bookings->perPage(), 'current_page' => $bookings->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'hotel_id' => 'required|exists:hotels,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_count' => 'required|integer|min:1',
        ]);

        $booking = TourHotel::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $booking->load(['tour', 'hotel']),
            'message' => 'Bron yaratildi.',
        ], 201);
    }

    public function show(TourHotel $hotelBooking)
    {
        return response()->json([
            'success' => true,
            'data' => $hotelBooking->load(['tour', 'hotel', 'destination', 'confirmedBy']),
        ]);
    }

    public function update(Request $request, TourHotel $hotelBooking)
    {
        $hotelBooking->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $hotelBooking->fresh()->load(['tour', 'hotel']),
            'message' => 'Bron yangilandi.',
        ]);
    }

    public function destroy(TourHotel $hotelBooking)
    {
        $hotelBooking->delete();
        return response()->json(['success' => true, 'message' => 'Bron o\'chirildi.']);
    }

    public function updateStatus(Request $request, TourHotel $hotelBooking)
    {
        $request->validate([
            'status' => 'required|in:pending,waiting_list,ok,confirmed,cancelled',
            'hotel_confirmation_number' => 'nullable|string',
        ]);

        $hotelBooking->update([
            'status' => $request->status,
            'hotel_confirmation_number' => $request->hotel_confirmation_number,
            'confirmed_by' => in_array($request->status, ['confirmed', 'ok']) ? $request->user()->id : $hotelBooking->confirmed_by,
            'confirmed_at' => in_array($request->status, ['confirmed', 'ok']) ? now() : $hotelBooking->confirmed_at,
        ]);

        return response()->json([
            'success' => true,
            'data' => $hotelBooking->fresh(),
            'message' => 'Status yangilandi.',
        ]);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = TourHotel::query();

        if ($user->hasRole('hotel_khiva')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'khiva'));
        } elseif ($user->hasRole('hotel_samarkand')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'samarkand'));
        } elseif ($user->hasRole('hotel_bukhara')) {
            $query->whereHas('hotel', fn($q) => $q->where('city', 'bukhara'));
        }

        $stats = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'waiting_list' => (clone $query)->where('status', 'waiting_list')->count(),
            'ok' => (clone $query)->where('status', 'ok')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'this_month' => (clone $query)->whereMonth('check_in_date', now()->month)->count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
