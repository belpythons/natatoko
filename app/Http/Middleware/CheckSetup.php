<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSetup
{
    /**
     * Allow access to /setup only if no admin user exists yet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\App\Models\User::count() === 0) {
            if (!$request->is('setup')) {
                return redirect()->route('setup');
            }
        }
        elseif ($request->is('setup')) {
            return redirect()->route('pos.login');
        }

        return $next($request);
    }
}