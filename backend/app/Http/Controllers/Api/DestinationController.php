<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $query = Destination::query();

        if ($request->country) {
            $query->where('country', $request->country);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('country', 'like', "%{$request->search}%")
                    ->orWhere('region', 'like', "%{$request->search}%");
            });
        }
        if ($request->active_only) {
            $query->where('is_active', true);
        }

        if ($request->all_list) {
            return response()->json([
                'success' => true,
                'data' => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            ]);
        }

        $items = $query->orderBy('sort_order')->orderBy('name')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => ['total' => $items->total(), 'per_page' => $items->perPage(), 'current_page' => $items->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
        ]);

        $destination = Destination::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $destination,
            'message' => 'Destination added successfully.',
        ], 201);
    }

    public function show(Destination $destination)
    {
        return response()->json(['success' => true, 'data' => $destination]);
    }

    public function update(Request $request, Destination $destination)
    {
        $destination->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $destination->fresh(),
            'message' => 'Destination updated.',
        ]);
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();
        return response()->json(['success' => true, 'message' => 'Destination deleted.']);
    }

    public function countries()
    {
        $countries = Destination::distinct()
            ->orderBy('country')
            ->pluck('country');

        return response()->json(['success' => true, 'data' => $countries]);
    }
}
