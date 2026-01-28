#!/bin/bash

# Script para corregir la tabla users y crear usuario admin
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Corrigiendo estructura de tabla users y creando usuario admin..."

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
echo "🔧 Corrigiendo estructura de la tabla users..."

php artisan tinker --execute="
    try {
        // 1. Verificar y corregir estructura de la tabla users
        echo '📋 Verificando estructura de la tabla users...' . PHP_EOL;
        
        \$columns = DB::select('SHOW COLUMNS FROM users');
        \$columnNames = array_column(\$columns, 'Field');
        
        // Agregar role_id si no existe
        if (!in_array('role_id', \$columnNames)) {
            echo '  ➕ Agregando columna role_id...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL AFTER password');
                echo '  ✅ Columna role_id agregada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al agregar role_id: ' . \$e->getMessage() . PHP_EOL;
            }
        } else {
            echo '  ✅ Columna role_id ya existe' . PHP_EOL;
        }
        
        // Agregar two_factor_secret si no existe
        if (!in_array('two_factor_secret', \$columnNames)) {
            echo '  ➕ Agregando columna two_factor_secret...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL AFTER role_id');
                echo '  ✅ Columna two_factor_secret agregada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al agregar two_factor_secret: ' . \$e->getMessage() . PHP_EOL;
            }
        } else {
            echo '  ✅ Columna two_factor_secret ya existe' . PHP_EOL;
        }
        
        // Agregar two_factor_recovery_codes si no existe
        if (!in_array('two_factor_recovery_codes', \$columnNames)) {
            echo '  ➕ Agregando columna two_factor_recovery_codes...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULL AFTER two_factor_secret');
                echo '  ✅ Columna two_factor_recovery_codes agregada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al agregar two_factor_recovery_codes: ' . \$e->getMessage() . PHP_EOL;
            }
        } else {
            echo '  ✅ Columna two_factor_recovery_codes ya existe' . PHP_EOL;
        }
        
        // Agregar avatar si no existe
        if (!in_array('avatar', \$columnNames)) {
            echo '  ➕ Agregando columna avatar...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER two_factor_recovery_codes');
                echo '  ✅ Columna avatar agregada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al agregar avatar: ' . \$e->getMessage() . PHP_EOL;
            }
        } else {
            echo '  ✅ Columna avatar ya existe' . PHP_EOL;
        }
        
        // Eliminar columnas antiguas si existen
        if (in_array('google2fa_secret', \$columnNames)) {
            echo '  ➖ Eliminando columna google2fa_secret...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users DROP COLUMN google2fa_secret');
                echo '  ✅ Columna google2fa_secret eliminada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al eliminar google2fa_secret: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        if (in_array('google2fa_enabled', \$columnNames)) {
            echo '  ➖ Eliminando columna google2fa_enabled...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users DROP COLUMN google2fa_enabled');
                echo '  ✅ Columna google2fa_enabled eliminada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al eliminar google2fa_enabled: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        if (in_array('status', \$columnNames)) {
            echo '  ➖ Eliminando columna status...' . PHP_EOL;
            try {
                DB::statement('ALTER TABLE users DROP COLUMN status');
                echo '  ✅ Columna status eliminada' . PHP_EOL;
            } catch (\Exception \$e) {
                echo '  ⚠️  Error al eliminar status: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        // Agregar foreign key para role_id si no existe
        echo '  🔗 Verificando foreign key para role_id...' . PHP_EOL;
        try {
            \$foreignKeys = DB::select(\"
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'role_id'
                AND CONSTRAINT_NAME != 'PRIMARY'
            \");
            
            if (empty(\$foreignKeys)) {
                echo '  ➕ Agregando foreign key para role_id...' . PHP_EOL;
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL');
                echo '  ✅ Foreign key agregada' . PHP_EOL;
            } else {
                echo '  ✅ Foreign key ya existe' . PHP_EOL;
            }
        } catch (\Exception \$e) {
            echo '  ⚠️  Error al agregar foreign key: ' . \$e->getMessage() . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        echo '✅ Estructura de la tabla users corregida' . PHP_EOL;
        echo '' . PHP_EOL;
        
        // 2. Crear roles si no existen
        echo '📋 Verificando roles...' . PHP_EOL;
        \$superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
        
        if (!\$superAdminRole) {
            echo '  ➕ Creando roles...' . PHP_EOL;
            \$roles = [
                ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Administrador principal', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Admin Operativo', 'slug' => 'admin-operativo', 'description' => 'Admin operativo', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Contador', 'slug' => 'contador', 'description' => 'Contador', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Soporte', 'slug' => 'soporte', 'description' => 'Soporte', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cliente', 'slug' => 'cliente', 'description' => 'Cliente', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ];
            
            foreach (\$roles as \$roleData) {
                DB::table('roles')->updateOrInsert(
                    ['slug' => \$roleData['slug']],
                    \$roleData
                );
            }
            
            \$superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
            echo '  ✅ Roles creados' . PHP_EOL;
        } else {
            echo '  ✅ Roles ya existen' . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        
        // 3. Crear usuario admin
        echo '👤 Creando usuario administrador...' . PHP_EOL;
        
        \$existingUser = DB::table('users')->where('email', '$ADMIN_EMAIL')->first();
        
        if (\$existingUser) {
            echo '  ⚠️  El usuario ya existe. Actualizando...' . PHP_EOL;
            
            DB::table('users')
                ->where('email', '$ADMIN_EMAIL')
                ->update([
                    'name' => '$ADMIN_NAME',
                    'password' => Hash::make('$ADMIN_PASSWORD'),
                    'role_id' => \$superAdminRole->id,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
            
            echo '  ✅ Usuario actualizado' . PHP_EOL;
        } else {
            DB::table('users')->insert([
                'name' => '$ADMIN_NAME',
                'email' => '$ADMIN_EMAIL',
                'password' => Hash::make('$ADMIN_PASSWORD'),
                'role_id' => \$superAdminRole->id,
                'email_verified_at' => now(),
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo '  ✅ Usuario creado' . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        echo '✅ Proceso completado exitosamente!' . PHP_EOL;
        echo '' . PHP_EOL;
        echo '📋 Datos del usuario:' . PHP_EOL;
        echo '  Nombre: $ADMIN_NAME' . PHP_EOL;
        echo '  Email: $ADMIN_EMAIL' . PHP_EOL;
        echo '  Contraseña: (la que ingresaste)' . PHP_EOL;
        echo '  Rol: Super Admin' . PHP_EOL;
        echo '' . PHP_EOL;
        echo '🔐 Puedes iniciar sesión en:' . PHP_EOL;
        echo '   https://services.dowgroupcol.com/admin/login' . PHP_EOL;
        
    } catch (\Exception \$e) {
        echo '' . PHP_EOL;
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        echo '   Trace: ' . \$e->getTraceAsString() . PHP_EOL;
        exit(1);
    }
"

echo ""
echo "✅ Proceso completado!"
echo ""
