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
        if (User::where('role', 'admin')->exists()) {
            return redirect('/login');
        }

        return $next($request);
    }
}