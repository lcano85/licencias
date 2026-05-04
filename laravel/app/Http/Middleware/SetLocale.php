<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: session -> cookie -> app default (session can fail on redirect/load balancer)
        $locale = Session::get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (in_array($locale, ['en', 'es'])) {
            App::setLocale($locale);
            // Keep session in sync when we read from cookie (e.g. after session was lost)
            if (!Session::has('locale')) {
                Session::put('locale', $locale);
            }
        }

        return $next($request);
    }
}