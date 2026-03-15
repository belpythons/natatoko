<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class MasterPinValidator
{
    /**
     * Handle an incoming request for Critical CRUD actions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Require master_pin in request payload for these sensitive routes
        $request->validate([
            'master_pin' => 'required|string|digits:6',
        ], [
            'master_pin.required' => 'Master PIN diperlukan untuk tindakan ini.',
            'master_pin.digits' => 'Master PIN harus 6 angka.',
        ]);

        $admin = $request->user();

        if (!$admin || hash('sha256', $request->master_pin) !== $admin->master_pin) {
            return back()->withErrors([
                'master_pin' => 'Master PIN yang dimasukkan salah.',
            ]);
        }

        // Clean up the request so controllers don't try to save the raw pin
        $request->request->remove('master_pin');

        return $next($request);
    }
}