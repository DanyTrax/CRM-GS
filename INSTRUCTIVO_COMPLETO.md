# 📋 Instructivo Completo - Instalación y Arranque de CRM-GS

## 🎯 Objetivo
Instalar y arrancar el sistema CRM-GS en el servidor `services.dowgroupcol.com`

---

## 📍 Paso 1: Conectarse al Servidor

**Desde SSH:**

```bash
ssh dowgroupcol@tu-servidor
# O usar Terminal de cPanel
```

**Desde cPanel:**
- Ir a "Terminal" o "SSH Access"

---

## 📍 Paso 2: Ir al Directorio del Proyecto

```bash
cd ~/services.dowgroupcol.com
```

**Verificar que estás en el lugar correcto:**
```bash
pwd
# Debe mostrar: /home/dowgroupcol/services.dowgroupcol.com
```

---

## 📍 Paso 3: Verificar PHP (8.2 o superior)

```bash
php -v
```

**Si muestra PHP 8.1 o menos:**
1. Ir a cPanel → "Select PHP Version"
2. Seleccionar `services.dowgroupcol.com`
3. Cambiar a **PHP 8.2** o superior
4. Guardar cambios
5. Verificar nuevamente: `php -v`

---

## 📍 Paso 4: Instalar Dependencias de Composer

```bash
cd ~/services.dowgroupcol.com
composer install --no-dev --optimize-autoloader
```

**Si da error de PHP:**
```bash
# Usar PHP 8.2 explícitamente
/opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

**Esperar a que termine** (puede tomar varios minutos)

---

## 📍 Paso 5: Instalar Dependencias de Node.js

```bash
cd ~/services.dowgroupcol.com
npm install
```

**Esperar a que termine**

---

## 📍 Paso 6: Configurar Archivo .env

```bash
cd ~/services.dowgroupcol.com

# Verificar si .env existe
ls -la .env

# Si NO existe, crearlo desde el ejemplo
cp .env.example .env

# Generar clave de aplicación (OBLIGATORIO)
php artisan key:generate
```

**Verificar que APP_KEY se generó:**
```bash
grep APP_KEY .env
# Debe mostrar: APP_KEY=base64:...
```

---

## 📍 Paso 7: Crear Estructura de Storage (MUY IMPORTANTE)

```bash
cd ~/services.dowgroupcol.com

# Ejecutar el script que crea todo automáticamente
bash setup-storage.sh
```

**Si el script no funciona, crear manualmente:**
```bash
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/backups
mkdir -p bootstrap/cache

# Dar permisos
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**Verificar que se creó:**
```bash
ls -la storage/
ls -la bootstrap/cache
# Deben existir las carpetas
```

---

## 📍 Paso 8: Publicar Migraciones de Spatie (OBLIGATORIO)

```bash
cd ~/services.dowgroupcol.com
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**Debería mostrar:**
```
Copied File [...] to [...]
Publishing complete.
```

---

## 📍 Paso 9: Configurar Base de Datos

**Opción A: Desde cPanel**
1. Ir a cPanel → "MySQL Databases"
2. Crear base de datos (ej: `dowgroupcol_crmgs`)
3. Crear usuario (ej: `dowgroupcol_user`)
4. Asignar usuario a la base de datos
5. Dar todos los privilegios

**Opción B: Editar .env manualmente**
```bash
cd ~/services.dowgroupcol.com
nano .env
```

**Editar estas líneas:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

**Guardar y salir:** `Ctrl+X`, luego `Y`, luego `Enter`

---

## 📍 Paso 10: Ejecutar Migraciones

```bash
cd ~/services.dowgroupcol.com
php artisan migrate --seed
```

**Esto creará todas las tablas y cargará datos iniciales:**
- 5 roles pre-configurados
- Plantillas de correo
- Configuraciones iniciales

**Esperar a que termine** (puede tardar unos minutos)

---

## 📍 Paso 11: Compilar Assets Frontend

```bash
cd ~/services.dowgroupcol.com
npm run build
```

**Esperar a que termine**

---

## 📍 Paso 12: Limpiar Caché

```bash
cd ~/services.dowgroupcol.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 📍 Paso 13: Verificar que Todo Está Bien

```bash
cd ~/services.dowgroupcol.com

# 1. Verificar que .env existe
ls -la .env

# 2. Verificar permisos de storage
ls -ld storage bootstrap/cache

# 3. Verificar que no hay errores en logs
tail -10 storage/logs/laravel.log
```

---

## 📍 Paso 14: Acceder al Sistema

**Abrir en el navegador:**
```
https://services.dowgroupcol.com
```

**O acceder al wizard de instalación:**
```
https://services.dowgroupcol.com/install
```

**O acceder directamente al admin:**
```
https://services.dowgroupcol.com/admin
```

---

## ❌ Si Aparece Error 500

**Ejecutar estos comandos en orden:**

```bash
cd ~/services.dowgroupcol.com

# 1. Crear estructura de storage
bash setup-storage.sh

# 2. Limpiar caché
php artisan config:clear
php artisan cache:clear

# 3. Ver el error específico
tail -50 storage/logs/laravel.log
```

**El log mostrará el error exacto.** Comparte la última parte del log para solucionarlo.

---

## ✅ Checklist Final

Antes de usar el sistema, verifica:

- [ ] PHP 8.2+ instalado (`php -v`)
- [ ] `composer install` ejecutado sin errores
- [ ] `npm install` ejecutado sin errores
- [ ] Archivo `.env` existe y tiene `APP_KEY`
- [ ] `bash setup-storage.sh` ejecutado
- [ ] Migraciones de Spatie publicadas
- [ ] Base de datos configurada en `.env`
- [ ] `php artisan migrate --seed` ejecutado sin errores
- [ ] `npm run build` ejecutado
- [ ] Permisos de storage correctos (775)

---

## 🔧 Comandos de Referencia Rápida

```bash
# Ir al proyecto
cd ~/services.dowgroupcol.com

# Crear storage
bash setup-storage.sh

# Limpiar caché
php artisan config:clear && php artisan cache:clear

# Ver logs
tail -50 storage/logs/laravel.log

# Verificar PHP
php -v

# Verificar permisos
ls -la storage/ bootstrap/cache
```

---

## 📞 Comandos Todo-en-Uno (Después de clonar)

Si ya clonaste el proyecto, ejecuta estos comandos en orden:

```bash
cd ~/services.dowgroupcol.com && \
composer install --no-dev --optimize-autoloader && \
npm install && \
cp .env.example .env && \
php artisan key:generate && \
bash setup-storage.sh && \
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" && \
echo "Ahora edita .env con tus datos de BD y ejecuta: php artisan migrate --seed"
```

**Luego:**
1. Editar `.env` con datos de base de datos
2. Ejecutar: `php artisan migrate --seed`
3. Ejecutar: `npm run build`
4. Acceder al sistema

---

## 💡 Notas Importantes

1. **El paso 7 (setup-storage.sh) es CRÍTICO** - Sin esto, tendrás error 500
2. **El paso 8 (publicar Spatie) es OBLIGATORIO** - Sin esto, las migraciones fallarán
3. **La base de datos debe crearse antes del paso 10**
4. **Los permisos de storage deben ser 775 o 777**

---

¡Listo! Sigue estos pasos en orden y el sistema debería arrancar correctamente.
