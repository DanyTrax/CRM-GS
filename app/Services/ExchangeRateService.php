<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Obtener TRM del Banco de la República de Colombia
     * API pública: https://www.datos.gov.co/api/views/32sa-8pi3/rows.json
     */
    public static function getTRMFromBancoRepublica(): ?float
    {
        try {
            // Cachear por 1 hora para no hacer muchas peticiones
            return Cache::remember('trm_banco_republica', 3600, function () {
                // API del Banco de la República (datos.gov.co)
                $response = Http::timeout(10)->get('https://www.datos.gov.co/api/views/32sa-8pi3/rows.json', [
                    '$limit' => 1,
                    '$order' => 'vigenciadesde DESC',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']) && count($data['data']) > 0) {
                        $latest = $data['data'][0];
                        // El campo 10 contiene el valor de la TRM
                        if (isset($latest[10]) && is_numeric($latest[10])) {
                            return (float) $latest[10];
                        }
                    }
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error obteniendo TRM del Banco de la República: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener TRM de una API alternativa (exchangerate-api.com o similar)
     */
    public static function getTRMFromAlternativeAPI(): ?float
    {
        try {
            return Cache::remember('trm_alternative', 3600, function () {
                // API alternativa: exchangerate-api.com (USD a COP)
                $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['rates']['COP'])) {
                        return (float) $data['rates']['COP'];
                    }
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error obteniendo TRM de API alternativa: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener TRM automática (intenta múltiples fuentes)
     */
    public static function getAutomaticTRM(): ?float
    {
        // Intentar primero Banco de la República
        $trm = self::getTRMFromBancoRepublica();
        
        if ($trm) {
            return $trm;
        }

        // Si falla, intentar API alternativa
        $trm = self::getTRMFromAlternativeAPI();
        
        if ($trm) {
            return $trm;
        }

        return null;
    }

    /**
     * Obtener TRM para usar (automática o manual según configuración)
     */
    public static function getTRM(): float
    {
        $autoTRMEnabled = \App\Models\Setting::get('trm_auto_enabled', false);
        
        if ($autoTRMEnabled) {
            $trm = self::getAutomaticTRM();
            
            if ($trm) {
                // Guardar la TRM obtenida automáticamente
                \App\Models\Setting::set('trm_base', $trm, 'decimal', 'TRM obtenida automáticamente');
                return $trm;
            }
        }

        // Si no está habilitada o falla, usar la manual
        return (float) \App\Models\Setting::get('trm_base', 4000);
    }
}
