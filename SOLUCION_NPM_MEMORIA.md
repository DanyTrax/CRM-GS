# Solución: Error de Memoria en npm run build

## Problema
```
RangeError: WebAssembly.instantiate(): Out of memory: Cannot allocate Wasm memory for new instance
```

Node.js/Vite se quedó sin memoria al compilar los assets.

---

## Solución 1: Aumentar Memoria de Node.js

```bash
cd ~/services.dowgroupcol.com

# Aumentar memoria de Node.js a 4GB
NODE_OPTIONS="--max-old-space-size=4096" npm run build
```

**Si no funciona, probar con menos memoria:**

```bash
# Con 2GB
NODE_OPTIONS="--max-old-space-size=2048" npm run build

# Con 1GB
NODE_OPTIONS="--max-old-space-size=1024" npm run build
```

---

## Solución 2: Compilar en Modo Producción Simplificado

```bash
cd ~/services.dowgroupcol.com

# Usar modo producción con menos optimizaciones
NODE_ENV=production NODE_OPTIONS="--max-old-space-size=2048" npm run build
```

---

## Solución 3: Compilar Localmente y Subir

Si el servidor no tiene suficiente memoria, compila en tu computadora local:

**En tu computadora local:**

```bash
cd /ruta/a/tu/proyecto

# Compilar assets
npm run build

# Subir solo la carpeta dist/compilada al servidor
# (usar FTP, cPanel File Manager, o git)
```

**En el servidor:**

```bash
cd ~/services.dowgroupcol.com
git pull origin main
# Los assets ya estarán compilados
```

---

## Solución 4: Usar Modo Desarrollo (Temporal)

Para desarrollo, puedes usar el servidor de desarrollo de Vite sin compilar:

```bash
cd ~/services.dowgroupcol.com

# En lugar de npm run build, usar:
npm run dev
```

**Nota:** Esto requiere mantener el proceso corriendo. No es ideal para producción.

---

## Solución 5: Limpiar y Reintentar

```bash
cd ~/services.dowgroupcol.com

# Limpiar node_modules y reinstalar
rm -rf node_modules package-lock.json
npm install

# Intentar compilar con más memoria
NODE_OPTIONS="--max-old-space-size=4096" npm run build
```

---

## Solución 6: Compilar Solo CSS (Sin JavaScript)

Si solo necesitas Tailwind CSS compilado:

```bash
cd ~/services.dowgroupcol.com

# Compilar solo CSS con Tailwind CLI
npx tailwindcss -i ./resources/css/app.css -o ./public/build/assets/app.css --minify
```

---

## Solución 7: Crear Script Personalizado

Editar `package.json` y agregar:

```json
"scripts": {
    "build": "NODE_OPTIONS='--max-old-space-size=2048' vite build",
    "build:lowmem": "NODE_OPTIONS='--max-old-space-size=1024' vite build"
}
```

Luego ejecutar:
```bash
npm run build:lowmem
```

---

## Recomendación para Producción

**La mejor solución es:**

1. **Compilar localmente en tu PC:**
   ```bash
   npm run build
   git add public/build
   git commit -m "Compilar assets"
   git push
   ```

2. **En el servidor, solo hacer pull:**
   ```bash
   git pull origin main
   ```

Esto evita problemas de memoria en el servidor compartido.

---

## Si No Puedes Compilar

**Opción temporal:** Usar Tailwind CDN directamente en las vistas (solo para desarrollo/pruebas):

En `resources/views/layouts/app.blade.php`, agregar:

```html
<script src="https://cdn.tailwindcss.com"></script>
```

**Nota:** Esto no es ideal para producción, pero funciona para pruebas rápidas.

---

## Verificar Memoria Disponible

```bash
# Ver cuánta memoria tiene el servidor
free -h

# Ver límites de Node.js
node -e "console.log(require('v8').getHeapStatistics())"
```
