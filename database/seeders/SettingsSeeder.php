<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'string', 'description' => 'Prefijo para numeración de facturas'],
            ['key' => 'invoice_start_number', 'value' => '1', 'type' => 'integer', 'description' => 'Número inicial de facturas'],
            ['key' => 'default_currency', 'value' => 'COP', 'type' => 'string', 'description' => 'Moneda por defecto'],
            ['key' => 'trm_spread', 'value' => '3', 'type' => 'integer', 'description' => 'Spread porcentual para conversión USD a COP'],
            ['key' => 'dian_resolution', 'value' => '', 'type' => 'string', 'description' => 'Resolución DIAN'],
            ['key' => 'company_nit', 'value' => '', 'type' => 'string', 'description' => 'NIT de la empresa'],
            ['key' => 'company_name', 'value' => '', 'type' => 'string', 'description' => 'Razón Social'],
            ['key' => 'backup_retention_days', 'value' => '30', 'type' => 'integer', 'description' => 'Días de retención de backups'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
