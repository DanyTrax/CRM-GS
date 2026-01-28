<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar que el caché use archivos si la tabla cache no existe
        // Esto evita el error de tabla cache inexistente con Livewire
        try {
            if (config('cache.default') === 'database') {
                // Si está configurado como database pero la tabla no existe, cambiar a file
                if (!\Illuminate\Support\Facades\Schema::hasTable('cache')) {
                    config(['cache.default' => 'file']);
                }
            }
        } catch (\Exception $e) {
            // Si hay error al verificar, forzar file
            config(['cache.default' => 'file']);
        }
        
        // Configurar rate limiting de Livewire para usar archivos en lugar de BD
        // Esto evita el error de tabla cache inexistente
        RateLimiter::for('livewire', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
