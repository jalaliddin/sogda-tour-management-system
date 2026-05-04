<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $branches]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|in:toshkent,samarkand,bukhara,khiva',
        ]);

        $branch = Branch::create($request->all());
        return response()->json(['success' => true, 'data' => $branch, 'message' => 'Filial qo\'shildi.'], 201);
    }

    public function show(Branch $branch)
    {
        return response()->json(['success' => true, 'data' => $branch->load('users')]);
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($request->all());
        return response()->json(['success' => true, 'data' => $branch->fresh(), 'message' => 'Filial yangilandi.']);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return response()->json(['success' => true, 'message' => 'Filial o\'chirildi.']);
    }
}
