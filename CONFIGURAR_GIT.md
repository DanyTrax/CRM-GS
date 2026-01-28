# 🔧 Configurar Git y Subir al Repositorio

## 📍 Repositorio: https://github.com/DanyTrax/CRM-GS

## 🚀 Pasos para Configurar y Subir

### Paso 1: Inicializar Git (si no está inicializado)

```bash
cd /Users/soporte/Desktop/repos/Services.dow

# Inicializar repositorio
git init

# Configurar usuario (si no está configurado)
git config user.name "Tu Nombre"
git config user.email "tu-email@example.com"
```

### Paso 2: Conectar con el Repositorio Remoto

```bash
# Agregar remote
git remote add origin https://github.com/DanyTrax/CRM-GS.git

# Verificar que se agregó correctamente
git remote -v
```

### Paso 3: Agregar Todos los Archivos

```bash
# Ver qué archivos se van a agregar
git status

# Agregar todos los archivos (excepto los de .gitignore)
git add .

# Verificar qué se agregó
git status
```

### Paso 4: Primer Commit

```bash
git commit -m "Migración completa a FilamentPHP v3:
- Removido Shield (no compatible con v3)
- Corregido error de Collision
- Agregado bootstrap/providers.php y bootstrap/app.php para Laravel 11
- Actualizados modelos con nuevos nombres de columnas
- Creados Resources de Filament (Client, Service)
- Instalador visual de 4 pasos
- Scripts de instalación para cPanel"
```

### Paso 5: Subir al Repositorio

```bash
# Si es la primera vez, crear rama main
git branch -M main

# Subir código
git push -u origin main
```

## 🔄 Si el Repositorio Ya Tiene Contenido

Si el repositorio remoto ya tiene código, primero debes hacer pull:

```bash
# Traer código existente
git pull origin main --allow-unrelated-histories

# Resolver conflictos si los hay, luego:
git add .
git commit -m "Merge con repositorio remoto"
git push origin main
```

## ✅ Verificación

Después de hacer push:

```bash
# Ver último commit
git log -1

# Ver archivos subidos
git ls-files | head -20
```

## 📋 Comandos Rápidos (Copia y Pega)

```bash
cd /Users/soporte/Desktop/repos/Services.dow && \
git init && \
git remote add origin https://github.com/DanyTrax/CRM-GS.git && \
git add . && \
git commit -m "Migración completa a FilamentPHP v3" && \
git branch -M main && \
git push -u origin main
```

---

**Nota:** Si te pide autenticación, usa un Personal Access Token de GitHub.
