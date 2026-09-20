<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = config('app.available_locales', ['en', 'ja']);

        // Pick one strategy: by default the app renders in config('app.locale').
        // Uncomment the source(s) below that fit your app; they are tried in
        // order. The language switcher route stores the choice in the session,
        // so enable #2 to make it take effect.

        // // 1. User preference from the database (e.g. a users.locale column)
        // if ($request->user()?->locale && in_array($request->user()->locale, $supported)) {
        //     return $request->user()->locale;
        // }

        // // 2. Session (set by the language-switcher route)
        // $sessionLocale = session('locale');
        // if ($sessionLocale && in_array($sessionLocale, $supported)) {
        //     return $sessionLocale;
        // }

        // // 3. Browser Accept-Language header (automatic detection)
        // $preferred = $request->getPreferredLanguage($supported);
        // if ($preferred) {
        //     return $preferred;
        // }

        // Default: the configured app locale
        $locale = config('app.locale', 'en');

        return in_array($locale, $supported) ? $locale : ($supported[0] ?? 'en');
    }
}