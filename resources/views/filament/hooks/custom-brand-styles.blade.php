@push('styles')
<style>
    /* Estilos personalizados para el logo de la marca - Rectangular */
    .fi-topbar-logo img,
    .fi-sidebar-logo img,
    .fi-simple-logo img,
    .fi-brand img {
        width: auto !important;
        height: auto !important;
        max-width: 200px !important;
        max-height: 60px !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        padding: 0.5rem 0 !important;
        display: block !important;
    }

    /* Asegurar que el contenedor del logo no tenga restricciones circulares */
    .fi-topbar-logo,
    .fi-sidebar-logo,
    .fi-simple-logo,
    .fi-brand {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: auto !important;
        height: auto !important;
    }

    /* Login page logo - más grande */
    .fi-simple-main-ctn .fi-simple-logo img {
        max-width: 300px !important;
        max-height: 100px !important;
    }

    /* Sidebar logo */
    .fi-sidebar-logo img {
        max-width: 180px !important;
        max-height: 50px !important;
    }

    /* Topbar logo */
    .fi-topbar-logo img {
        max-width: 150px !important;
        max-height: 40px !important;
    }
</style>
@endpush
