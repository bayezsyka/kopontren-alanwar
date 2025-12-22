<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ForceLogoutKasirAtOneAM
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // owner ga kena auto logout
        if ($user->role !== User::ROLE_KASIR) {
            return $next($request);
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = now($tz);

        // batas hari ini jam 01:00
        $cutoff = $now->copy()->startOfDay()->addHour(1);

        // kalau sekarang sudah lewat cutoff, tapi login terakhir sebelum cutoff => paksa logout
        if ($now->greaterThanOrEqualTo($cutoff) && $user->last_login_at && $user->last_login_at->lt($cutoff)) {
            $token = $user->currentAccessToken();
            if ($token) $token->delete();

            return response()->json([
                'message' => 'Session kasir expired (auto logout jam 01:00). Silakan login ulang.'
            ], 401);
        }

        return $next($request);
    }
}
