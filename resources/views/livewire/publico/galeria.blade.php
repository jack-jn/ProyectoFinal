<div>
<header class="navbar">
    <a href="{{ route('home') }}" class="logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS Academia Musical" style="height:48px;width:auto;object-fit:contain;">
    </a>

    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu" id="navMenu">
        <a href="{{ route('home') }}#inicio" class="nav-link">Inicio</a>
        <a href="{{ route('home') }}#cursos" class="nav-link">Cursos</a>
        <a href="{{ route('home') }}#nosotros" class="nav-link">Nosotros</a>
        <a href="{{ route('home') }}#eventos" class="nav-link">Eventos</a>
        <a href="{{ route('publico.galeria') }}" class="nav-link active">Galería</a>
        <a href="{{ route('home') }}#contacto" class="nav-link">Contacto</a>
        <a href="{{ route('login') }}" class="nav-link"><i class="bi bi-person-circle"></i> Iniciar sesión</a>
        <a href="{{ route('publico.matricula') }}" class="nav-enroll">
            <i class="bi bi-pencil-square"></i> Matrículate ya
        </a>
    </nav>
</header>

<main>
    <section class="gallery-hero">
        <div class="gallery-hero-content">
            <span class="eyebrow">NUESTROS MOMENTOS</span>
            <h1>Vive la música,<br><span>captura el momento.</span></h1>
            <p>Descubre las experiencias, presentaciones y momentos que forman parte de nuestra familia musical.</p>
            <a href="#contenidoGaleria" class="btn-primary">Explorar galería <i class="bi bi-arrow-down"></i></a>
        </div>
    </section>

    <section class="gallery-content" id="contenidoGaleria">
        <div class="section-heading">
            <span class="section-icon"><i class="bi bi-images"></i></span>
            <h2>Nuestra <span>Galería</span></h2>
            <p>Cada fotografía y video cuenta una historia de aprendizaje, talento y pasión por la música.</p>
        </div>

        <div class="gallery-filters">
            <button type="button" class="gallery-filter {{ $filtro === 'all' ? 'active' : '' }}" wire:click="filtrar('all')">
                <i class="bi bi-grid"></i> Todo
            </button>
            <button type="button" class="gallery-filter {{ $filtro === 'Foto' ? 'active' : '' }}" wire:click="filtrar('Foto')">
                <i class="bi bi-camera"></i> Fotografías
            </button>
            <button type="button" class="gallery-filter {{ $filtro === 'Video' ? 'active' : '' }}" wire:click="filtrar('Video')">
                <i class="bi bi-play-circle"></i> Videos
            </button>
        </div>

        <div class="media-grid">
            @foreach ($eventos as $evento)
                @php
                    $urlArchivo = asset($evento->archivo_url ?: 'assets/img/galeria/galeria-01.webp');
                @endphp
                <article class="media-card {{ $evento->tipo_multimedia === 'Video' ? 'video-card' : '' }}" wire:key="gal-page-{{ $evento->id_galeria }}">
                    @if ($evento->tipo_multimedia === 'Video')
                        <video controls preload="metadata">
                            <source src="{{ $urlArchivo }}" type="video/mp4">
                        </video>
                    @else
                        <img
                            src="{{ $urlArchivo }}"
                            alt="{{ $evento->titulo_evento }}"
                            style="cursor:pointer;"
                            wire:click="abrirModal('{{ $urlArchivo }}', '{{ addslashes($evento->titulo_evento) }}')"
                        >
                        <button
                            type="button"
                            class="media-open"
                            wire:click="abrirModal('{{ $urlArchivo }}', '{{ addslashes($evento->titulo_evento) }}')"
                            aria-label="Ampliar fotografía"
                        >
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    @endif
                    <div class="media-caption">
                        <span>{{ $evento->tipo_multimedia }}</span>
                        <h3>{{ $evento->titulo_evento }}</h3>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="gallery-invitation">
        <i class="bi bi-music-note-beamed"></i>

        <h2>Tu talento también puede<br>
            ser parte de nuestra historia.</h2>

        <p>Comienza hoy tu formación musical con nosotros.</p>

        <a href="{{ route('publico.matricula') }}" class="btn-primary">
            Matrículate ahora
            <i class="bi bi-arrow-right"></i>
        </a>
    </section>
</main>

<!-- VISOR DE FOTOGRAFÍAS -->
<div
    class="gallery-modal {{ $imagenModalUrl ? 'open' : '' }}"
    id="galleryModal"
    role="dialog"
    aria-modal="true"
    aria-label="Fotografía ampliada"
    wire:click.self="cerrarModal"
>
    <button
        type="button"
        class="modal-close"
        id="modalClose"
        wire:click="cerrarModal"
        aria-label="Cerrar"
    >
        <i class="bi bi-x-lg"></i>
    </button>

    @if ($imagenModalUrl)
        <img src="{{ $imagenModalUrl }}" id="modalImage" alt="{{ $imagenModalTitulo }}">
        <p id="modalTitle">{{ $imagenModalTitulo }}</p>
    @endif
</div>

<footer class="footer">
    <a href="{{ route('home') }}" class="footer-logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS Academia Musical" style="height:64px;width:auto;object-fit:contain;margin:auto;">
    </a>
    <div class="social-links">
        <a href="https://www.facebook.com/share/1BhdSWgurk/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/academiamusicaldunamys?jplx=MXUyZHV4ZHM0aDU5Mw==" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
    </div>
    <p>© <span>{{ now()->year }}</span> DUNAMYS Academia Musical. Todos los derechos reservados.</p>
</footer>
</div>
