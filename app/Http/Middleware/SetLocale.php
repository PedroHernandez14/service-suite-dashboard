<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
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
        $locale = session()->get('locale', config('app.locale'));

        // 2. Aplica el idioma a la Aplicación (Laravel)
        App::setLocale($locale);

        // 3. Aplica el idioma a Carbon (Fechas)
        // En Laravel 12/Filament 4 esto es crucial para los DateTimePickers
        Carbon::setLocale($locale);

        return $next($request);
    }
}
