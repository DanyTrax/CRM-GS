<x-filament-panels::page>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Botón de autorización de Zoho
                const authorizeButtonContainer = document.getElementById('zoho-authorize-button-container');
                if (authorizeButtonContainer) {
                    const configId = {{ $this->record->id ?? 'null' }};
                    const authorizeUrl = '{{ route("zoho.oauth.authorize", ["config_id" => $this->record->id ?? 0]) }}';
                    
                    if (configId && authorizeUrl) {
                        authorizeButtonContainer.innerHTML = `
                            <a href="${authorizeUrl}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        `;
                    }
                }
            });
            
            // Escuchar evento de montaje del botón
            window.addEventListener('zoho-auth-button-mounted', function(event) {
                const authorizeButtonContainer = document.getElementById('zoho-authorize-button-container');
                if (authorizeButtonContainer && event.detail) {
                    const { configId, authorizeUrl } = event.detail;
                    if (configId && authorizeUrl) {
                        authorizeButtonContainer.innerHTML = `
                            <a href="${authorizeUrl}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        `;
                    }
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
