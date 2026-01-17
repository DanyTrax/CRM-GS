# Solución: Error 404 Not Found

## Problema
```
Not Found
The requested URL was not found on this server.
```

El servidor no está encontrando las rutas de Laravel.

---

## Solución 1: Verificar que index.php está en la Raíz

```bash
cd ~/services.dowgroupcol.com

# Verificar que index.php existe en la raíz
ls -la index.php

# Si NO existe, copiarlo desde public/ o del repositorio
# El archivo index.php debe estar en:
# ~/services.dowgroupcol.com/index.php
```

---

## Solución 2: Verificar .htaccess

```bash
cd ~/services.dowgroupcol.com

# Verificar que .htaccess existe en la raíz
ls -la .htaccess

# Si NO existe, crearlo o copiarlo desde public/
cp public/.htaccess .htaccess 2>/dev/null || echo ".htaccess no está en public/"
```

**El .htaccess debe estar en la raíz `services.dowgroupcol.com/`**

**Contenido del .htaccess:**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## Solución 3: Verificar Rutas de Laravel

```bash
cd ~/services.dowgroupcol.com

# Verificar que las rutas están registradas
php artisan route:list | grep admin

# Debe mostrar rutas como:
# GET|HEAD  admin ........................... admin.dashboard
# etc.
```

---

## Solución 4: Verificar Archivos en la Raíz

Asegúrate de que estos archivos estén en la raíz:

```bash
cd ~/services.dowgroupcol.com

ls -la | grep -E "(index.php|\.htaccess|artisan)"

# Debe mostrar:
# -rw-r--r--  artisan
# -rw-r--r--  .htaccess
# -rw-r--r--  index.php
```

---

## Solución 5: Verificar Permisos

```bash
cd ~/services.dowgroupcol.com

# Los archivos deben tener permisos de lectura
chmod 644 index.php .htaccess
chmod 755 artisan
```

---

## Solución 6: Probar Ruta Base

Intenta acceder a la ruta raíz primero:

```
https://services.dowgroupcol.com/
```

O:

```
https://services.dowgroupcol.com/install
```

Si estas rutas funcionan, el problema es específico de `/admin`.

---

## Solución 7: Verificar Configuración del Dominio en cPanel

En cPanel:

1. Ir a **"Subdomains"** o **"Addon Domains"**
2. Verificar que `services.dowgroupcol.com` está configurado correctamente
3. Verificar que apunta al directorio `services.dowgroupcol.com`

---

## Solución 8: Crear .htaccess Manualmente

Si el .htaccess no existe:

```bash
cd ~/services.dowgroupcol.com

# Crear .htaccess
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# Verificar que se creó
cat .htaccess
```

---

## Verificación Rápida

Ejecuta este checklist:

```bash
cd ~/services.dowgroupcol.com

# 1. Verificar index.php
[ -f index.php ] && echo "✓ index.php existe" || echo "✗ index.php NO existe"

# 2. Verificar .htaccess
[ -f .htaccess ] && echo "✓ .htaccess existe" || echo "✗ .htaccess NO existe"

# 3. Verificar rutas
php artisan route:list 2>&1 | head -5

# 4. Verificar permisos
ls -la index.php .htaccess artisan
```

---

## Comandos Todo-en-Uno

```bash
cd ~/services.dowgroupcol.com && \
[ ! -f .htaccess ] && cp public/.htaccess .htaccess 2>/dev/null || true && \
[ ! -f index.php ] && echo "ERROR: index.php debe estar en la raíz" && \
php artisan route:list | grep admin && \
echo "Verifica que .htaccess y index.php estén en la raíz del proyecto"
```

---

## Nota Importante

Si moviste el contenido de `public/` a la raíz, asegúrate de:
1. ✅ `index.php` está en `~/services.dowgroupcol.com/index.php` (raíz)
2. ✅ `.htaccess` está en `~/services.dowgroupcol.com/.htaccess` (raíz)
3. ✅ Las rutas en `index.php` apuntan correctamente (sin `/public`)

---

## Si el Problema Persiste

Comparte el output de:

```bash
cd ~/services.dowgroupcol.com
ls -la index.php .htaccess
php artisan route:list | head -10
```

Esto ayudará a identificar el problema específico.
