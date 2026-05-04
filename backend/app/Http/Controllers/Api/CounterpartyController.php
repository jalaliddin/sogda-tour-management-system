<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counterparty;
use Illuminate\Http\Request;

class CounterpartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Counterparty::query();

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->is_active !== null) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->search) {
            $query->where('company_name', 'like', "%{$request->search}%");
        }

        $counterparties = $query->orderBy('company_name')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $counterparties->items(),
            'meta' => ['total' => $counterparties->total(), 'per_page' => $counterparties->perPage(), 'current_page' => $counterparties->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'type' => 'required|in:foreign_tour,local_tour,hotel,restaurant,guide,folklore,transport',
        ]);

        $data = $request->all();
        $data['created_by'] = $request->user()->id;

        $counterparty = Counterparty::create($data);

        return response()->json([
            'success' => true,
            'data' => $counterparty,
            'message' => 'Kontragent qo\'shildi.',
        ], 201);
    }

    public function show(Counterparty $counterparty)
    {
        $counterparty->load(['tours', 'offers', 'hotels']);
        return response()->json(['success' => true, 'data' => $counterparty]);
    }

    public function update(Request $request, Counterparty $counterparty)
    {
        $counterparty->update($request->all());
        return response()->json(['success' => true, 'data' => $counterparty->fresh(), 'message' => 'Kontragent yangilandi.']);
    }

    public function destroy(Counterparty $counterparty)
    {
        $counterparty->delete();
        return response()->json(['success' => true, 'message' => 'Kontragent o\'chirildi.']);
    }

    public function getByType(Request $request, string $type)
    {
        $counterparties = Counterparty::where('type', $type)->where('is_active', true)->orderBy('company_name')->get(['id', 'company_name', 'country', 'city', 'contact_person', 'phone']);

        return response()->json(['success' => true, 'data' => $counterparties]);
    }
}
