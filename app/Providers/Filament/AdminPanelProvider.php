<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->homeUrl(fn (): string => '/admin')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\Settings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName(function () {
                try {
                    return \App\Models\Setting::get('company_name', 'CRM Services');
                } catch (\Exception $e) {
                    return 'CRM Services';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = \App\Models\Setting::get('company_logo_light', null);
                    return $logo ? asset('storage/' . $logo) : null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->darkModeBrandLogo(function () {
                try {
                    $logo = \App\Models\Setting::get('company_logo_dark', null);
                    return $logo ? asset('storage/' . $logo) : null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->favicon(function () {
                try {
                    $favicon = \App\Models\Setting::get('company_favicon', null);
                    if ($favicon) {
                        return asset('storage/' . $favicon);
                    }
                    // Fallback al logo claro si no hay favicon
                    $logo = \App\Models\Setting::get('company_logo_light', null);
                    return $logo ? asset('storage/' . $logo) : null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Gestión',
                'Facturación',
                'Soporte',
                'Mensajería',
                'Configuración',
            ])
            ->renderHook(
                'styles.end',
                fn () => view('filament.hooks.custom-brand-styles')
            );
    }
}
