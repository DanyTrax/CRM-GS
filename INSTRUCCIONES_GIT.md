# Instrucciones para Conectar con GitHub

## Paso 1: Inicializar Git (si no está inicializado)

```bash
cd "/Users/soporte/Desktop/repos/Gestor de Servicios"
git init
```

## Paso 2: Agregar el Repositorio Remoto

```bash
git remote add origin https://github.com/DanyTrax/CRM-GS.git
```

## Paso 3: Agregar Archivos

```bash
git add .
```

## Paso 4: Hacer el Primer Commit

```bash
git commit -m "Initial commit: Sistema CRM-GS completo"
```

## Paso 5: Subir al Repositorio

```bash
git branch -M main
git push -u origin main
```

## Nota sobre .env

El archivo `.env` está en `.gitignore` y no se subirá al repositorio. Asegúrate de:

1. Copiar `.env.example` a `.env` en el servidor
2. Configurar las variables de entorno según el entorno de producción

## Estructura del Proyecto

El proyecto incluye:

- ✅ Migraciones completas de base de datos
- ✅ Modelos con relaciones Eloquent
- ✅ Sistema de roles y permisos (Spatie)
- ✅ Controladores para todos los módulos
- ✅ Servicios de negocio (Invoice, Payment, Email, Backup)
- ✅ Integración con Bold (webhooks)
- ✅ Wizard de instalación
- ✅ Panel de salud y monitoreo
- ✅ Sistema de backups
- ✅ Configuración de Tailwind CSS
- ✅ Manual de despliegue

## Próximos Pasos

1. Instalar dependencias: `composer install && npm install`
2. Configurar `.env` con datos de producción
3. Ejecutar migraciones: `php artisan migrate --seed`
4. Compilar assets: `npm run build`
5. Configurar cron jobs y colas según el manual de despliegue
