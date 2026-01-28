#!/bin/bash

# Script para verificar el usuario admin creado durante la instalación
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔍 Verificando usuario administrador..."

php artisan tinker --execute="
    try {
        // Buscar usuarios con rol Super Admin
        \$superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
        
        if (\$superAdminRole) {
            echo 'Rol Super Admin encontrado (ID: ' . \$superAdminRole->id . ')' . PHP_EOL;
            echo '' . PHP_EOL;
            
            // Buscar usuarios con este rol
            \$admins = DB::table('users')
                ->where('role_id', \$superAdminRole->id)
                ->select('id', 'name', 'email', 'created_at')
                ->get();
            
            if (\$admins->count() > 0) {
                echo '👤 Usuarios Administradores encontrados:' . PHP_EOL;
                echo '' . PHP_EOL;
                foreach (\$admins as \$admin) {
                    echo '  ID: ' . \$admin->id . PHP_EOL;
                    echo '  Nombre: ' . \$admin->name . PHP_EOL;
                    echo '  Email: ' . \$admin->email . PHP_EOL;
                    echo '  Creado: ' . \$admin->created_at . PHP_EOL;
                    echo '' . PHP_EOL;
                }
                echo '📝 NOTA: La contraseña fue la que ingresaste en el Paso 3 del instalador.' . PHP_EOL;
                echo '   Si no la recuerdas, puedes restablecerla ejecutando:' . PHP_EOL;
                echo '   php artisan tinker' . PHP_EOL;
                echo '   \$user = App\Models\User::where(\"email\", \"TU_EMAIL\")->first();' . PHP_EOL;
                echo '   \$user->password = Hash::make(\"NUEVA_CONTRASEÑA\");' . PHP_EOL;
                echo '   \$user->save();' . PHP_EOL;
            } else {
                echo '⚠️  No se encontraron usuarios con rol Super Admin.' . PHP_EOL;
                echo '   Esto puede significar que la instalación no se completó correctamente.' . PHP_EOL;
            }
        } else {
            echo '❌ El rol Super Admin no existe.' . PHP_EOL;
            echo '   Ejecuta: php artisan db:seed --class=RoleSeeder' . PHP_EOL;
        }
        
        // También mostrar todos los usuarios
        echo '' . PHP_EOL;
        echo '📋 Todos los usuarios en el sistema:' . PHP_EOL;
        \$allUsers = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name', 'users.created_at')
            ->get();
        
        if (\$allUsers->count() > 0) {
            foreach (\$allUsers as \$user) {
                echo '  - ' . \$user->name . ' (' . \$user->email . ') - Rol: ' . (\$user->role_name ?? 'Sin rol') . PHP_EOL;
            }
        } else {
            echo '  No hay usuarios en el sistema.' . PHP_EOL;
        }
        
    } catch (\Exception \$e) {
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        echo '   Verifica que la base de datos esté configurada correctamente.' . PHP_EOL;
    }
"

echo ""
echo "✅ Verificación completada!"
echo ""
