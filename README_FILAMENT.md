# CRM - Gestor de Servicios (FilamentPHP v3)

Sistema completo de CRM migrado a **FilamentPHP v3** siguiendo la arquitectura de [ACRegulatory](https://github.com/DanyTrax/ACRegulatory).

## 🚀 Migración a FilamentPHP

Este proyecto ha sido completamente migrado de Blade templates manuales a **FilamentPHP v3**, proporcionando:

- ✅ Panel Administrativo moderno y profesional
- ✅ Panel Cliente separado
- ✅ CRUD automático con Resources
- ✅ Sistema de roles con Filament Shield
- ✅ Formularios y tablas generados automáticamente
- ✅ Instalador visual de 4 pasos

## 📦 Stack Tecnológico

- **PHP:** 8.2+
- **Laravel:** 11.x
- **FilamentPHP:** 3.2
- **Filament Shield:** 3.0 (Roles y Permisos)
- **Spatie Settings:** 3.0 (Configuración global)
- **DomPDF:** 2.0 (Generación de PDFs)
- **Maatwebsite Excel:** 3.1 (Exportación Excel)
- **Laravel Auditing:** 15.0 (Auditoría)

## 🛠️ Instalación

### Paso 1: Clonar y Instalar Dependencias

```bash
git clone [repo-url]
cd Services.dow
composer install
```

### Paso 2: Acceder al Instalador

Abre tu navegador en: `http://tu-dominio/install`

El instalador ejecutará automáticamente:
- ✅ Configuración de base de datos
- ✅ Migraciones
- ✅ Seeders (roles y configuraciones)
- ✅ Creación de usuario administrador

## 📁 Estructura Filament

```
app/
├── Filament/
│   ├── Resources/              # Resources de Filament
│   │   ├── ClientResource.php
│   │   ├── ServiceResource.php
│   │   ├── InvoiceResource.php
│   │   └── PaymentResource.php
│   ├── Pages/                  # Páginas personalizadas
│   │   └── Dashboard.php
│   └── Widgets/                 # Widgets del dashboard
├── Providers/
│   └── Filament/
│       ├── AdminPanelProvider.php    # Panel Admin (Azul)
│       └── ClientPanelProvider.php   # Panel Cliente (Amber)
└── Models/                      # Modelos Eloquent
```

## 🎯 Paneles

### Admin Panel (`/admin`)
- **Color:** Azul (#3b82f6)
- **Path:** `/admin`
- **Recursos:** Clientes, Servicios, Facturas, Pagos
- **Roles:** Super Admin, Admin Operativo, Contador, Soporte

### Client Panel (`/portal`)
- **Color:** Amber (#f59e0b)
- **Path:** `/portal`
- **Acceso:** Solo lectura de servicios y facturas propias
- **Rol:** Cliente

## 🔐 Configuración de Roles

Después de la instalación, ejecutar:

```bash
php artisan shield:generate --all
```

Esto creará los roles y permisos automáticamente.

## 📝 Recursos Filament Creados

### ClientResource
- ✅ CRUD completo de clientes
- ✅ Campos: company_name, tax_id, email_login, email_billing
- ✅ Estados: borrador, activo, suspendido
- ✅ Filtros y búsqueda

### ServiceResource
- ✅ CRUD completo de servicios
- ✅ Tipos: único, recurrente
- ✅ Monedas: COP, USD
- ✅ Ciclos: 1, 3, 6, 12 meses
- ✅ Acción "Renovar" con lógica anti-fraude

### InvoiceResource (Pendiente)
- ✅ Generación de facturas
- ✅ Plantillas PDF (legal, cuenta_cobro)
- ✅ Conversión USD->COP con TRM

### PaymentResource (Pendiente)
- ✅ Gestión de pagos
- ✅ Integración Bold
- ✅ Aprobación manual

## 🎨 Características Filament

- **Formularios Dinámicos:** Generados automáticamente desde modelos
- **Tablas Interactivas:** Búsqueda, filtros, ordenamiento
- **Acciones Masivas:** Operaciones en lote
- **Relaciones:** Selects con búsqueda y precarga
- **Badges y Estados:** Visualización de estados con colores
- **Validación:** Reglas automáticas desde modelos

## 🔄 Diferencias con Versión Anterior

### Antes (Blade Manual)
- Controladores manuales
- Vistas Blade personalizadas
- Formularios HTML manuales
- Tablas con DataTables

### Ahora (FilamentPHP)
- Resources de Filament (CRUD automático)
- Formularios generados automáticamente
- Tablas con filtros y búsqueda integrados
- Paneles separados (Admin/Cliente)

## 📋 Próximos Pasos

1. ✅ Completar InvoiceResource y PaymentResource
2. ✅ Crear Custom Pages (Dashboard con Health Check)
3. ✅ Implementar Email Interceptor como Custom Page
4. ✅ Configurar Filament Shield completamente
5. ✅ Crear Widgets para Dashboard

## 🐛 Solución de Problemas

### Error: "Panel not found"
```bash
php artisan filament:install
```

### Error: "Shield not configured"
```bash
php artisan shield:install
php artisan shield:generate --all
```

### Limpiar caché
```bash
php artisan optimize:clear
```

## 📞 Referencias

- [FilamentPHP Docs](https://filamentphp.com/docs)
- [Filament Shield Docs](https://github.com/bezhanSalleh/filament-shield)
- [ACRegulatory Repo](https://github.com/DanyTrax/ACRegulatory)

---

**Versión:** 2.0.0 (FilamentPHP)  
**Última actualización:** Enero 2026
