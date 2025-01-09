<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $user = Auth::user();
    
        // Cek apakah user memiliki salah satu dari peran yang diperbolehkan
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'error' => 'Forbidden',
                'message'=> 'Anda Tidak Memiliki Akses',
        ], 403);
        }
    
        return $next($request);
    }
}
