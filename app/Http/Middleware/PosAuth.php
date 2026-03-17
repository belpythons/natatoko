<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PosAuth
{
    /**
     * Handle an incoming request for POS routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('pos_authenticated')) {
            return redirect()->route('login')->withErrors([
                'pin' => 'Silakan masuk menggunakan PIN untuk akses POS.'
            ]);
        }

        return $next($request);
    }
}