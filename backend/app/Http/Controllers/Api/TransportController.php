<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use App\Models\TourTransport;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transport::with('counterparty');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->is_own !== null) {
            $query->where('is_own', $request->boolean('is_own'));
        }

        $transports = $query->orderBy('type')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $transports->items(),
            'meta' => ['total' => $transports->total(), 'per_page' => $transports->perPage(), 'current_page' => $transports->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bus,minibus,car,train,internal_flight,transfer',
        ]);

        $transport = Transport::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $transport,
            'message' => 'Transport qo\'shildi.',
        ], 201);
    }

    public function show(Transport $transport)
    {
        return response()->json([
            'success' => true,
            'data' => $transport->load(['counterparty', 'tourTransports.tour']),
        ]);
    }

    public function update(Request $request, Transport $transport)
    {
        $transport->update($request->all());
        return response()->json(['success' => true, 'data' => $transport->fresh(), 'message' => 'Transport yangilandi.']);
    }

    public function destroy(Transport $transport)
    {
        $transport->delete();
        return response()->json(['success' => true, 'message' => 'Transport o\'chirildi.']);
    }
}
