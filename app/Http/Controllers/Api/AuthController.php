<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
            'device_name' => ['nullable','string'],
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json(['message' => 'Email / password salah'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $user->last_login_at = now();

        // default mode
        if ($user->role === User::ROLE_OWNER && empty($user->ui_mode)) {
            $user->ui_mode = 'owner';
        }
        if ($user->role === User::ROLE_KASIR) {
            $user->ui_mode = 'kasir';
        }

        $user->save();

        $deviceName = $data['device_name'] ?? 'pwa';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function setMode(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== User::ROLE_OWNER) {
            return response()->json(['message' => 'Owner only'], 403);
        }

        $data = $request->validate([
            'ui_mode' => ['required','in:owner,kasir'],
        ]);

        $user->ui_mode = $data['ui_mode'];
        $user->save();

        return response()->json(['user' => $this->userPayload($user)]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        if ($token) $token->delete();

        return response()->json(['message' => 'Logged out']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => (int)$user->id,
            'name' => (string)$user->name,
            'email' => (string)$user->email,
            'role' => (string)$user->role,
            'ui_mode' => (string)($user->ui_mode ?? ''),
        ];
    }
}
