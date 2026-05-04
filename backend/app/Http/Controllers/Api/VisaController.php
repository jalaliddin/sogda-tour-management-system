<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visa;
use Illuminate\Http\Request;

class VisaController extends Controller
{
    public function index(Request $request)
    {
        $query = Visa::with(['tour', 'processedBy']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->tour_id) {
            $query->where('tour_id', $request->tour_id);
        }
        if ($request->search) {
            $query->where('applicant_name', 'like', "%{$request->search}%")
                ->orWhere('passport_number', 'like', "%{$request->search}%");
        }

        $visas = $query->orderByDesc('created_at')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $visas->items(),
            'meta' => ['total' => $visas->total(), 'per_page' => $visas->perPage(), 'current_page' => $visas->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'applicant_name' => 'required|string',
            'visa_type' => 'required|in:tourist,business,transit,evisa,visa_on_arrival',
        ]);

        $visa = Visa::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $visa->load('tour'),
            'message' => 'Viza qo\'shildi.',
        ], 201);
    }

    public function show(Visa $visa)
    {
        return response()->json(['success' => true, 'data' => $visa->load(['tour', 'processedBy'])]);
    }

    public function update(Request $request, Visa $visa)
    {
        $visa->update($request->all());
        return response()->json(['success' => true, 'data' => $visa->fresh(), 'message' => 'Viza yangilandi.']);
    }

    public function destroy(Visa $visa)
    {
        $visa->delete();
        return response()->json(['success' => true, 'message' => 'Viza o\'chirildi.']);
    }

    public function process(Request $request, Visa $visa)
    {
        $request->validate([
            'status' => 'required|in:pending,submitted,approved,rejected,expired',
            'rejection_reason' => 'nullable|string',
        ]);

        $data = ['status' => $request->status, 'processed_by' => $request->user()->id];

        if ($request->status === 'submitted') {
            $data['submission_date'] = now()->toDateString();
        } elseif ($request->status === 'approved') {
            $data['issued_date'] = $request->issued_date ?? now()->toDateString();
        } elseif ($request->status === 'rejected') {
            $data['rejection_reason'] = $request->rejection_reason;
        }

        $visa->update($data);

        return response()->json([
            'success' => true,
            'data' => $visa->fresh(),
            'message' => 'Viza statusi yangilandi.',
        ]);
    }

    public function expiringVisas()
    {
        $visas = Visa::where('status', 'pending')
            ->whereNotNull('expected_date')
            ->whereDate('expected_date', '<=', now()->addDays(3)->toDateString())
            ->with('tour')
            ->get();

        return response()->json(['success' => true, 'data' => $visas]);
    }
}
