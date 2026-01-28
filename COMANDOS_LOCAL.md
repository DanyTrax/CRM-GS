# 📤 Comandos para Ejecutar en LOCAL

## 🎯 Objetivo
Subir todos los cambios al repositorio Git desde tu máquina local.

## ✅ Paso a Paso

### 1. Verificar Estado del Repositorio

```bash
cd /Users/soporte/Desktop/repos/Services.dow

# Ver si hay cambios
git status
```

### 2. Ver Archivos Importantes que se Van a Subir

```bash
# Ver archivos de Filament
git status | grep Filament

# Ver archivos de bootstrap (Laravel 11)
git status | grep bootstrap

# Ver migraciones
git status | grep migrations

# Ver composer.json
git status | grep composer.json
```

### 3. Agregar Todos los Cambios

```bash
git add .
```

### 4. Verificar qué se Va a Subir

```bash
git status
```

Deberías ver archivos como:
- `composer.json`
- `bootstrap/providers.php`
- `bootstrap/app.php`
- `app/Filament/Resources/*`
- `app/Providers/Filament/*`
- `database/migrations/*`
- `resources/views/installer/*`
- `comandos-cpanel-v3.sh`
- etc.

### 5. Hacer Commit

```bash
git commit -m "Migración completa a FilamentPHP v3:
- Removido Shield (no compatible con v3)
- Corregido error de Collision
- Agregado bootstrap para Laravel 11
- Creados Resources de Filament
- Actualizados modelos y migraciones
- Scripts de instalación actualizados"
```

### 6. Subir al Repositorio

```bash
# Si es la primera vez o necesitas configurar el remote:
# git remote add origin https://github.com/tu-usuario/tu-repo.git

# Subir cambios
git push origin main
# o
git push origin master
```

## ✅ Verificación

Después de hacer push, verifica:

```bash
# Ver último commit
git log -1

# Ver archivos en el último commit
git show --name-only HEAD
```

## 📋 Checklist Antes de Push

- [ ] `composer.json` actualizado (sin Shield)
- [ ] `bootstrap/providers.php` existe
- [ ] `bootstrap/app.php` existe
- [ ] Resources de Filament creados
- [ ] Modelos actualizados
- [ ] Migraciones con nombres correctos
- [ ] Scripts actualizados
- [ ] `.gitignore` correcto (no sube vendor, .env, etc.)

## 🚨 Si Hay Errores

### Error: "not a git repository"
```bash
git init
git remote add origin [url-del-repo]
```

### Error: "nothing to commit"
```bash
# Verificar que los archivos no estén en .gitignore
git check-ignore -v ruta/al/archivo.php
```

### Error: "remote not found"
```bash
# Verificar remotes
git remote -v

# Agregar remote si falta
git remote add origin [url-del-repo]
```

---

**Una vez subido, en el servidor solo necesitas hacer `git pull` y ejecutar el script.**
