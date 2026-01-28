<style>
    /* Estilos personalizados para el logo de la marca - Rectangular */
    /* Eliminar formato circular y adaptar al ancho disponible */
    .fi-topbar-logo img,
    .fi-sidebar-logo img,
    .fi-simple-logo img,
    .fi-brand img,
    [x-filament-brand-logo] img {
        width: auto !important;
        height: auto !important;
        max-width: 100% !important;
        max-height: 60px !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.5rem 0 !important;
        display: block !important;
    }

    /* Asegurar que el contenedor del logo se adapte al ancho */
    .fi-topbar-logo,
    .fi-sidebar-logo,
    .fi-simple-logo,
    .fi-brand,
    [x-filament-brand-logo] {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
    }

    /* Login page logo - más grande y adaptado */
    .fi-simple-main-ctn .fi-simple-logo,
    .fi-simple-main-ctn .fi-simple-logo img {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 100px !important;
    }

    /* Sidebar logo - adaptado al ancho del sidebar */
    .fi-sidebar-logo {
        width: 100% !important;
        padding: 0 1rem !important;
    }
    
    .fi-sidebar-logo img {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 50px !important;
    }

    /* Topbar logo - adaptado al espacio disponible */
    .fi-topbar-logo {
        max-width: 200px !important;
    }
    
    .fi-topbar-logo img {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 40px !important;
    }

    /* Eliminar cualquier restricción circular o cuadrada */
    .fi-topbar-logo::before,
    .fi-sidebar-logo::before,
    .fi-simple-logo::before {
        display: none !important;
    }
</style>
