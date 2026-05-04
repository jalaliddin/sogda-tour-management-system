<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email yoki parol noto\'g\'ri.'],
            ]);
        }

        if ($user->status === 'inactive') {
            return response()->json(['success' => false, 'message' => 'Akkaunt faol emas.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->userResource($user),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Muvaffaqiyatli chiqildi.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->userResource($request->user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'language' => 'nullable|in:uz,ru,en',
        ]);

        $request->user()->update($request->only(['name', 'phone', 'language']));

        return response()->json([
            'success' => true,
            'data' => $this->userResource($request->user()->fresh()),
            'message' => 'Profil yangilandi.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Joriy parol noto\'g\'ri.'],
            ]);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Parol muvaffaqiyatli o\'zgartirildi.']);
    }

    private function userResource(User $user): array
    {
        $user->load('branch');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'department' => $user->department,
            'position' => $user->position,
            'avatar' => $user->avatar,
            'status' => $user->status,
            'language' => $user->language,
            'branch' => $user->branch ? ['id' => $user->branch->id, 'name' => $user->branch->name, 'city' => $user->branch->city] : null,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'last_login_at' => $user->last_login_at,
        ];
    }
}
