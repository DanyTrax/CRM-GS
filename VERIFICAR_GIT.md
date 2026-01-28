# Verificar que los Archivos se Suben al Repositorio

## 🔍 Verificación Rápida

Para verificar qué archivos están siendo rastreados por Git:

```bash
# Ver archivos rastreados
git ls-files

# Ver archivos modificados/agregados
git status

# Ver qué archivos NO están en .gitignore
git status --ignored
```

## ✅ Archivos que DEBEN estar en el Repositorio

Los siguientes archivos/carpetas **SÍ deben estar** en Git:

- ✅ `app/` - Todo el código de la aplicación
- ✅ `database/migrations/` - Migraciones
- ✅ `database/seeders/` - Seeders
- ✅ `resources/views/` - Vistas Blade
- ✅ `routes/` - Rutas
- ✅ `config/` - Configuración
- ✅ `bootstrap/` - Bootstrap de Laravel
- ✅ `public/` - Archivos públicos (excepto storage)
- ✅ `composer.json` - Dependencias
- ✅ `.env.example` - Ejemplo de configuración
- ✅ `README.md` - Documentación
- ✅ Scripts `.sh` - Scripts de instalación

## ❌ Archivos que NO deben estar (en .gitignore)

- ❌ `vendor/` - Dependencias de Composer
- ❌ `.env` - Variables de entorno (contiene secretos)
- ❌ `storage/app/.installed` - Flag de instalación
- ❌ `node_modules/` - Dependencias de NPM
- ❌ Archivos de caché

## 🔧 Comandos para Subir Cambios

```bash
# 1. Ver qué archivos han cambiado
git status

# 2. Agregar todos los archivos nuevos/modificados
git add .

# 3. Verificar qué se va a subir
git status

# 4. Hacer commit
git commit -m "Descripción de los cambios"

# 5. Subir al repositorio
git push origin main
# o
git push origin master
```

## 📋 Checklist de Archivos Importantes

Verifica que estos archivos estén en el repositorio:

```bash
# Verificar que existen en Git
git ls-files | grep -E "(composer.json|app/Filament|database/migrations|resources/views|routes)"
```

### Archivos Críticos que DEBEN estar:

- [ ] `composer.json` - Con todas las dependencias
- [ ] `app/Providers/Filament/AdminPanelProvider.php`
- [ ] `app/Providers/Filament/ClientPanelProvider.php`
- [ ] `app/Filament/Resources/ClientResource.php`
- [ ] `app/Filament/Resources/ServiceResource.php`
- [ ] `database/migrations/*.php` - Todas las migraciones
- [ ] `database/seeders/DatabaseSeeder.php`
- [ ] `resources/views/installer/*.blade.php` - Vistas del instalador
- [ ] `routes/web.php`
- [ ] `bootstrap/providers.php` - **NUEVO para Laravel 11**
- [ ] `comandos-cpanel-v3.sh` - Script de instalación

## 🚨 Si un Archivo NO se Sube

Si un archivo importante no se está subiendo:

1. **Verificar .gitignore:**
```bash
git check-ignore -v ruta/al/archivo.php
```

2. **Forzar agregar (si es necesario):**
```bash
git add -f ruta/al/archivo.php
```

3. **Verificar que no esté en .gitignore:**
```bash
cat .gitignore | grep -i "nombre-del-archivo"
```

## 📤 Subir Cambios Actuales

Si acabas de hacer cambios y quieres subirlos:

```bash
cd ~/ruta/del/proyecto

# Ver cambios
git status

# Agregar todo
git add .

# Commit
git commit -m "Migración a FilamentPHP v3 - Removido Shield, corregido Collision"

# Push
git push
```

---

**Nota:** El archivo `bootstrap/providers.php` es **NUEVO en Laravel 11** y debe estar en el repositorio.
