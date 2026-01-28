<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Seed las configuraciones iniciales del sistema
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'trm_base',
                'value' => '4000',
                'type' => 'integer',
                'description' => 'TRM base para conversión USD a COP',
            ],
            [
                'key' => 'bold_spread_percentage',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Spread porcentual para conversión Bold (TRM + Spread)',
            ],
            [
                'key' => 'bold_webhook_secret',
                'value' => '',
                'type' => 'string',
                'description' => 'Secret key para validar webhooks de Bold',
            ],
            [
                'key' => 'backup_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Habilitar backups automáticos a Google Drive',
            ],
            [
                'key' => 'google_drive_folder_id',
                'value' => '',
                'type' => 'string',
                'description' => 'ID de la carpeta en Google Drive para backups',
            ],
            [
                'key' => 'last_backup_date',
                'value' => null,
                'type' => 'string',
                'description' => 'Fecha del último backup realizado',
            ],
            [
                'key' => 'last_schedule_run',
                'value' => null,
                'type' => 'string',
                'description' => 'Última ejecución del scheduler (para Health Check)',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }

        $this->command->info('Configuraciones iniciales creadas.');
    }
}
