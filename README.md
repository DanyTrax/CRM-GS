# CRM - Gestor de Servicios

Sistema ERP/CRM/Billing desarrollado con **Laravel 11** y **FilamentPHP v3**.

## 🚀 Instalación Rápida

### Paso 1: Clonar Repositorio

```bash
git clone https://github.com/DanyTrax/CRM-GS.git
cd CRM-GS
```

### Paso 2: Acceder al Instalador

Abre tu navegador en: `http://tu-dominio/install`

El instalador ejecutará automáticamente:
- ✅ Instalación de dependencias de Composer
- ✅ Configuración de base de datos
- ✅ Migraciones
- ✅ Seeders (roles y configuraciones)
- ✅ Creación de usuario administrador

**¡No necesitas ejecutar comandos manuales!**

## 📋 Requisitos

- **PHP:** 8.2+ (⚠️ Laravel 11 requiere PHP 8.2 mínimo)
- **MySQL:** 8.0+ o MariaDB 10.3+
- **Composer:** 2.x
- **Extensiones PHP:** intl, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, curl, zip

### ⚠️ Si tienes PHP 8.1 en el servidor

Debes actualizar PHP a 8.2 o superior:

1. En cPanel, buscar **"Select PHP Version"** o **"MultiPHP Manager"**
2. Seleccionar el dominio
3. Cambiar a **PHP 8.2** o superior
4. Guardar cambios
5. Verificar: `php -v`

## 🛠️ Instalación Manual (Si Prefieres)

```bash
# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Publicar Filament
php artisan filament:install --panels

# Publicar Spatie Permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Configurar base de datos en .env y ejecutar
php artisan migrate --seed
```

## 📦 Stack Tecnológico

- **PHP:** 8.2+
- **Laravel:** 11.x
- **FilamentPHP:** 3.2
- **Base de Datos:** MySQL
- **Frontend:** FilamentPHP (Tailwind CSS)
- **Colas:** Database Driver
- **Scheduler:** Task Scheduling

## ✨ Características

### 1. Panel Administrativo (FilamentPHP)
- ✅ CRUD completo de Clientes, Servicios, Facturas, Pagos
- ✅ Dashboard con Health Check
- ✅ Sistema de roles nativo
- ✅ Formularios y tablas generados automáticamente

### 2. Instalador Visual
- ✅ Wizard de 4 pasos (Requirements, Database, Admin, Finish)
- ✅ Instalación completamente automática
- ✅ Sin necesidad de tocar código ni SQL

### 3. Gestión de Servicios
- ✅ Tipos: Único y Recurrente
- ✅ Renovación Anti-Fraude
- ✅ Upselling (cambio de ciclo)

### 4. Facturación
- ✅ Multimoneda (USD/COP)
- ✅ Conversión automática USD→COP
- ✅ Generación de PDFs

### 5. Integración Bold
- ✅ Webhook para pagos automáticos
- ✅ Renovación automática de servicios

## 🎯 Paneles

### Admin Panel (`/admin`)
- Color: Azul (#3b82f6)
- Recursos: Clientes, Servicios, Facturas, Pagos
- Roles: Super Admin, Admin Operativo, Contador, Soporte

### Client Panel (`/portal`)
- Color: Amber (#f59e0b)
- Acceso: Solo lectura de servicios y facturas propias

## 🔐 Credenciales Iniciales

Después de la instalación, usa las credenciales que configuraste en el Paso 3 del wizard.

## 📁 Estructura del Proyecto

```
app/
├── Filament/
│   ├── Resources/          # Resources de Filament
│   └── Pages/               # Páginas personalizadas
├── Providers/
│   └── Filament/            # Providers de Filament
├── Models/                  # Modelos Eloquent
└── Services/                # Servicios de negocio

database/
├── migrations/              # Migraciones
└── seeders/                 # Seeders

resources/
└── views/
    ├── layouts/             # Layouts
    ├── partials/            # Fragmentos reutilizables
    └── installer/           # Vistas del instalador
```

## 🐛 Solución de Problemas

### Error: "CollisionServiceProvider not found"
**Solución:** No ejecutes `config:cache`, `route:cache` o `view:cache` en producción. Ver `SOLUCION_COLLISION.md`

### Error: "Shield commands not found"
**Solución:** Filament Shield no está disponible para v3. El sistema usa roles nativos. Ver `SOLUCION_SHIELD_V3.md`

### Error 500
Ver `SOLUCION_ERROR_500.md` o ejecutar:
```bash
bash setup-storage.sh
php artisan config:clear
php artisan cache:clear
```

## 📞 Documentación Adicional

- `INSTALACION_CPANEL.md` - Guía completa de instalación en cPanel
- `COMANDOS_LOCAL.md` - Comandos para trabajar en LOCAL
- `COMANDOS_SERVIDOR.md` - Comandos para ejecutar en SERVIDOR
- `FLUJO_TRABAJO.md` - Flujo completo Local → Servidor
- `CONFIGURAR_GIT.md` - Cómo configurar Git y subir cambios

## 📄 Licencia

MIT

## 👨‍💻 Autor

Desarrollado para gestión de servicios y facturación.

---

**Versión:** 2.0.0 (FilamentPHP v3)  
**Repositorio:** https://github.com/DanyTrax/CRM-GS
