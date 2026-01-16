<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class InstallController extends Controller
{
    public function index()
    {
        // Si ya está instalado, redirigir
        if (file_exists(base_path('.env')) && DB::connection()->getPdo()) {
            try {
                $userCount = User::count();
                if ($userCount > 0) {
                    return redirect()->route('client.login');
                }
            } catch (\Exception $e) {
                // Continuar con la instalación
            }
        }

        return view('install.index');
    }

    public function checkRequirements(Request $request)
    {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'extensions' => [
                'BCMath' => extension_loaded('bcmath'),
                'Ctype' => extension_loaded('ctype'),
                'JSON' => extension_loaded('json'),
                'Mbstring' => extension_loaded('mbstring'),
                'OpenSSL' => extension_loaded('openssl'),
                'PDO' => extension_loaded('pdo'),
                'XML' => extension_loaded('xml'),
            ],
            'writable' => [
                'storage' => is_writable(storage_path()),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        $allPassed = $requirements['php_version'] 
            && !in_array(false, $requirements['extensions'])
            && !in_array(false, $requirements['writable']);

        return response()->json([
            'requirements' => $requirements,
            'passed' => $allPassed,
        ]);
    }

    public function testDatabase(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            config([
                'database.connections.mysql.host' => $request->host,
                'database.connections.mysql.database' => $request->database,
                'database.connections.mysql.username' => $request->username,
                'database.connections.mysql.password' => $request->password,
            ]);

            DB::connection()->getPdo();

            // Guardar en .env
            $envContent = file_get_contents(base_path('.env.example'));
            $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST={$request->host}", $envContent);
            $envContent = str_replace('DB_DATABASE=crm_gs', "DB_DATABASE={$request->database}", $envContent);
            $envContent = str_replace('DB_USERNAME=root', "DB_USERNAME={$request->username}", $envContent);
            $envContent = str_replace('DB_PASSWORD=', "DB_PASSWORD={$request->password}", $envContent);

            if (!file_exists(base_path('.env'))) {
                file_put_contents(base_path('.env'), $envContent);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            $superAdminRole = Role::where('name', 'Super Administrador')->first();
            if ($superAdminRole) {
                $user->assignRole($superAdminRole);
            }

            return response()->json(['success' => true, 'user_id' => $user->id]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function runMigrations(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('key:generate', ['--force' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
