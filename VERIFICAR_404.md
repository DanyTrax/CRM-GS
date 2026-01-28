# Solución: Error 404 en /admin

## Problema
Acceder a `https://services.dowgroupcol.com/admin` da error 404.

## Causas Posibles

### 1. El usuario no está autenticado
Las rutas de `/admin` requieren autenticación. Si no estás logueado, puede dar 404 o redirigir.

**Solución:** Primero intenta acceder a `/install` o crear una ruta de login.

---

## Solución: Verificar Pasos en el Servidor

Ejecuta estos comandos en orden:

```bash
cd ~/services.dowgroupcol.com

# 1. Verificar que index.php existe en la raíz
ls -la index.php
# Debe existir

# 2. Verificar que .htaccess existe en la raíz
ls -la .htaccess
# Debe existir

# 3. Si no existen, copiarlos
if [ ! -f index.php ]; then
    echo "ERROR: index.php no existe en la raíz"
    # Si está en public/, moverlo:
    # cp public/index.php index.php
fi

if [ ! -f .htaccess ]; then
    echo "ERROR: .htaccess no existe en la raíz"
    # Crear desde public/ o crear nuevo
    cp public/.htaccess .htaccess 2>/dev/null || echo "No se encontró .htaccess en public/"
fi

# 4. Verificar rutas de Laravel
php artisan route:list | grep admin

# Debe mostrar rutas como:
# GET|HEAD  admin ......................... admin.dashboard
```

---

## Solución: Probar Ruta Base Primero

Antes de `/admin`, prueba estas rutas:

```bash
# En el navegador, intentar:
https://services.dowgroupcol.com/
https://services.dowgroupcol.com/install
https://services.dowgroupcol.com/client/login
```

Si estas rutas funcionan, el problema es que `/admin` requiere autenticación.

---

## Solución: Crear Usuario Administrador

Si no tienes usuario admin, crearlo:

```bash
cd ~/services.dowgroupcol.com

# Opción 1: Usar el wizard
# Acceder a: https://services.dowgroupcol.com/install

# Opción 2: Crear manualmente desde tinker
php artisan tinker

# Dentro de tinker:
$user = \App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('tu_contraseña_segura'),
    'status' => 'active',
]);
$user->assignRole('Super Administrador');
exit
```

---

## Verificación Completa

```bash
cd ~/services.dowgroupcol.com

# Checklist completo
echo "=== Verificación ==="
[ -f index.php ] && echo "✓ index.php existe" || echo "✗ index.php NO existe"
[ -f .htaccess ] && echo "✓ .htaccess existe" || echo "✗ .htaccess NO existe"
[ -f .env ] && echo "✓ .env existe" || echo "✗ .env NO existe"
php artisan route:list 2>&1 | grep -q "admin" && echo "✓ Rutas admin registradas" || echo "✗ Rutas admin NO registradas"
```

---

## Si el Problema Persiste

Comparte el output de:

```bash
cd ~/services.dowgroupcol.com
ls -la index.php .htaccess artisan
php artisan route:list | grep -E "(admin|install|client)"
```

Esto ayudará a identificar el problema específico.
