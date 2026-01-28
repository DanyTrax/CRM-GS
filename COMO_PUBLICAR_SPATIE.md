# Cómo Publicar Migraciones de Spatie Permission

## Paso 1: Ejecutar el Comando

```bash
cd ~/services.dowgroupcol.com
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**O si aparece el menú interactivo:**

```bash
php artisan vendor:publish
```

---

## Paso 2: Si Aparece el Menú Interactivo

Cuando ejecutas `php artisan vendor:publish` sin especificar el provider, aparece un menú.

**En el menú que apareció:**

1. **Verás una lista de providers**
2. **Busca y selecciona:**
   ```
   Spatie\Permission\PermissionServiceProvider
   ```

3. **Cómo seleccionarlo:**
   - **Opción 1:** Escribe `Spatie` en el campo "Search..." y te filtrará
   - **Opción 2:** Usa las flechas ↑ ↓ para navegar hasta `Spatie\Permission\PermissionServiceProvider`
   - **Opción 3:** Presiona el número que aparece al lado (si lo hay)

4. **Seleccionar:**
   - Presiona `Enter` o `Space` para seleccionar

5. **Si pregunta por tag, selecciona:**
   - `migrations` o `all` (presiona Enter)

---

## Paso 3: Verificar que Funcionó

Después de seleccionar, deberías ver:

```
Copied File [...]
Publishing complete.
```

Y deberías ver nuevos archivos en:
```bash
ls -la database/migrations/*permission*
```

---

## Comando Directo (Sin Menú)

Si prefieres evitar el menú interactivo:

```bash
cd ~/services.dowgroupcol.com

# Publicar solo migraciones de Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"

# O publicar todo de Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

---

## Si No Aparece Spatie en el Menú

**Verificar que está instalado:**

```bash
cd ~/services.dowgroupcol.com
composer show | grep spatie

# Debe mostrar:
# spatie/laravel-permission
```

**Si no aparece, reinstalar:**

```bash
composer require spatie/laravel-permission
```

---

## Resumen Rápido

**En el menú que te apareció:**

1. Escribe `Spatie` en el campo de búsqueda
2. Selecciona `Spatie\Permission\PermissionServiceProvider`
3. Presiona `Enter`
4. Si pregunta por tag, presiona `Enter` de nuevo (o escribe `migrations`)

**O ejecuta directamente:**

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
```
