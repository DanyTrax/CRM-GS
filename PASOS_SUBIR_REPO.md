# 📤 Pasos para Subir Cambios al Repositorio

## 🎯 Repositorio: https://github.com/DanyTrax/CRM-GS

## ✅ Estado Actual

- ✅ Git inicializado
- ✅ Remote configurado: `origin -> https://github.com/DanyTrax/CRM-GS.git`
- ✅ 110 archivos listos para agregar
- ⚠️ El repositorio remoto ya tiene contenido en `main`

## 🚀 Opción 1: Script Automático (Recomendado)

```bash
cd /Users/soporte/Desktop/repos/Services.dow
./SUBIR_CAMBIOS.sh
```

El script te preguntará si quieres hacer pull primero (recomendado).

## 🚀 Opción 2: Comandos Manuales

### Paso 1: Traer Cambios del Repositorio Remoto

```bash
cd /Users/soporte/Desktop/repos/Services.dow

# Traer cambios existentes
git pull origin main --allow-unrelated-histories
```

Si hay conflictos, resuélvelos manualmente y luego:
```bash
git add .
git commit -m "Merge con repositorio remoto"
```

### Paso 2: Agregar Todos los Cambios

```bash
# Ver qué archivos se van a agregar
git status

# Agregar todos los archivos
git add .
```

### Paso 3: Crear Commit

```bash
git commit -m "Migración completa a FilamentPHP v3:
- Removido Shield (no compatible con v3)
- Corregido error de Collision
- Agregado bootstrap/providers.php y bootstrap/app.php para Laravel 11
- Actualizados modelos con nuevos nombres de columnas
- Creados Resources de Filament (Client, Service)
- Instalador visual de 4 pasos
- Scripts de instalación para cPanel
- Documentación completa"
```

### Paso 4: Subir al Repositorio

```bash
# Asegurar que estamos en main
git branch -M main

# Subir cambios
git push -u origin main
```

## ⚠️ Si Pide Autenticación

GitHub requiere autenticación. Opciones:

### Opción A: Personal Access Token (Recomendado)

1. Ir a: https://github.com/settings/tokens
2. Generar nuevo token (classic)
3. Permisos: `repo` (acceso completo a repositorios)
4. Copiar el token
5. Al hacer push, usar el token como contraseña:
   - Usuario: tu-usuario-de-github
   - Contraseña: [pegar-token]

### Opción B: SSH (Si tienes clave configurada)

```bash
git remote set-url origin git@github.com:DanyTrax/CRM-GS.git
git push -u origin main
```

## ✅ Verificación

Después de hacer push:

```bash
# Ver último commit
git log -1

# Ver archivos en el repositorio
git ls-files | head -20

# Verificar remote
git remote -v
```

## 📋 Archivos que se Van a Subir

- ✅ `composer.json` - Dependencias actualizadas
- ✅ `bootstrap/providers.php` - NUEVO (Laravel 11)
- ✅ `bootstrap/app.php` - NUEVO (Laravel 11)
- ✅ `app/Filament/Resources/*` - Resources de Filament
- ✅ `app/Providers/Filament/*` - Providers
- ✅ `database/migrations/*` - Migraciones
- ✅ `resources/views/installer/*` - Vistas del instalador
- ✅ `comandos-cpanel-v3.sh` - Scripts
- ✅ Todos los modelos actualizados
- ✅ Documentación completa

**Total: ~110 archivos**

## 🔄 Después de Subir

En el servidor (cPanel):

```bash
cd ~/services.dowgroupcol.com
git pull
./comandos-cpanel-v3.sh
```

---

**Listo para subir. Ejecuta `./SUBIR_CAMBIOS.sh` o sigue los pasos manuales.**
