#!/bin/bash

# Script para crear usuario administrador manualmente
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "👤 Creando usuario administrador..."

# Solicitar datos al usuario
echo ""
echo "Ingresa los datos del usuario administrador:"
echo ""
read -p "Nombre completo: " ADMIN_NAME
read -p "Email: " ADMIN_EMAIL
read -sp "Contraseña (mínimo 8 caracteres): " ADMIN_PASSWORD
echo ""
read -sp "Confirmar contraseña: " ADMIN_PASSWORD_CONFIRM
echo ""

# Validar que las contraseñas coincidan
if [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; then
    echo "❌ Error: Las contraseñas no coinciden"
    exit 1
fi

# Validar longitud de contraseña
if [ ${#ADMIN_PASSWORD} -lt 8 ]; then
    echo "❌ Error: La contraseña debe tener al menos 8 caracteres"
    exit 1
fi

# Validar que se ingresaron datos
if [ -z "$ADMIN_NAME" ] || [ -z "$ADMIN_EMAIL" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "❌ Error: Todos los campos son obligatorios"
    exit 1
fi

echo ""
echo "🔧 Creando usuario en la base de datos..."

php artisan tinker --execute="
    try {
        // Verificar que el rol Super Admin existe
        \$superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
        
        if (!\$superAdminRole) {
            echo '❌ Error: El rol Super Admin no existe. Creando roles primero...' . PHP_EOL;
            
            // Crear roles si no existen
            \$roles = [
                ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Administrador principal', 'is_active' => 1],
                ['name' => 'Admin Operativo', 'slug' => 'admin-operativo', 'description' => 'Admin operativo', 'is_active' => 1],
                ['name' => 'Contador', 'slug' => 'contador', 'description' => 'Contador', 'is_active' => 1],
                ['name' => 'Soporte', 'slug' => 'soporte', 'description' => 'Soporte', 'is_active' => 1],
                ['name' => 'Cliente', 'slug' => 'cliente', 'description' => 'Cliente', 'is_active' => 1],
            ];
            
            foreach (\$roles as \$roleData) {
                DB::table('roles')->updateOrInsert(
                    ['slug' => \$roleData['slug']],
                    \$roleData
                );
            }
            
            \$superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
            echo '✅ Roles creados' . PHP_EOL;
        }
        
        // Verificar si el usuario ya existe
        \$existingUser = DB::table('users')->where('email', '$ADMIN_EMAIL')->first();
        
        if (\$existingUser) {
            echo '⚠️  El usuario con email $ADMIN_EMAIL ya existe. Actualizando...' . PHP_EOL;
            
            // Actualizar usuario existente
            DB::table('users')
                ->where('email', '$ADMIN_EMAIL')
                ->update([
                    'name' => '$ADMIN_NAME',
                    'password' => Hash::make('$ADMIN_PASSWORD'),
                    'role_id' => \$superAdminRole->id,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
            
            echo '✅ Usuario actualizado exitosamente' . PHP_EOL;
        } else {
            // Crear nuevo usuario
            // Verificar qué columnas tiene la tabla users
            \$columns = DB::select('SHOW COLUMNS FROM users');
            \$columnNames = array_column(\$columns, 'Field');
            
            \$userData = [
                'name' => '$ADMIN_NAME',
                'email' => '$ADMIN_EMAIL',
                'password' => Hash::make('$ADMIN_PASSWORD'),
                'email_verified_at' => now(),
                'role_id' => \$superAdminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Solo agregar columnas que existen en la tabla
            if (in_array('two_factor_secret', \$columnNames)) {
                \$userData['two_factor_secret'] = null;
            }
            if (in_array('two_factor_recovery_codes', \$columnNames)) {
                \$userData['two_factor_recovery_codes'] = null;
            }
            if (in_array('avatar', \$columnNames)) {
                \$userData['avatar'] = null;
            }
            
            DB::table('users')->insert(\$userData);
            
            echo '✅ Usuario creado exitosamente' . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        echo '📋 Datos del usuario:' . PHP_EOL;
        echo '  Nombre: $ADMIN_NAME' . PHP_EOL;
        echo '  Email: $ADMIN_EMAIL' . PHP_EOL;
        echo '  Rol: Super Admin' . PHP_EOL;
        echo '' . PHP_EOL;
        echo '✅ Usuario listo para usar!' . PHP_EOL;
        echo '   Puedes iniciar sesión en: https://services.dowgroupcol.com/admin/login' . PHP_EOL;
        
    } catch (\Exception \$e) {
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        echo '   Trace: ' . \$e->getTraceAsString() . PHP_EOL;
        exit(1);
    }
"

echo ""
echo "✅ Proceso completado!"
echo ""
