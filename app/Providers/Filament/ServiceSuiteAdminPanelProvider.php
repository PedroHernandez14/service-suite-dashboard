<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ServiceSuiteAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandName('Service Suite')
            ->id('service-suite-admin')
            ->path('service-suite-admin')
            ->login()
            ->font('Oufit')
            ->colors([
                'primary' => Color::Sky,
                'gray' => Color::Slate, // Cambia el tono de los grises (Slate, Gray, Zinc, Neutral, Stone)
                'danger' => Color::Red,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'info' => Color::Cyan,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                // Botón: Cambiar a Español
                MenuItem::make()
                    ->label('Español')
                    ->url(fn () => route('switch-language', 'es'))
                    ->icon('heroicon-o-language') // Icono visual
                    ->sort(1) // Ordenarlo arriba
                    // Truco visual: Ocultarlo si YA estás en español
                    ->hidden(fn() => app()->getLocale() === 'es'),

                // Botón: Cambiar a Inglés
                MenuItem::make()
                    ->label('English')
                    ->url(fn () => route('switch-language', 'en'))
                    ->icon('heroicon-o-language')
                    ->sort(1)
                    // Truco visual: Ocultarlo si YA estás en inglés
                    ->hidden(fn() => app()->getLocale() === 'en'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
