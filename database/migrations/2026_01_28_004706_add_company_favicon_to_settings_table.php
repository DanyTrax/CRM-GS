<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar configuración de favicon si no existe
        Setting::updateOrCreate(
            ['key' => 'company_favicon'],
            [
                'key' => 'company_favicon',
                'value' => null,
                'type' => 'string',
                'description' => 'Icono del sistema (favicon) que aparece en la pestaña del navegador',
            ]
        );
    }

    public function down(): void
    {
        // Eliminar configuración de favicon
        Setting::where('key', 'company_favicon')->delete();
    }
};
