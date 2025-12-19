<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class CheckUserBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_blocked) {
            Auth::guard('web')->logout(); // Log out the blocked user

            $request->session()->invalidate(); // Invalidate the session

            $request->session()->regenerateToken(); // Regenerate CSRF token

            return redirect('/')->with('status', 'Jūsu konts ir bloķēts.'); // Redirect with message 
        }

        return $next($request);
    }
}
