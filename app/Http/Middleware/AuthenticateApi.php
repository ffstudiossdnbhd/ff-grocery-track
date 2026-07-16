<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token tidak disediakan.'], 401);
        }
        
        $user = User::where('api_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Token tidak sah atau sesi telah tamat.'], 401);
        }
        
        Auth::login($user);
        return $next($request);
    }
}
