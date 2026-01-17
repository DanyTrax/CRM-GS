# Solución: APP_KEY no encontrado en .env

## Problema
```
Unable to set application key. No APP_KEY variable was found in the .env file.
```

El archivo `.env` no tiene la línea `APP_KEY=`.

## Solución: Agregar APP_KEY Manualmente

### Paso 1: Verificar .env

```bash
cd ~/services.dowgroupcol.com

# Ver si APP_KEY existe en .env
grep APP_KEY .env

# Si no muestra nada, no existe la línea
```

### Paso 2: Agregar APP_KEY Manualmente

**Opción 1: Desde SSH**

```bash
cd ~/services.dowgroupcol.com

# Si APP_KEY no existe, agregarlo
if ! grep -q "^APP_KEY=" .env; then
    # Agregar APP_KEY vacío al principio del archivo
    sed -i '1i APP_KEY=' .env
    echo "APP_KEY agregado al .env"
fi

# Ahora generar la clave
php artisan key:generate
```

**Opción 2: Editar Manualmente con nano**

```bash
cd ~/services.dowgroupcol.com
nano .env
```

**Buscar estas líneas al principio del archivo:**

```env
APP_NAME="CRM-GS"
APP_ENV=local
APP_KEY=
```

**Si `APP_KEY=` no existe, agregarlo después de `APP_ENV=`:**

```env
APP_NAME="CRM-GS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
```

**Guardar:** `Ctrl+X`, luego `Y`, luego `Enter`

**Después de agregar la línea `APP_KEY=`, ejecutar:**

```bash
php artisan key:generate
```

### Paso 3: Verificar que se Generó

```bash
cd ~/services.dowgroupcol.com

# Ver APP_KEY generada
grep APP_KEY .env | grep -v "^#"

# Debe mostrar:
# APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

---

## Solución Rápida Todo-en-Uno

```bash
cd ~/services.dowgroupcol.com

# 1. Agregar APP_KEY= si no existe
if ! grep -q "^APP_KEY=" .env; then
    # Buscar la línea APP_ENV y agregar APP_KEY después
    sed -i '/^APP_ENV=/a APP_KEY=' .env
fi

# 2. Generar la clave
php artisan key:generate

# 3. Limpiar caché
php artisan config:clear
php artisan cache:clear

# 4. Verificar
grep APP_KEY .env | grep -v "^#"
```

---

## Si .env no Existe o Está Vacío

```bash
cd ~/services.dowgroupcol.com

# Si .env no existe, crearlo desde .env.example
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ .env creado desde .env.example"
fi

# Ahora generar APP_KEY
php artisan key:generate
```

---

## Verificación Final

Después de ejecutar los pasos:

```bash
cd ~/services.dowgroupcol.com

# Verificar que APP_KEY está configurado
grep "^APP_KEY=" .env

# Debe mostrar algo como:
# APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=

# Limpiar caché
php artisan config:clear
php artisan cache:clear
```

---

## Nota

El archivo `.env` debe tener la línea `APP_KEY=` (incluso vacía) para que `php artisan key:generate` pueda funcionar. Laravel busca esa línea específica en el archivo.
