<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * Authenticate POS PIN
     */
    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|digits:6',
        ]);

        $admin = User::first();

        // Check if the provided PIN matches the admin's hashed pin
        if ($admin && Hash::check($request->pin, $admin->pin)) {
            session(['pos_authenticated' => true]);

            return redirect()->route('pos.session.create');
        }

        return back()->withErrors([
            'pin' => 'PIN yang Anda masukkan salah.',
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