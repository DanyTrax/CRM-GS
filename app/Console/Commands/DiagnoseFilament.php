<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

class DiagnoseFilament extends Command
{
    protected $signature = 'filament:diagnose';
    protected $description = 'Diagnosticar problemas con Filament y generar logs detallados';

    public function handle()
    {
        $this->info('🔍 Diagnóstico de Filament...');
        $this->newLine();

        $log = [];
        $log[] = "=== DIAGNÓSTICO DE FILAMENT ===";
        $log[] = "Fecha: " . now()->toDateTimeString();
        $log[] = "";

        // 1. Verificar Providers
        $this->info('1. Verificando Providers...');
        $providers = $this->checkProviders();
        $log[] = "## Providers";
        $log[] = json_encode($providers, JSON_PRETTY_PRINT);
        $this->displayResults($providers);

        // 2. Verificar Resources
        $this->info('2. Verificando Resources...');
        $resources = $this->checkResources();
        $log[] = "";
        $log[] = "## Resources";
        $log[] = json_encode($resources, JSON_PRETTY_PRINT);
        $this->displayResults($resources);

        // 3. Verificar Pages
        $this->info('3. Verificando Pages...');
        $pages = $this->checkPages();
        $log[] = "";
        $log[] = "## Pages";
        $log[] = json_encode($pages, JSON_PRETTY_PRINT);
        $this->displayResults($pages);

        // 4. Verificar Rutas de Filament
        $this->info('4. Verificando Rutas de Filament...');
        $routes = $this->checkFilamentRoutes();
        $log[] = "";
        $log[] = "## Rutas de Filament";
        $log[] = json_encode($routes, JSON_PRETTY_PRINT);
        $this->displayResults($routes);

        // 5. Guardar log
        $logContent = implode("\n", $log);
        $logPath = storage_path('logs/filament-diagnosis-' . date('Y-m-d-His') . '.log');
        File::put($logPath, $logContent);
        
        $this->newLine();
        $this->info("✅ Log guardado en: {$logPath}");
        
        return 0;
    }

    protected function checkProviders()
    {
        $results = [];
        
        $providersPath = base_path('bootstrap/providers.php');
        if (File::exists($providersPath)) {
            $providers = require $providersPath;
            $results['providers.php'] = [
                'exists' => true,
                'providers' => $providers,
            ];
            
            foreach ($providers as $provider) {
                if (str_contains($provider, 'Filament')) {
                    $results['providers.php']['filament_providers'][] = $provider;
                    $results['providers.php']['filament_providers_exist'][] = class_exists($provider);
                }
            }
        } else {
            $results['providers.php'] = ['exists' => false];
        }

        return $results;
    }

