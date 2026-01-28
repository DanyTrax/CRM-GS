<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Crear el usuario Super Admin inicial
     */
    public function run(): void
    {
        // Buscar el rol Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            $this->command->error('El rol super-admin no existe. Ejecuta primero RoleSeeder.');
            return;
        }

        // Crear o actualizar el Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@services.local'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('Admin123!'), // CAMBIAR EN PRODUCCIÓN
                'email_verified_at' => now(),
                'role_id' => $superAdminRole->id,
                // two_factor_secret y two_factor_recovery_codes se dejan null por defecto
            ]
        );

        $this->command->info('Super Admin creado/actualizado:');
        $this->command->info('  Email: admin@services.local');
        $this->command->info('  Password: Admin123!');
        $this->command->warn('  ⚠️  IMPORTANTE: Cambiar la contraseña después del primer login y habilitar 2FA.');
    }
}
