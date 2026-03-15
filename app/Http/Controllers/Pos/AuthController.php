<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show Numpad Login Screen
     */
    public function showLogin()
    {
        if (session('pos_authenticated')) {
            return redirect()->route('pos.session.create');
        }

        return Inertia::render('Pos/Auth/Login');
    }

    /**
     * Authenticate Store PIN
     */
    public function login(Request $request)
    {
        $request->validate([
            'store_pin' => 'required|string|digits:6',
        ]);

        $admin = User::first();

        // Check if the provided PIN matches the admin's hashed store_pin
        if ($admin && hash('sha256', $request->store_pin) === $admin->store_pin) {
            session(['pos_authenticated' => true]);

            return redirect()->route('pos.session.create');
        }

        return back()->withErrors([
            'store_pin' => 'PIN yang Anda masukkan salah.',
        ]);
    }

    /**
     * Logout POS Session
     */
    public function logout(Request $request)
    {
        $request->session()->forget('pos_authenticated');

        return redirect()->route('pos.login');
    }
}