    protected function checkResources()
    {
        $results = [];
        $resourcesPath = app_path('Filament/Resources');
        
        if (!File::isDirectory($resourcesPath)) {
            return ['error' => 'Directorio Resources no existe'];
        }

        $files = File::glob($resourcesPath . '/*.php');
        
        foreach ($files as $file) {
            $className = 'App\\Filament\\Resources\\' . basename($file, '.php');
            
            if (!class_exists($className)) {
                $results[basename($file)] = ['error' => 'Clase no existe'];
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $resource = [
                    'class' => $className,
                    'exists' => true,
                    'has_slug' => $reflection->hasProperty('slug'),
                    'has_canViewAny' => $reflection->hasMethod('canViewAny'),
                    'has_shouldRegisterNavigation' => $reflection->hasMethod('shouldRegisterNavigation'),
                    'has_getPages' => $reflection->hasMethod('getPages'),
                ];

                // Verificar slug
                if ($reflection->hasProperty('slug')) {
                    $slugProp = $reflection->getProperty('slug');
                    $slugProp->setAccessible(true);
                    $resource['slug_value'] = $slugProp->getValue();
                }

                // Verificar métodos
                if ($reflection->hasMethod('canViewAny')) {
                    $method = $reflection->getMethod('canViewAny');
                    $method->setAccessible(true);
                    try {
                        $resource['canViewAny_result'] = $method->invoke(null);
                    } catch (\Exception $e) {
                        $resource['canViewAny_error'] = $e->getMessage();
                    }
                }

                if ($reflection->hasMethod('shouldRegisterNavigation')) {
                    $method = $reflection->getMethod('shouldRegisterNavigation');
                    $method->setAccessible(true);
                    try {
                        $resource['shouldRegisterNavigation_result'] = $method->invoke(null);
                    } catch (\Exception $e) {
                        $resource['shouldRegisterNavigation_error'] = $e->getMessage();
                    }
                }

                // Verificar páginas
                if ($reflection->hasMethod('getPages')) {
                    $method = $reflection->getMethod('getPages');
                    $method->setAccessible(true);
                    try {
                        $pages = $method->invoke(null);
                        $resource['pages'] = $pages;
                    } catch (\Exception $e) {
                        $resource['getPages_error'] = $e->getMessage();
                    }
                }

                $results[basename($file)] = $resource;
            } catch (\Exception $e) {
                $results[basename($file)] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    protected function checkPages()
    {
        $results = [];
        $pagesPath = app_path('Filament/Pages');
        
        if (!File::isDirectory($pagesPath)) {
            return ['error' => 'Directorio Pages no existe'];
        }

        $files = File::glob($pagesPath . '/*.php');
        
        foreach ($files as $file) {
            $className = 'App\\Filament\\Pages\\' . basename($file, '.php');
            
            if (!class_exists($className)) {
                $results[basename($file)] = ['error' => 'Clase no existe'];
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $page = [
                    'class' => $className,
                    'exists' => true,
                    'has_slug' => $reflection->hasProperty('slug'),
                    'has_navigationGroup' => $reflection->hasProperty('navigationGroup'),
                ];

                if ($reflection->hasProperty('slug')) {
                    $slugProp = $reflection->getProperty('slug');
                    $slugProp->setAccessible(true);
                    $page['slug_value'] = $slugProp->getValue();
                }

                $results[basename($file)] = $page;
            } catch (\Exception $e) {
                $results[basename($file)] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    protected function checkFilamentRoutes()
    {
        $results = [];
        
        $allRoutes = Route::getRoutes();
        $filamentRoutes = [];
        
        foreach ($allRoutes as $route) {
            $name = $route->getName();
            if ($name && str_starts_with($name, 'filament.admin')) {
                $filamentRoutes[] = [
                    'name' => $name,
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                ];
            }
        }

        $results['total_routes'] = count($filamentRoutes);
        $results['routes'] = $filamentRoutes;

        // Verificar rutas específicas esperadas
        $expectedRoutes = [
            'filament.admin.pages.dashboard',
            'filament.admin.pages.settings',
            'filament.admin.resources.clients.index',
            'filament.admin.resources.tickets.index',
            'filament.admin.resources.services.index',
            'filament.admin.resources.invoices.index',
        ];

        $results['expected_routes'] = [];
        foreach ($expectedRoutes as $expectedRoute) {
            $exists = Route::has($expectedRoute);
            $results['expected_routes'][$expectedRoute] = $exists;
            
            if (!$exists) {
                $this->warn("  ❌ Ruta faltante: {$expectedRoute}");
            } else {
                $this->info("  ✅ Ruta existe: {$expectedRoute}");
            }
        }

        return $results;
    }

    protected function displayResults($results)
    {
        if (isset($results['error'])) {
            $this->error("  ❌ Error: " . $results['error']);
            return;
        }

        foreach ($results as $key => $value) {
            if (is_array($value)) {
                if (isset($value['exists']) && $value['exists']) {
                    $this->info("  ✅ {$key}");
                } elseif (isset($value['error'])) {
                    $this->error("  ❌ {$key}: " . $value['error']);
                } else {
                    $this->line("  ℹ️  {$key}");
                }
            }
        }
    }
}
