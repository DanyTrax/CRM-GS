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
        // IMPORTANTE: Forzar sesión en archivos durante la instalación
        // para evitar errores de conexión a BD antes de que esté configurada
        config(['session.driver' => 'file']);
        
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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ], 400);
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
            // IMPORTANTE: Cambiar sesión a 'file' durante la instalación
            // para evitar errores de conexión a BD antes de que esté configurada
            $this->updateEnv([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?: '',
                'SESSION_DRIVER' => 'file', // Usar archivos durante instalación
            ]);

            // Actualizar configuración temporal
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
                'session.driver' => 'file', // Forzar sesión en archivos
            ]);

            // Generar APP_KEY si no existe
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // Limpiar caché de configuración para que tome los nuevos valores
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Guardar datos en sesión para usarlos en complete()
        session([
            'admin_name' => $request->name,
            'admin_email' => $request->email,
            'admin_password' => $request->password,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Datos de administrador guardados',
        ]);
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

            // 3. Ejecutar migraciones
            Artisan::call('migrate', ['--force' => true]);

            // 4. Ejecutar seeders
            Artisan::call('db:seed', ['--force' => true, '--class' => 'DatabaseSeeder']);

            // 5. Crear usuario administrador
            $adminName = session('admin_name');
            $adminEmail = session('admin_email');
            $adminPassword = session('admin_password');

            if ($adminName && $adminEmail && $adminPassword) {
                $superAdminRole = Role::where('slug', 'super-admin')->first();
                
                if ($superAdminRole) {
                    $user = User::create([
                        'name' => $adminName,
                        'email' => $adminEmail,
                        'password' => Hash::make($adminPassword),
                        'role_id' => $superAdminRole->id,
                        'email_verified_at' => now(),
                    ]);
                }
            }

            // 6. Crear directorios necesarios
            $this->createRequiredDirectories();

            // 7. Crear archivo de instalación completada
            File::put(storage_path('app/.installed'), now()->toDateTimeString());

            // 8. Limpiar caché
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => 'Instalación completada exitosamente',
                'redirect' => route('login'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error durante la instalación: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
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
                // Crear .env básico con sesión en archivos
                File::put($envFile, "APP_NAME=CRM\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\nSESSION_DRIVER=file\n\n");
            }
        }

        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Escapar caracteres especiales en el valor
            $escapedValue = $value;
            // Si el valor contiene espacios o caracteres especiales, ponerlo entre comillas
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
    }
}
