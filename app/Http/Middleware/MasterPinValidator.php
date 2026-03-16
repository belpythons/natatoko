<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class MasterPinValidator
{
    /**
     * Handle an incoming request for Critical CRUD actions.
     * Validates the submitted PIN against the authenticated user's hashed pin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'master_pin' => 'required|string|digits:6',
        ], [
            'master_pin.required' => 'PIN Admin diperlukan untuk tindakan ini.',
            'master_pin.digits' => 'PIN Admin harus 6 angka.',
        ]);

        $admin = $request->user();

        if (!$admin || !Hash::check($request->master_pin, $admin->pin)) {
            return back()->withErrors([
                'master_pin' => 'PIN Admin yang dimasukkan salah.',
            ]);
        }

        // Clean up the request so controllers don't try to save the raw pin
        $request->request->remove('master_pin');

        return $next($request);
    }
}