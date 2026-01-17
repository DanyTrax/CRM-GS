# Pasos Finales - Solucionar Error 500

## ✅ Lo que ya hiciste:
- [x] composer install
- [x] npm install
- [x] Crear .env
- [x] Publicar migraciones de Spatie
- [x] Configurar base de datos

## ⚠️ Lo que falta hacer:

### Paso 1: Ejecutar Migraciones (MUY IMPORTANTE)

```bash
cd ~/services.dowgroupcol.com

# Ejecutar migraciones y seeders
php artisan migrate --seed
```

**Esto creará todas las tablas en la base de datos:**
- Tabla de usuarios
- Tabla de clientes
- Tabla de servicios
- Tabla de facturas
- Tabla de roles y permisos
- Etc.

**Esperar a que termine** (puede tardar 1-2 minutos)

---

### Paso 2: Compilar Assets Frontend

```bash
cd ~/services.dowgroupcol.com
npm run build
```

**Esperar a que termine**

---

### Paso 3: Limpiar TODA la Caché

```bash
cd ~/services.dowgroupcol.com

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché general
php artisan cache:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear
```

---

### Paso 4: Verificar Logs de Error

Si después de los pasos anteriores sigue dando error 500:

```bash
cd ~/services.dowgroupcol.com

# Ver los últimos errores
tail -50 storage/logs/laravel.log
```

**El log mostrará el error específico.** Comparte la última parte del log para solucionarlo.

---

### Paso 5: Verificar Permisos de Storage

```bash
cd ~/services.dowgroupcol.com

# Verificar permisos
ls -ld storage bootstrap/cache

# Si no son 775 o 777, cambiarlos:
chmod -R 775 storage bootstrap/cache

# Verificar que existen las carpetas
ls -la storage/
ls -la bootstrap/cache
```

---

## 🔄 Comandos Todo-en-Uno (En Orden)

Ejecuta estos comandos uno por uno:

```bash
cd ~/services.dowgroupcol.com

# 1. Ejecutar migraciones (CRÍTICO)
php artisan migrate --seed

# 2. Compilar assets
npm run build

# 3. Limpiar toda la caché
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 4. Verificar logs
tail -20 storage/logs/laravel.log
```

---

## 📋 Checklist Completo

Verifica que todo esté correcto:

```bash
cd ~/services.dowgroupcol.com

# ✅ 1. .env existe y tiene APP_KEY
grep APP_KEY .env | grep -v "^#"

# ✅ 2. Base de datos configurada en .env
grep DB_ .env | grep -v "^#"

# ✅ 3. Storage existe con permisos correctos
ls -ld storage bootstrap/cache

# ✅ 4. Migraciones de Spatie publicadas
ls -la database/migrations/*permission*

# ✅ 5. Vendor existe
ls -d vendor/

# ✅ 6. No hay errores recientes (revisar manualmente)
tail -10 storage/logs/laravel.log
```

---

## 🎯 Orden Correcto de Todos los Pasos

```bash
cd ~/services.dowgroupcol.com

# 1. Instalar dependencias
composer install --no-dev --optimize-autoloader
npm install

# 2. Configurar .env
cp .env.example .env
php artisan key:generate

# 3. Crear storage
bash setup-storage.sh

# 4. Publicar Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"

# 5. Configurar base de datos en .env (editar manualmente)
nano .env
# Editar: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. EJECUTAR MIGRACIONES (¡ESTE ES EL PASO QUE FALTA!)
php artisan migrate --seed

# 7. Compilar assets
npm run build

# 8. Limpiar caché
php artisan config:clear
php artisan cache:clear

# 9. Acceder al sistema
# https://services.dowgroupcol.com
```

---

## ❓ Si Sigue dando Error 500

Después de ejecutar `php artisan migrate --seed` y `npm run build`, revisa los logs:

```bash
tail -100 storage/logs/laravel.log
```

El log te dirá exactamente qué está fallando.

---

## 💡 Nota Importante

**El paso más crítico que falta es:**
```bash
php artisan migrate --seed
```

**Sin este paso, la base de datos está vacía y el sistema no puede funcionar.**
