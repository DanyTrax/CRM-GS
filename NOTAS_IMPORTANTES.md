# Notas Importantes - CRM-GS

## ⚠️ Antes de Ejecutar Migraciones

**IMPORTANTE**: Las migraciones de Spatie Permission deben publicarse ANTES de ejecutar `php artisan migrate`.

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Esto creará las migraciones necesarias para las tablas de roles y permisos.

## Orden Correcto de Instalación

1. `composer install`
2. `npm install`
3. `cp .env.example .env`
4. `php artisan key:generate`
5. **`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`** ⚠️
6. Configurar `.env` con datos de base de datos
7. `php artisan migrate --seed`
8. `npm run build`

## Estructura de Migraciones

Las migraciones están numeradas para ejecutarse en orden:
- `2024_01_01_000001` - users
- `2024_01_01_000002` - clients
- ... (resto de migraciones del sistema)
- Las migraciones de Spatie se ejecutarán automáticamente después de ser publicadas

## Configuración de Colas

El sistema usa `database` como driver de colas por defecto. Asegúrate de:

1. Ejecutar las migraciones (incluye tablas `jobs`, `failed_jobs`, `job_batches`)
2. Configurar Supervisor o Cron para procesar colas
3. Verificar en `/admin/health` que las colas funcionan

## Modo Silencioso

El checkbox "Silenciar Notificaciones" está disponible en:
- Crear/Editar Cliente
- Crear/Editar Factura
- Crear/Editar Servicio

Cuando está activo, NO se envían correos electrónicos.

## Renovación Anti-Fraude

La lógica de renovación de servicios:
- ✅ Usa `current_due_date` como base
- ❌ NO usa `payment_date`
- Esto previene que clientes paguen tarde y ganen tiempo extra

Ejemplo:
- Vence: 01/Enero
- Paga: 20/Enero
- Nueva fecha: 01/Febrero (no 20/Febrero)

## Multimoneda

Cuando una factura es en USD:
1. Sistema obtiene TRM del día (tabla `exchange_rates`)
2. Aplica spread configurado (default: 3%)
3. Convierte a COP para enviar a Bold
4. Guarda la tasa usada en `payments.exchange_rate`

## Webhook de Bold

El endpoint es: `/api/bold/webhook`

Configurar en Bold:
- URL: `https://tu-dominio.com/api/bold/webhook`
- Método: POST
- Headers: `X-Bold-Signature` (firma de seguridad)

## Backups

Los backups se crean automáticamente:
- Diario a las 2:00 AM (configurable en `routes/console.php`)
- Retención: 30 días (configurable en `.env`)
- Ubicación: `storage/app/backups/`

Para subir a nube, configurar tokens en `.env`:
- `BACKUP_DRIVE_TOKEN` (Google Drive)
- `BACKUP_ONEDRIVE_TOKEN` (OneDrive)

## Cron Jobs

Configurar en servidor:
```
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Tareas programadas:
- Verificación de facturas vencidas (diario)
- Backups automáticos (diario 2 AM)
- Log de ejecución (cada minuto)

## 2FA

Para activar 2FA:
1. Usuario debe generar código QR
2. Escanear con Google Authenticator
3. Activar en perfil

Obligatorio para Super Administrador.

## Impersonation

Solo usuarios con permiso `users.impersonate` pueden:
1. Ver opción "Ver como usuario" en lista de usuarios
2. Iniciar sesión como otro usuario
3. Se registra en `impersonation_logs`

## Soft Deletes

Todas las tablas críticas tienen `deleted_at`:
- users
- clients
- services
- invoices
- payments

Los datos NO se eliminan físicamente, solo se marcan como eliminados.

## Troubleshooting

### Error: "Class 'Spatie\Permission\Models\Role' not found"
- Ejecutar: `composer require spatie/laravel-permission`
- Publicar migraciones: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

### Error: "Table 'roles' doesn't exist"
- Las migraciones de Spatie no se han ejecutado
- Publicar y ejecutar migraciones en orden

### Colas no procesan
- Verificar que Supervisor está corriendo
- Verificar conexión a base de datos
- Revisar `storage/logs/worker.log`

### Cron no ejecuta
- Verificar sintaxis del cron job
- Verificar permisos de ejecución
- Verificar ruta completa al proyecto
