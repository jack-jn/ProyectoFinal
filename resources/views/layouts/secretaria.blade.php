<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DUNAMYS | Gestión de Pagos' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/dunamis.css') }}">
    <style>
        .app > .main { margin-left:0; }
        .app > .sidebar { inset:auto; top:0; }
        @media (width < 1024px) { .app > .sidebar { left:0; } }
        .sidebar-close-btn {
            display: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, .18);
            background: #292526;
            color: #fff;
            font-size: 15px;
            place-items: center;
        }
        .sidebar-close-btn:hover {
            background: var(--red);
            border-color: var(--red);
        }
        .sec-filters-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
        }
        .sec-filters-bar .search {
            flex: 1 1 250px;
            width: auto;
            min-width: 200px;
        }
        .sec-filters-bar .status-select {
            flex: 0 1 180px;
            width: auto;
            min-width: 145px;
        }
        .sec-date-range {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 0 1 auto;
        }
        .sec-date-range .control {
            width: 138px;
        }
        .sec-date-range span {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
        }
        .dialog {
            max-width: 95vw;
            max-height: 90vh;
            overflow-y: auto;
        }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (width < 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .3s ease;
                z-index: 200;
            }
            body.nav-open .sidebar {
                transform: translateX(0);
            }
            .sidebar-close-btn {
                display: inline-grid;
            }
            .scrim {
                position: fixed;
                inset: 0;
                z-index: 150;
                background: rgba(0, 0, 0, .52);
                backdrop-filter: blur(2px);
            }
            body.nav-open .scrim {
                display: block;
            }
            .main {
                margin-left: 0;
                padding: 0 20px 24px;
            }
            .mobile-menu {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--border);
                font-size: 17px;
            }
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .stats {
                grid-template-columns: 1fr;
            }
            .sec-filters-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .sec-filters-bar .search,
            .sec-filters-bar .status-select {
                width: 100%;
                flex: 1 1 auto;
            }
            .sec-date-range {
                width: 100%;
            }
            .sec-date-range .control {
                flex: 1;
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/panel-dunamys.css') }}">
    @stack('styles')
    @livewireStyles
</head>
<body>
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
      <symbol id="i-menu" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
      <symbol id="i-x" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
      <symbol id="i-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></symbol>
      <symbol id="i-file-text" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></symbol>
      <symbol id="i-book" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></symbol>
      <symbol id="i-right" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></symbol>
    </svg>
    {{ $slot }}

    <x-toast-dunamys />
    <script>document.addEventListener('keydown',function(e){if(e.key==='Escape')document.body.classList.remove('nav-open');});</script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
