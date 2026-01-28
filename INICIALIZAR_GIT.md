# Inicializar Repositorio Git

## 🚀 Pasos para Crear el Repositorio

### Paso 1: Inicializar Git (si no está inicializado)

```bash
cd ~/services.dowgroupcol.com

# Inicializar repositorio
git init

# Agregar todos los archivos
git add .

# Primer commit
git commit -m "Initial commit: CRM Services con FilamentPHP v3"
```

### Paso 2: Conectar con Repositorio Remoto

```bash
# Agregar remote (reemplaza con tu URL)
git remote add origin https://github.com/tu-usuario/Services.dow.git

# O si ya existe, verificar
git remote -v

# Subir código
git branch -M main
git push -u origin main
```

### Paso 3: Verificar que Todo se Subió

```bash
# Ver archivos rastreados
git ls-files | wc -l

# Ver archivos importantes
git ls-files | grep -E "(composer.json|Filament|migrations|views)"
```

## 📋 Archivos Críticos que DEBEN estar

Verifica que estos archivos estén en Git:

```bash
# Verificar archivos importantes
git ls-files | grep -E "(composer.json|bootstrap/providers.php|app/Filament|database/migrations)"
```

### Checklist:

- [ ] `composer.json` - Dependencias
- [ ] `bootstrap/providers.php` - **NUEVO para Laravel 11**
- [ ] `bootstrap/app.php` - Bootstrap de Laravel 11
- [ ] `app/Providers/Filament/AdminPanelProvider.php`
- [ ] `app/Providers/Filament/ClientPanelProvider.php`
- [ ] `app/Filament/Resources/ClientResource.php`
- [ ] `app/Filament/Resources/ServiceResource.php`
- [ ] `database/migrations/*.php` - Todas las migraciones
- [ ] `resources/views/installer/*.blade.php`
- [ ] `routes/web.php`
- [ ] `comandos-cpanel-v3.sh`
- [ ] `.env.example`

## 🔄 Comandos para Subir Cambios Futuros

```bash
# Ver cambios
git status

# Agregar cambios
git add .

# Commit
git commit -m "Descripción del cambio"

# Push
git push
```

## ⚠️ Archivos que NO deben subirse

Estos archivos están en `.gitignore` y NO se subirán:

- `vendor/` - Se instala con `composer install`
- `.env` - Contiene secretos
- `storage/app/.installed` - Flag local
- `node_modules/` - Se instala con npm
- Archivos de caché

---

**Nota:** Si el repositorio ya existe en el servidor, solo necesitas hacer `git pull` para obtener los cambios.
