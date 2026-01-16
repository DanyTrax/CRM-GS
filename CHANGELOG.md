# Changelog

## [1.0.0] - 2026-01-16

### Agregado
- Sistema completo de CRM/ERP/Billing
- Sistema de roles y permisos dinámicos (ACL)
- Módulo de clientes con soporte para Persona Natural/Jurídica
- Modo silencioso para migración de datos
- Módulo de servicios con lógica de renovación anti-fraude
- Sistema de facturación multimoneda (COP/USD)
- Integración con pasarela de pagos Bold
- Sistema de comunicaciones con interceptor de correos
- Área de cliente con portal web
- Sistema de tickets de soporte
- Sistema de backups automáticos
- Wizard de instalación
- Panel de salud y monitoreo
- 2FA con Google Authenticator
- Impersonation de usuarios
- Tareas programadas (cron jobs)
- Sistema de colas para correos

### Características Técnicas
- Laravel 11
- PHP 8.2+
- MySQL 8.0 / MariaDB
- Tailwind CSS (responsive)
- Soft Deletes en todas las tablas críticas
- Sistema de colas (database/redis)
- Integración SMTP (Zoho Mail)
