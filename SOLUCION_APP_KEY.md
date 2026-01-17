# Solución: APP_KEY No Configurado

## Problema
```
✗ APP_KEY NO está configurado
Error: No application encryption key has been specified.
```

## Solución Simple

Ejecuta este comando:

```bash
cd ~/services.dowgroupcol.com
php artisan key:generate
```

Esto generará automáticamente la `APP_KEY` en tu archivo `.env`.

## Verificación

Después de ejecutar el comando:

```bash
cd ~/services.dowgroupcol.com

# Verificar que APP_KEY se generó
grep APP_KEY .env | grep -v "^#"

# Debe mostrar algo como:
# APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

## Limpiar Caché Después

Después de generar la APP_KEY:

```bash
cd ~/services.dowgroupcol.com
php artisan config:clear
php artisan cache:clear
```

## Acceder al Sistema

Después de generar la APP_KEY y limpiar caché:

```
https://services.dowgroupcol.com/install
```

O si ya tienes usuario admin:

```
https://services.dowgroupcol.com/admin
```

---

## Si el Comando No Funciona

**Verificar que .env existe:**

```bash
cd ~/services.dowgroupcol.com

# Si .env no existe, crearlo
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
else
    php artisan key:generate
fi
```

---

## Comando Todo-en-Uno

```bash
cd ~/services.dowgroupcol.com && \
php artisan key:generate && \
php artisan config:clear && \
php artisan cache:clear && \
echo "✓ APP_KEY generado y caché limpiado"
```
