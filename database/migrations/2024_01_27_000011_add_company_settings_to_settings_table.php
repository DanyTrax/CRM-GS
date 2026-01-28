<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar configuraciones de empresa si no existen
        $companySettings = [
            [
                'key' => 'company_name',
                'value' => 'DOWGROUP',
                'type' => 'string',
                'description' => 'Nombre de la empresa',
            ],
            [
                'key' => 'company_tax_id',
                'value' => '901399785',
                'type' => 'string',
                'description' => 'NIT de la empresa',
            ],
            [
                'key' => 'company_email',
                'value' => 'tecnologia@dowgroupcol.com',
                'type' => 'string',
                'description' => 'Email de contacto de la empresa',
            ],
            [
                'key' => 'company_phone',
                'value' => '3111111111',
                'type' => 'string',
                'description' => 'Teléfono de contacto de la empresa',
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Dirección de la empresa',
            ],
            [
                'key' => 'company_website',
                'value' => 'dowgroupcol.com',
                'type' => 'string',
                'description' => 'Sitio web de la empresa',
            ],
            [
                'key' => 'company_logo_light',
                'value' => null,
                'type' => 'string',
                'description' => 'Logo para tema claro',
            ],
            [
                'key' => 'company_logo_dark',
                'value' => null,
                'type' => 'string',
                'description' => 'Logo para tema oscuro',
            ],
            [
                'key' => 'session_timeout',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Tiempo de inactividad en minutos antes de cerrar sesión automáticamente (mínimo: 1, máximo: 120)',
            ],
        ];

        foreach ($companySettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        // Eliminar configuraciones de empresa
        $keys = [
            'company_name',
            'company_tax_id',
            'company_email',
            'company_phone',
            'company_address',
            'company_website',
            'company_logo_light',
            'company_logo_dark',
            'session_timeout',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
