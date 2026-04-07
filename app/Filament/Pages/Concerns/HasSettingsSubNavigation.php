<?php

namespace App\Filament\Pages\Concerns;

use Filament\Navigation\NavigationItem;

trait HasSettingsSubNavigation
{
    public function getSubNavigation(): array
    {
        return [
            NavigationItem::make('Empresa')
                ->url(\App\Filament\Pages\SettingsBillingContact::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.facturacion-contacto'))
                ->sort(1),

            NavigationItem::make('Configuración de Moneda')
                ->url(\App\Filament\Pages\SettingsCurrency::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.moneda'))
                ->sort(2),

            NavigationItem::make('Conexión Google Drive')
                ->url(\App\Filament\Pages\SettingsBackups::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.backups'))
                ->sort(3),

            NavigationItem::make('Correo & SMTP')
                ->url(route('filament.admin.resources.email-configurations.index'))
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.email-configurations.*'))
                ->sort(4),

            NavigationItem::make('Plantillas de Email')
                ->url(route('filament.admin.resources.email-templates.index'))
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.email-templates.*'))
                ->sort(5),

            NavigationItem::make('Marca y Logos')
                ->url(\App\Filament\Pages\SettingsBranding::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.marca'))
                ->sort(6),

            NavigationItem::make('Seguridad')
                ->url(\App\Filament\Pages\SettingsSecurity::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.seguridad'))
                ->sort(7),

            NavigationItem::make('Sistema')
                ->url(\App\Filament\Pages\SystemUpdate::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.settings.actualizacion'))
                ->sort(8),
        ];
    }
}
