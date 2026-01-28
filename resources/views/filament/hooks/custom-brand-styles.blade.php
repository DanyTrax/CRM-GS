<style>
    /* Estilos personalizados para el logo de la marca - Rectangular y Responsive */
    /* Sobrescribir estilos inline de Filament con mayor especificidad */
    
    /* Logo en Sidebar - Tamaño fijo 3rem */
    .fi-sidebar-logo,
    .fi-sidebar-logo img,
    .fi-sidebar-logo img[style*="height"] {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 1rem 1.25rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }
    
    .fi-sidebar-logo img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
    }

    /* Logo en Topbar - Tamaño fijo 3rem */
    .fi-topbar-logo,
    .fi-topbar-logo img,
    .fi-topbar-logo img[style*="height"] {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.75rem 0 !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .fi-topbar-logo img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
    }

    /* Logo en Login - Tamaño fijo 3rem */
    .fi-simple-logo,
    .fi-simple-logo img,
    .fi-simple-logo img[style*="height"],
    .fi-simple-main-ctn .fi-simple-logo,
    .fi-simple-main-ctn .fi-simple-logo img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 2rem 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .fi-simple-main-ctn .fi-simple-logo img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
    }

    /* Logo genérico - Tamaño fijo 3rem */
    .fi-brand img,
    [x-filament-brand-logo] img,
    img[alt*="logo"],
    .fi-logo img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.75rem 0 !important;
        display: block !important;
    }

    /* Contenedores de logo - Sin restricciones */
    .fi-brand,
    [x-filament-brand-logo],
    .fi-logo {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
    }

    /* Eliminar cualquier restricción circular, cuadrada o de altura fija */
    .fi-topbar-logo::before,
    .fi-sidebar-logo::before,
    .fi-simple-logo::before,
    .fi-brand::before {
        display: none !important;
    }

    /* Asegurar que los logos ocultos en modo claro/oscuro también tengan tamaño fijo 3rem */
    .fi-logo.hidden.dark\:flex img,
    .fi-logo.flex.dark\:hidden img {
        width: 3rem !important;
        height: 3rem !important;
        max-width: 3rem !important;
        max-height: 3rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
    }
</style>
