<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DUNAMYS Academia Musical. Aprende guitarra, piano, violín, canto y más.">
    <title>{{ $title ?? 'DUNAMYS | Academia Musical' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/estilos.css') }}">
    @if (request()->is('/') || request()->is('galeria*'))
        <link rel="stylesheet" href="{{ asset('assets/css/galeria.css') }}">
    @endif
    @if (request()->is('matricula*'))
        <link rel="stylesheet" href="{{ asset('assets/css/matricula.css') }}">
    @endif
    @if (request()->is('reclamaciones*'))
        <link rel="stylesheet" href="{{ asset('assets/css/reclamaciones.css') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .gallery-modal img {
            max-width: min(95vw, 1100px);
            max-height: 82vh;
        }
        .success-card {
            max-width: min(95vw, 440px);
            max-height: 90vh;
            overflow-y: auto;
        }
        @media (max-width: 1024px) {
            .menu-toggle {
                display: block;
            }
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                padding: 20px;
                flex-direction: column;
                align-items: stretch;
                background: rgba(5, 5, 5, .98);
                border-bottom: 2px solid var(--red);
            }
            .nav-menu.open {
                display: flex;
            }
            .nav-link,
            .nav-enroll {
                text-align: center;
                margin: 0;
            }
        }
        @media (max-width: 640px) {
            .enroll-course-grid {
                grid-template-columns: 1fr;
            }
            .steps-container {
                overflow-x: auto;
                gap: 10px;
                padding-bottom: 6px;
            }
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>
<body @class([
    'gallery-page' => request()->is('galeria*'),
    'enrollment-page' => request()->is('matricula*'),
    'claims-page' => request()->is('reclamaciones*'),
    $bodyClass ?? '' => !empty($bodyClass ?? ''),
])>
    {{ $slot }}

    <x-toast-dunamys />
    <script src="{{ asset('assets/js/script.js') }}"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
