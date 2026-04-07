<x-filament-panels::page>
    <div class="fi-section-content-ctn space-y-6">
        <div
            class="rounded-xl bg-gray-950 p-4 text-sm text-gray-100 shadow-sm ring-1 ring-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            <p class="mb-2 font-semibold text-white">Versión de código en este servidor</p>
            @if (!empty($gitInfo['error']))
                <p class="text-amber-300">{{ $gitInfo['error'] }}</p>
            @else
                <ul class="space-y-1 font-mono text-xs text-gray-300">
                    <li><span class="text-gray-500">Commit:</span> {{ $gitInfo['short'] ?? '—' }}
                        @if (!empty($gitInfo['full']))
                            <span class="text-gray-500">({{ $gitInfo['full'] }})</span>
                        @endif
                    </li>
                    <li><span class="text-gray-500">Fecha del commit (ISO):</span> {{ $gitInfo['date'] ?? '—' }}</li>
                    <li><span class="text-gray-500">Rama:</span> {{ $gitInfo['branch'] ?? '—' }}</li>
                </ul>
            @endif
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Ejecuta <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-800">git pull</code> para
            actualizar el código desde el repositorio remoto. Los botones están arriba a la derecha
            (acciones del encabezado).
        </p>

        <div
            class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
            <strong>Advertencia:</strong> estas acciones modifican el código y la caché del servidor. Asegúrate de
            tener copia de seguridad y de que el usuario del servidor web tenga permisos sobre el repositorio Git.
        </div>

        <div>
            <h3 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">Comandos Laravel</h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $btn =
                        'rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-sm outline-none transition hover:opacity-90 focus-visible:ring-2 focus-visible:ring-primary-500 disabled:pointer-events-none disabled:opacity-50';
                @endphp
                <button type="button" wire:click="artisanViewClear" wire:loading.attr="disabled" class="{{ $btn }} bg-slate-600">
                    view:clear
                </button>
                <button type="button" wire:click="artisanCacheClear" wire:loading.attr="disabled" class="{{ $btn }} bg-sky-600">
                    cache:clear
                </button>
                <button type="button" wire:click="artisanViewAndCacheClear" wire:loading.attr="disabled" class="{{ $btn }} bg-fuchsia-600">
                    view:clear + cache:clear
                </button>
                <button type="button" wire:click="artisanConfigClear" wire:loading.attr="disabled" class="{{ $btn }} bg-amber-500">
                    config:clear
                </button>
                <button type="button" wire:click="artisanRouteClear" wire:loading.attr="disabled" class="{{ $btn }} bg-emerald-600">
                    route:clear
                </button>
                <button type="button" wire:click="artisanOptimizeClear" wire:confirm="¿Limpiar toda la caché optimizada (optimize:clear)?" wire:loading.attr="disabled" class="{{ $btn }} bg-red-600">
                    optimize:clear
                </button>
                <button type="button" wire:click="artisanMigrateForce" wire:confirm="¿Ejecutar migraciones en producción (migrate --force)? Verifica un respaldo de la base de datos." wire:loading.attr="disabled" class="{{ $btn }} bg-teal-600">
                    migrate --force
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
