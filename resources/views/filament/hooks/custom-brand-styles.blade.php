<style>
    /* Estilos personalizados para el logo de la marca - Rectangular y Responsive */
    /* Sobrescribir estilos inline de Filament con mayor especificidad */
    
    /* Logo en Sidebar - Responsive completo */
    .fi-sidebar-logo,
    .fi-sidebar-logo img,
    .fi-sidebar-logo img[style*="height"] {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        min-height: auto !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.75rem 1rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }
    
    .fi-sidebar-logo img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: 60px !important;
    }

    /* Logo en Topbar - Responsive */
    .fi-topbar-logo,
    .fi-topbar-logo img,
    .fi-topbar-logo img[style*="height"] {
        width: auto !important;
        max-width: 250px !important;
        height: auto !important;
        max-height: none !important;
        min-height: auto !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.5rem 0 !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .fi-topbar-logo img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: 50px !important;
    }

    /* Logo en Login - Responsive completo */
    .fi-simple-logo,
    .fi-simple-logo img,
    .fi-simple-logo img[style*="height"],
    .fi-simple-main-ctn .fi-simple-logo,
    .fi-simple-main-ctn .fi-simple-logo img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        min-height: auto !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 1rem 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .fi-simple-main-ctn .fi-simple-logo img {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 120px !important;
    }

    /* Logo genérico - Responsive */
    .fi-brand img,
    [x-filament-brand-logo] img,
    img[alt*="logo"],
    .fi-logo img {
        width: auto !important;
        height: auto !important;
        max-width: 100% !important;
        max-height: 60px !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.5rem 0 !important;
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

    /* Asegurar que los logos ocultos en modo claro/oscuro también sean responsive */
    .fi-logo.hidden.dark\:flex img,
    .fi-logo.flex.dark\:hidden img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: 60px !important;
    }
</style>
