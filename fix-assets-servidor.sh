#!/bin/bash

# Script para corregir problemas de assets en el servidor
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Corrigiendo problemas de assets en el servidor..."

# 1. Verificar APP_URL en .env
echo "📝 Verificando APP_URL..."
if ! grep -q "^APP_URL=" .env; then
    echo "APP_URL=https://services.dowgroupcol.com" >> .env
    echo "✅ APP_URL agregado"
else
    APP_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    echo "APP_URL actual: $APP_URL"
    if [ "$APP_URL" != "https://services.dowgroupcol.com" ]; then
        echo "⚠️  APP_URL no coincide con el dominio. Actualizando..."
        sed -i 's|^APP_URL=.*|APP_URL=https://services.dowgroupcol.com|' .env
        echo "✅ APP_URL actualizado"
    fi
fi

# 2. Verificar permisos de archivos públicos
echo ""
echo "🔐 Verificando permisos..."
chmod -R 755 public/css public/js
chmod -R 644 public/css/filament/**/*.css 2>/dev/null || true
chmod -R 644 public/js/filament/**/*.js 2>/dev/null || true
echo "✅ Permisos actualizados"

# 3. Verificar que los assets existan
echo ""
echo "🔍 Verificando assets..."
if [ -f "public/css/filament/filament/app.css" ]; then
    echo "✅ CSS assets encontrados"
else
    echo "❌ CSS assets NO encontrados. Publicando..."
    php artisan filament:assets
fi

if [ -f "public/js/filament/filament/app.js" ]; then
    echo "✅ JS assets encontrados"
else
    echo "❌ JS assets NO encontrados. Publicando..."
    php artisan filament:assets
fi

# 4. Verificar .htaccess
echo ""
echo "📄 Verificando .htaccess..."
if [ ! -f "public/.htaccess" ]; then
    echo "⚠️  .htaccess no existe. Creando..."
    cat > public/.htaccess << 'EOF'
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
    echo "✅ .htaccess creado"
else
    echo "✅ .htaccess existe"
fi

# 5. Crear .htaccess específico para assets si es necesario
if [ ! -f "public/css/.htaccess" ]; then
    echo ""
    echo "📄 Creando .htaccess para CSS..."
    cat > public/css/.htaccess << 'EOF'
<IfModule mod_headers.c>
    Header set Cache-Control "public, max-age=31536000"
</IfModule>
EOF
    echo "✅ .htaccess para CSS creado"
fi

if [ ! -f "public/js/.htaccess" ]; then
    echo "📄 Creando .htaccess para JS..."
    cat > public/js/.htaccess << 'EOF'
<IfModule mod_headers.c>
    Header set Cache-Control "public, max-age=31536000"
</IfModule>
EOF
    echo "✅ .htaccess para JS creado"
fi

# 6. Limpiar caché
echo ""
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 7. Verificar acceso a assets
echo ""
echo "🔍 Verificando acceso a assets..."
echo "Probando URLs de assets:"
echo "  - CSS: https://services.dowgroupcol.com/css/filament/filament/app.css"
echo "  - JS:  https://services.dowgroupcol.com/js/filament/filament/app.js"
echo ""
echo "Si estas URLs devuelven 404, puede ser un problema de configuración del servidor web."
echo ""

# 8. Verificar configuración de Laravel
echo "📋 Verificando configuración de Laravel..."
php artisan tinker --execute="
    echo 'APP_URL: ' . config('app.url') . PHP_EOL;
    echo 'ASSET_URL: ' . (config('app.asset_url') ?? 'null') . PHP_EOL;
    echo 'Public path: ' . public_path() . PHP_EOL;
"

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Verifica que los assets sean accesibles directamente:"
echo "   https://services.dowgroupcol.com/css/filament/filament/app.css"
echo "   https://services.dowgroupcol.com/js/filament/filament/app.js"
echo ""
echo "2. Si devuelven 404, verifica la configuración del servidor web (Apache/Nginx)"
echo "3. Recarga la página de login con Ctrl+Shift+R"
echo "4. Si aún no funciona, verifica los logs del servidor web"
echo ""
