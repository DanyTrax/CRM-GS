<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Clientes
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_sensitive_data',
            
            // Servicios
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            'services.renew',
            
            // Facturas
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.download_pdf',
            
            // Pagos
            'payments.view',
            'payments.approve',
            'payments.reject',
            
            // Tickets
            'tickets.view',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.reply',
            
            // Usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
            
            // Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Configuración
            'settings.view',
            'settings.edit',
            'settings.backup',
            'settings.api_keys',
            
            // Reportes
            'reports.view',
            'reports.financial',
            
            // Comunicaciones
            'communications.send',
            'communications.view_logs',
        ];

        // Crear permisos si no existen (idempotente)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 1. Super Administrador - Acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Administrador Operativo
        $admin = Role::firstOrCreate(['name' => 'Administrador Operativo']);
        $admin->syncPermissions([
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_sensitive_data',
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            'services.renew',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.download_pdf',
            'payments.view',
            'payments.approve',
            'payments.reject',
            'tickets.view',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.reply',
            'users.view',
            'users.create',
            'users.edit',
            'reports.view',
            'communications.send',
            'communications.view_logs',
        ]);

        // 3. Contador - Solo lectura financiera
        $accountant = Role::firstOrCreate(['name' => 'Contador']);
        $accountant->syncPermissions([
            'invoices.view',
            'invoices.download_pdf',
            'payments.view',
            'reports.view',
            'reports.financial',
        ]);

        // 4. Soporte - Solo tickets
        $support = Role::firstOrCreate(['name' => 'Soporte']);
        $support->syncPermissions([
            'tickets.view',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.reply',
            'services.view',
            'clients.view',
        ]);

        // 5. Cliente - Acceso limitado a su información
        $client = Role::firstOrCreate(['name' => 'Cliente']);
        // Los permisos del cliente se manejan en Policies basadas en ownership
    }
}
