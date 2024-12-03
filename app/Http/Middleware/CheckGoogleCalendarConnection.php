<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckGoogleCalendarConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user->google_token || !$user->google_refresh_token) {
            return redirect()->route('google.auth')->with('warning', 'You need to connect your Google Calendar.');
        }

        return $next($request);
    }
}
