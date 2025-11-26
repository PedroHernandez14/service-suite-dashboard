<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/switch-language/{locale}', function ($locale) {
    // Validación de seguridad: solo permitir 'en' o 'es'
    if (! in_array($locale, ['en', 'es'])) {
        abort(400);
    }

    // Guardar en la sesión del usuario
    Session::put('locale', $locale);

    // Redirigir atrás (recargar la página actual ya traducida)
    return redirect()->back();
})->name('switch-language');
