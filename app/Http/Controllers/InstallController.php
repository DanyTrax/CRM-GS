<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Role;
use Hash;

class InstallController extends Controller
{
    /**
     * Verificar si ya está instalado
     */
    public function __construct()
    {
        // IMPORTANTE: Forzar sesión y caché en archivos durante la instalación
        // para evitar errores de conexión a BD antes de que esté configurada
        config([
            'session.driver' => 'file',
            'cache.default' => 'file', // Usar archivos para caché también
        ]);
        
        $isInstalled = file_exists(base_path('.env')) && 
                      file_exists(storage_path('app/.installed')) &&
                      file_exists(base_path('vendor/autoload.php'));
        
        if ($isInstalled && !request()->get('force')) {
            abort(404, 'El sistema ya está instalado.');
        }
    }

    /**
     * Paso 1: Verificar Requisitos
     */
    public function requirements()
    {
        return view('installer.requirements');
    }

    /**
     * Verificar si Composer está instalado
     */
    public function checkComposer()
    {
        try {
            $composerPath = $this->findComposer();
            
            // También verificar si vendor/autoload.php existe
            $vendorExists = file_exists(base_path('vendor/autoload.php'));
            
            return response()->json([
                'installed' => $composerPath !== null || $vendorExists,
                'path' => $composerPath,
                'vendor_exists' => $vendorExists,
            ]);
        } catch (\Exception $e) {
            // Si hay algún error, asumir que Composer no está disponible
            // pero no fallar completamente
            return response()->json([
                'installed' => file_exists(base_path('vendor/autoload.php')),
                'path' => null,
                'vendor_exists' => file_exists(base_path('vendor/autoload.php')),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Paso 2: Configuración de Base de Datos
     */
    public function database()
    {
        return view('installer.database');
    }

    /**
     * Probar conexión a base de datos
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);

            DB::connection('mysql')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa a la base de datos',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Guardar configuración de base de datos
     */
    public function saveDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // IMPORTANTE: Cambiar sesión y caché a 'file' durante la instalación
            // para evitar errores de conexión a BD antes de que esté configurada
            // Limpiar el nombre de la base de datos (eliminar espacios y caracteres especiales)
            $dbName = trim($request->db_database);
            
            $this->updateEnv([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?: '',
                'SESSION_DRIVER' => 'file', // Usar archivos durante instalación
                'CACHE_DRIVER' => 'file', // Usar archivos para caché también
            ]);

            // Actualizar configuración temporal
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
                'session.driver' => 'file', // Forzar sesión en archivos
                'cache.default' => 'file', // Forzar caché en archivos
            ]);

            // Generar APP_KEY si no existe
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // Limpiar caché de configuración para que tome los nuevos valores
            // Usar try-catch para evitar errores si la BD aún no está lista
            try {
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignorar errores de caché durante instalación
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Asegurar que siempre devolvemos JSON válido
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Paso 3: Crear Usuario Administrador
     */
    public function admin()
    {
        return view('installer.admin');
    }

    /**
     * Guardar usuario administrador
     */
    public function saveAdmin(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Verificar si el email ya existe (pero no usar unique:users porque la tabla puede no existir aún)
            // Esto se validará en el paso de complete() cuando se cree el usuario

            // Guardar datos en sesión para usarlos en complete()
            session([
                'admin_name' => $request->name,
                'admin_email' => $request->email,
                'admin_password' => $request->password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos de administrador guardados correctamente',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors()['password'] ?? $e->errors()['email'] ?? $e->errors()['name'] ?? ['Error desconocido']),
                'errors' => $e->errors(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Paso 4: Finalizar Instalación
     */
    public function finish()
    {
        return view('installer.finish');
    }

    /**
     * Completar instalación (ejecutar migraciones y seeders)
     */
    public function complete()
    {
        try {
            // 1. Verificar/Instalar dependencias de Composer
            if (!$this->checkComposerDependencies()) {
                $this->runComposerInstall();
            }

            // 2. Generar APP_KEY si no existe
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // 3. Verificar si la tabla migrations existe y tiene registros
            $migrationsTableExists = false;
            $migrationsCount = 0;
            
            try {
                // Verificar si la tabla migrations existe
                $migrationsTableExists = DB::getSchemaBuilder()->hasTable('migrations');
                
                if ($migrationsTableExists) {
                    // Contar migraciones ejecutadas
                    $migrationsCount = DB::table('migrations')->count();
                }
            } catch (\Exception $e) {
                // Si hay error, asumir que no existe
                $migrationsTableExists = false;
                $migrationsCount = 0;
            }

            // 4. Verificar si las tablas principales ya existen
            $usersTableExists = false;
            try {
                $usersTableExists = DB::getSchemaBuilder()->hasTable('users');
            } catch (\Exception $e) {
                $usersTableExists = false;
            }

            // 5. Verificar si la tabla users tiene la estructura correcta
            $usersTableNeedsUpdate = false;
            if ($usersTableExists) {
                try {
                    $columns = DB::select('SHOW COLUMNS FROM users');
                    $columnNames = array_column($columns, 'Field');
                    $usersTableNeedsUpdate = !in_array('role_id', $columnNames);
                } catch (\Exception $e) {
                    $usersTableNeedsUpdate = false;
                }
            }

            // 6. Ejecutar migraciones
            if ($usersTableExists && $migrationsTableExists && $migrationsCount > 0 && !$usersTableNeedsUpdate) {
                // Si las tablas ya existen y hay migraciones registradas, ejecutar solo pendientes
                Artisan::call('migrate', ['--force' => true]);
            } elseif ($usersTableExists && (!$migrationsTableExists || $usersTableNeedsUpdate)) {
                // Si las tablas existen pero no hay tabla migrations, crear la tabla migrations primero
                // y luego marcar las migraciones como ejecutadas
                try {
                    // Crear tabla migrations si no existe
                    if (!$migrationsTableExists) {
                        DB::statement("CREATE TABLE IF NOT EXISTS `migrations` (
                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                            `migration` varchar(255) NOT NULL,
                            `batch` int(11) NOT NULL,
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    }
                    
                    // Obtener todas las migraciones del directorio
                    $migrationFiles = glob(database_path('migrations/*.php'));
                    $batch = 1;
                    
                    foreach ($migrationFiles as $file) {
                        $migrationName = basename($file, '.php');
                        
                        // Verificar si ya está registrada
                        $exists = DB::table('migrations')
                            ->where('migration', $migrationName)
                            ->exists();
                        
                        if (!$exists) {
                            DB::table('migrations')->insert([
                                'migration' => $migrationName,
                                'batch' => $batch,
                            ]);
                        }
                    }
                    
                    // Ahora ejecutar solo migraciones pendientes
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Exception $e) {
                    // Si falla, intentar migrar normalmente (puede fallar si las tablas existen)
                    // En este caso, simplemente continuar sin ejecutar migraciones
                }
            } else {
                // Si no hay tablas, ejecutar migraciones normalmente
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Exception $e) {
                    // Si falla porque las tablas ya existen, intentar registrar las migraciones
                    if (str_contains($e->getMessage(), 'already exists')) {
                        // Las tablas existen pero no están registradas en migrations
                        // Crear tabla migrations y registrar todas las migraciones
                        try {
                            if (!DB::getSchemaBuilder()->hasTable('migrations')) {
                                DB::statement("CREATE TABLE IF NOT EXISTS `migrations` (
                                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                    `migration` varchar(255) NOT NULL,
                                    `batch` int(11) NOT NULL,
                                    PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                            }
                            
                            $migrationFiles = glob(database_path('migrations/*.php'));
                            $batch = 1;
                            
                            foreach ($migrationFiles as $file) {
                                $migrationName = basename($file, '.php');
                                
                                $exists = DB::table('migrations')
                                    ->where('migration', $migrationName)
                                    ->exists();
                                
                                if (!$exists) {
                                    DB::table('migrations')->insert([
                                        'migration' => $migrationName,
                                        'batch' => $batch,
                                    ]);
                                }
                            }
                        } catch (\Exception $e2) {
                            // Si falla, continuar de todas formas
                        }
                    } else {
                        throw $e;
                    }
                }
            }

            // 5. Ejecutar seeders (solo si no hay datos)
            try {
                $userCount = User::count();
                $roleCount = Role::count();
                
                // Solo ejecutar seeders si no hay datos
                if ($userCount === 0 || $roleCount === 0) {
                    Artisan::call('db:seed', ['--force' => true, '--class' => 'DatabaseSeeder']);
                }
            } catch (\Exception $e) {
                // Si hay error, intentar ejecutar seeders de todas formas
                Artisan::call('db:seed', ['--force' => true, '--class' => 'DatabaseSeeder']);
            }

            // 6. Crear usuario administrador (solo si no existe)
            $adminName = session('admin_name');
            $adminEmail = session('admin_email');
            $adminPassword = session('admin_password');

            if ($adminName && $adminEmail && $adminPassword) {
                // Verificar si el usuario ya existe
                $existingUser = User::where('email', $adminEmail)->first();
                
                if (!$existingUser) {
                    $superAdminRole = Role::where('slug', 'super-admin')->first();
                    
                    if ($superAdminRole) {
                        User::create([
                            'name' => $adminName,
                            'email' => $adminEmail,
                            'password' => Hash::make($adminPassword),
                            'role_id' => $superAdminRole->id,
                            'email_verified_at' => now(),
                        ]);
                    }
                } else {
                    // Si el usuario ya existe, actualizar la contraseña
                    $existingUser->update([
                        'password' => Hash::make($adminPassword),
                    ]);
                }
            }

            // 7. Crear directorios necesarios
            $this->createRequiredDirectories();

            // 8. Crear archivo de instalación completada
            File::put(storage_path('app/.installed'), now()->toDateTimeString());

            // 9. Asegurar que CACHE_DRIVER esté en 'file' (no 'database')
            $this->updateEnv([
                'CACHE_DRIVER' => 'file',
            ]);
            
            // 10. Limpiar caché (con manejo de errores)
            try {
                Artisan::call('config:clear');
                // Solo limpiar caché de BD si la tabla existe
                try {
                    if (DB::getSchemaBuilder()->hasTable('cache')) {
                        Artisan::call('cache:clear');
                    }
                } catch (\Exception $e) {
                    // Ignorar si la tabla no existe
                }
            } catch (\Exception $e) {
                // Ignorar errores de caché
            }
            
            // 11. Publicar assets de Filament
            try {
                Artisan::call('filament:assets');
            } catch (\Exception $e) {
                // Ignorar si ya están publicados
            }

            return response()->json([
                'success' => true,
                'message' => 'Instalación completada exitosamente',
                'redirect' => route('login'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error durante la instalación: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Verificar si las dependencias de Composer están instaladas
     */
    protected function checkComposerDependencies(): bool
    {
        return file_exists(base_path('vendor/autoload.php'));
    }

    /**
     * Ejecutar composer install
     */
    protected function runComposerInstall()
    {
        $composerPath = $this->findComposer();
        
        if (!$composerPath) {
            throw new \Exception('Composer no encontrado. Por favor, instala Composer primero.');
        }

        $command = "{$composerPath} install --no-interaction --prefer-dist --optimize-autoloader 2>&1";
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception('Error al instalar dependencias: ' . implode("\n", $output));
        }
    }

    /**
     * Encontrar ruta de Composer
     */
    protected function findComposer(): ?string
    {
        try {
            $paths = [
                'composer',
                base_path('composer.phar'),
                '/usr/local/bin/composer',
                '/usr/bin/composer',
                '/opt/cpanel/composer/bin/composer', // cPanel específico
                '/usr/local/cpanel/3rdparty/bin/composer', // cPanel alternativo
            ];

            foreach ($paths as $path) {
                // Verificar si el archivo existe y es ejecutable
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }

            // Intentar encontrar con which (solo si shell_exec está permitido)
            if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
                $whichComposer = @shell_exec('which composer 2>/dev/null');
                if ($whichComposer && trim($whichComposer)) {
                    return trim($whichComposer);
                }
            }

            // Si no se encuentra, verificar si vendor/autoload.php existe
            // Esto indica que las dependencias ya están instaladas
            if (file_exists(base_path('vendor/autoload.php'))) {
                // Retornar un valor que indique que las dependencias están instaladas
                return 'vendor/autoload.php exists';
            }

            return null;
        } catch (\Exception $e) {
            // En caso de error, retornar null
            return null;
        }
    }

    /**
     * Crear directorios necesarios
     */
    protected function createRequiredDirectories()
    {
        $directories = [
            storage_path('app/backups'),
            storage_path('app/public'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Actualizar archivo .env
     */
    protected function updateEnv(array $data)
    {
        $envFile = base_path('.env');

        if (!File::exists($envFile)) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), $envFile);
            } else {
                // Crear .env básico con sesión y caché en archivos
                File::put($envFile, "APP_NAME=CRM\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\nSESSION_DRIVER=file\nCACHE_DRIVER=file\n\n");
            }
        }

        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Limpiar el valor (eliminar espacios al inicio y final)
            $value = trim($value);
            
            // Escapar caracteres especiales en el valor
            $escapedValue = $value;
            // Si el valor contiene espacios, #, =, comillas simples o dobles, ponerlo entre comillas
            if (preg_match('/[\s#=\'"]/', $value)) {
                $escapedValue = '"' . str_replace('"', '\\"', $value) . '"';
            }
            
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$escapedValue}", $envContent);
            } else {
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }

        File::put($envFile, $envContent);
        
        // Recargar variables de entorno
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Limpiar caché de configuración para que tome los nuevos valores
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignorar errores
        }
    }
}
