<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed los roles del sistema
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Administrador principal con acceso completo al sistema, incluyendo configuración y 2FA obligatorio.',
                'is_active' => true,
            ],
            [
                'name' => 'Admin Operativo',
                'slug' => 'admin-operativo',
                'description' => 'Administrador operativo con acceso a gestión de clientes, servicios, facturas y pagos.',
                'is_active' => true,
            ],
            [
                'name' => 'Contador',
                'slug' => 'contador',
                'description' => 'Acceso a facturas, pagos y reportes financieros.',
                'is_active' => true,
            ],
            [
                'name' => 'Soporte',
                'slug' => 'soporte',
                'description' => 'Acceso a tickets y comunicación con clientes.',
                'is_active' => true,
            ],
            [
                'name' => 'Cliente',
                'slug' => 'cliente',
                'description' => 'Acceso al área de cliente (dashboard, facturas, pagos, tickets).',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('Roles creados/actualizados exitosamente.');
    }
}
