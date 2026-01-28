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
                'two_factor_enabled' => false, // Se debe habilitar después del primer login
            ]
        );

        // Asignar rol si no lo tiene
        if (!$superAdmin->roles->contains($superAdminRole->id)) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        $this->command->info('Super Admin creado/actualizado:');
        $this->command->info('  Email: admin@services.local');
        $this->command->info('  Password: Admin123!');
        $this->command->warn('  ⚠️  IMPORTANTE: Cambiar la contraseña después del primer login y habilitar 2FA.');
    }
}
