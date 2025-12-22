<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->role !== User::ROLE_OWNER) {
            return response()->json(['message' => 'Owner only'], 403);
        }
        return $next($request);
    }
}
