<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
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
        // Check if user is authenticated and has language preference
        if (Auth::check()) {
            $userLanguage = Auth::user()->getSettingsOrCreate()->language ?? 'ar';
            App::setLocale($userLanguage);
        } else {
            // Check for language in session or request
            $language = session('locale', $request->get('lang', config('app.locale')));
            if (in_array($language, ['en', 'ar'])) {
                App::setLocale($language);
                session(['locale' => $language]);
            }
        }

        return $next($request);
    }
}
