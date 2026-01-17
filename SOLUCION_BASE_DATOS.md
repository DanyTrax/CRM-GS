# Solución: Error de Acceso a Base de Datos

## Problema
```
Access denied for user 'root'@'localhost' (using password: NO)
SQL: delete from `cache`
```

El sistema está intentando conectarse a MySQL con credenciales incorrectas o sin configurar.

---

## Solución: Configurar Base de Datos en .env

### Paso 1: Crear Base de Datos desde cPanel

1. **Ir a cPanel → "MySQL Databases"**
2. **Crear base de datos:**
   - Nombre: `dowgroupcol_crmgs` (o el que prefieras)
   - Click en "Create Database"
   - Guardar el nombre completo (será algo como `dowgroupcol_crmgs`)

3. **Crear usuario:**
   - Nombre de usuario: `dowgroupcol_user` (o el que prefieras)
   - Contraseña: (generar una segura)
   - Click en "Create User"
   - **GUARDAR EL NOMBRE DE USUARIO Y CONTRASEÑA**

4. **Asignar usuario a base de datos:**
   - Seleccionar el usuario y la base de datos
   - Click en "Add"
   - Seleccionar **"ALL PRIVILEGES"**
   - Click en "Make Changes"

### Paso 2: Editar Archivo .env

```bash
cd ~/services.dowgroupcol.com

# Abrir .env para editar
nano .env
```

**Buscar estas líneas y cambiarlas:**

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_completo_de_tu_base_de_datos
DB_USERNAME=nombre_completo_de_tu_usuario
DB_PASSWORD=tu_contraseña
```

**Ejemplo real:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=dowgroupcol_crmgs
DB_USERNAME=dowgroupcol_user
DB_PASSWORD=MiPasswordSeguro123!
```

**Guardar y salir:**
- Presionar `Ctrl+X`
- Presionar `Y` (para confirmar)
- Presionar `Enter` (para guardar)

### Paso 3: Verificar que Funciona

```bash
cd ~/services.dowgroupcol.com

# Probar conexión a la base de datos
php artisan tinker
# En el prompt de tinker, escribir:
DB::connection()->getPdo();
# Si funciona, verás: PDO {#123 ...}
# Salir: exit

# Limpiar caché (ahora debería funcionar)
php artisan config:clear
php artisan cache:clear
```

---

## Verificación Rápida

```bash
cd ~/services.dowgroupcol.com

# Verificar que .env tiene las credenciales
grep DB_ .env | grep -v "^#"

# Debe mostrar:
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_DATABASE=tu_base_datos
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña
```

---

## Si no tienes cPanel o quieres hacerlo desde SSH

```bash
# Conectarse a MySQL (si tienes acceso)
mysql -u root -p

# Dentro de MySQL:
CREATE DATABASE dowgroupcol_crmgs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dowgroupcol_user'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';
GRANT ALL PRIVILEGES ON dowgroupcol_crmgs.* TO 'dowgroupcol_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Luego editar .env como en el Paso 2
```

---

## Notas Importantes

1. **El nombre de la base de datos y usuario en cPanel suele tener un prefijo**
   - Ejemplo: Si creas `crmgs`, puede ser `dowgroupcol_crmgs`
   - Ejemplo: Si creas `user`, puede ser `dowgroupcol_user`

2. **La contraseña debe ser segura**
   - Mínimo 8 caracteres
   - Mezcla de mayúsculas, minúsculas, números y símbolos

3. **No usar 'root' sin contraseña**
   - Es inseguro
   - Generalmente está deshabilitado en servidores compartidos

4. **Guardar las credenciales**
   - Anota el nombre completo de la BD y usuario
   - Anota la contraseña (en lugar seguro)

---

## Después de Configurar

Una vez configurado `.env`, ejecutar:

```bash
cd ~/services.dowgroupcol.com

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché general
php artisan cache:clear

# Publicar migraciones de Spatie (si no lo has hecho)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Ejecutar migraciones
php artisan migrate --seed
```

---

## Verificación Final

```bash
cd ~/services.dowgroupcol.com

# Intentar limpiar caché (no debe dar error)
php artisan cache:clear

# Si funciona, verás: "Application cache cleared!"
# Si da error, revisar credenciales en .env
```
