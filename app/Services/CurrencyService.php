<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Obtener TRM (Tasa Representativa del Mercado) desde API externa
     * Con fallback a configuración local
     * 
     * @return float
     */
    public function getTRM(): float
    {
        // Intentar obtener desde caché (1 hora)
        return Cache::remember('trm_usd_cop', 3600, function () {
            // Intentar obtener desde API del Banco de la República de Colombia
            try {
                $response = Http::timeout(5)->get('https://www.datos.gov.co/api/views/32sa-8pi3/rows.json');
                
                if ($response->successful()) {
                    $data = $response->json();
                    // Procesar datos de la API (estructura puede variar)
                    // Por ahora, usar configuración local como fallback
                }
            } catch (\Exception $e) {
                \Log::warning('Error obteniendo TRM desde API: ' . $e->getMessage());
            }

            // Fallback a configuración local
            return (float) Setting::get('trm_base', 4000);
        });
    }

    /**
     * Calcular TRM + Spread para conversión Bold
     * 
     * @param float|null $customSpread Spread personalizado (opcional)
     * @return float
     */
    public function getExchangeRate(?float $customSpread = null): float
    {
        $trm = $this->getTRM();
        $spread = $customSpread ?? (float) Setting::get('bold_spread_percentage', 3);

        return $trm * (1 + ($spread / 100));
    }

    /**
     * Convertir USD a COP usando TRM + Spread
     * 
     * @param float $usdAmount Monto en USD
     * @param float|null $customSpread Spread personalizado (opcional)
     * @return float Monto en COP
     */
    public function convertUsdToCop(float $usdAmount, ?float $customSpread = null): float
    {
        $exchangeRate = $this->getExchangeRate($customSpread);
        return round($usdAmount * $exchangeRate, 2);
    }

    /**
     * Convertir COP a USD usando TRM + Spread
     * 
     * @param float $copAmount Monto en COP
     * @param float|null $customSpread Spread personalizado (opcional)
     * @return float Monto en USD
     */
    public function convertCopToUsd(float $copAmount, ?float $customSpread = null): float
    {
        $exchangeRate = $this->getExchangeRate($customSpread);
        return round($copAmount / $exchangeRate, 2);
    }

    /**
     * Formatear monto según moneda
     * 
     * @param float $amount
     * @param string $currency USD o COP
     * @return string
     */
    public function formatCurrency(float $amount, string $currency = 'COP'): string
    {
        $symbol = $currency === 'USD' ? '$' : '$';
        $decimals = 2;
        
        if ($currency === 'COP') {
            return $symbol . number_format($amount, $decimals, ',', '.');
        }
        
        return $symbol . number_format($amount, $decimals, '.', ',');
    }
}
