<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    /**
     * Show the first-time admin setup form.
     */
    public function index(): Response
    {
        return Inertia::render('Setup');
    }

    /**
     * Create the first admin user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'master_pin' => 'required|string|digits:6',
            'store_pin' => 'required|string|digits:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'master_pin' => $validated['master_pin'],
            'store_pin' => $validated['store_pin'],
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('admin.dashboard')->with('status', 'Setup berhasil diselesaikan!');
    }
}