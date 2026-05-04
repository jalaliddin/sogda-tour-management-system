<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'branch']);

        if ($request->role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => ['total' => $users->total(), 'per_page' => $users->perPage(), 'current_page' => $users->currentPage()],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|exists:roles,name',
        ]);

        $userData = $request->except('role');
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load(['roles', 'branch']),
            'message' => 'Foydalanuvchi qo\'shildi.',
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load(['roles', 'branch', 'tours']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $data = $request->except(['password', 'role']);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'success' => true,
            'data' => $user->fresh()->load(['roles', 'branch']),
            'message' => 'Foydalanuvchi yangilandi.',
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'O\'zingizni o\'chirib bo\'lmaydi.'], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Foydalanuvchi o\'chirildi.']);
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);
        $user->syncRoles([$request->role]);

        return response()->json([
            'success' => true,
            'data' => $user->fresh()->load('roles'),
            'message' => 'Rol tayinlandi.',
        ]);
    }

    public function roles()
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return response()->json(['success' => true, 'data' => $roles]);
    }
}
