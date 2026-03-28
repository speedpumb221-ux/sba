<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Prefer an authenticated user's preference when available
        $user = Auth::user();
        if ($user && isset($user->language) && $user->language) {
            $locale = $user->language;
        } else {
            $locale = session('locale', $request->get('lang', config('app.locale')));
        }

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
