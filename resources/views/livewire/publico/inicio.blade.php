<div>
<!-- NAVBAR -->
<header class="navbar">
    <a href="#inicio" class="logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS Academia Musical" style="height:48px;width:auto;object-fit:contain;">
    </a>

    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu" id="navMenu">
        <a href="#inicio" class="nav-link active">Inicio</a>
        <a href="#cursos" class="nav-link">Cursos</a>
        <a href="#nosotros" class="nav-link">Nosotros</a>
        <a href="#eventos" class="nav-link">Eventos</a>
        <a href="{{ route('publico.galeria') }}" class="nav-link">Galería</a>
        <a href="#contacto" class="nav-link">Contacto</a>
        <a href="{{ route('login') }}" class="nav-link"><i class="bi bi-person-circle"></i> Iniciar sesión</a>
        <a href="{{ route('publico.matricula') }}" class="nav-enroll">
            <i class="bi bi-pencil-square"></i> Matrículate ya
        </a>
    </nav>
</header>

<main>

<!-- PORTADA -->
<section class="hero" id="inicio">
    <div class="hero-overlay"></div>

    <div class="music-decoration note-one">
        <i class="bi bi-music-note-beamed"></i>
    </div>

    <div class="music-decoration note-two">
        <i class="bi bi-music-note"></i>
    </div>

    <div class="music-decoration note-three">
        <i class="bi bi-soundwave"></i>
    </div>

    <div class="hero-content">
        <span class="hero-subtitle">ACADEMIA MUSICAL</span>
        <h1>DUNAMYS</h1>

        <h2>Donde tu talento, <span>suena.</span></h2>

        <p>
            Aprende música con nosotros y descubre todo
            tu potencial musical.
        </p>

        <div class="hero-buttons">
            <a href="#cursos" class="btn-primary">
                Explorar cursos <i class="fas fa-arrow-right"></i>
            </a>

            <a href="{{ route('publico.matricula') }}" class="btn-outline">
                <i class="bi bi-mortarboard-fill"></i>
                Matrículate ahora
            </a>
        </div>

        <div class="hero-features">
            <div><i class="fas fa-award"></i> Docentes especializados</div>
            <div><i class="fas fa-laptop"></i> 100% Virtual</div>
            <div><i class="fas fa-chart-line"></i> Metodología moderna</div>
        </div>
    </div>
</section>

<!-- CURSOS -->
<section class="courses section-light" id="cursos">
    <div class="section-heading">
        <span class="section-icon"><i class="fas fa-wave-square"></i></span>
        <h2>Nuestros <span>Cursos</span></h2>
        <p>Elige el curso que más te apasione y comienza tu formación musical.</p>
    </div>

    <div class="courses-grid">
        @forelse ($cursos as $curso)
            <article class="course-card" wire:key="curso-pub-{{ $curso->id_curso }}">
                <img src="{{ asset($curso->imagen_url ?: 'assets/img/cursos/guitarra.webp') }}" alt="Curso de {{ $curso->nombre_curso }}">
                <div class="course-info">
                    <h3>{{ $curso->nombre_curso }}</h3>
                    <p>
                        {{ $curso->instrumento?->nombre_instrumento ? $curso->instrumento->nombre_instrumento . ' · ' : '' }}Nivel {{ $curso->nivel }} · 100% Virtual<br>
                        <small>Acceso 24/7 (Virtual) · {{ $curso->cupos_disponibles }} cupos libres</small>
                    </p>
                    <span class="availability">
                        {{ $curso->cupos_disponibles > 0 ? '● Disponible' : '● Sin cupos' }}
                    </span>
                    <strong>S/ {{ number_format((float) $curso->costo, 2) }}</strong>
                    <a href="#cursos" wire:click.prevent="$set('idCursoDetalle', {{ $curso->id_curso }})">Ver detalle del curso <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 36px 20px; color: #555;">
                <p style="font-size: 15px; font-weight: 600;">Por el momento no hay cursos habilitados por el docente.</p>
            </div>
        @endforelse
    </div>
</section>

<!-- NOSOTROS -->
<section class="about" id="nosotros">
    <div class="about-image">
        <img src="{{ asset('assets/img/institucional/academia.webp') }}"
             alt="Instalaciones de DUNAMYS Academia Musical">
    </div>

    <div class="about-content">
        <span class="eyebrow">CONÓCENOS</span>
        <h2>¿Quiénes <span>Somos?</span></h2>

        <p class="mf" id="mf">{{ $quienesSomos }}</p>

        <p>
            Somos una academia musical independiente,
            comprometida con el desarrollo del talento
            artístico y musical.
        </p>

        <div class="about-values">
            <div><i class="fas fa-bullseye"></i> Pasión por la música</div>
            <div><i class="fas fa-star"></i> Formación de calidad</div>
            <div><i class="fas fa-heart"></i> Comunidad artística</div>
        </div>

        <a href="#contacto" class="btn-primary">
            Conoce más <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<!-- EVENTOS Y AVISOS -->
<section class="events section-light" id="eventos">
    <div class="section-heading">
        <h2>Eventos y <span>Recitales</span></h2>
        <p>Revive nuestros mejores momentos y participa en nuestros eventos.</p>
    </div>

    @if ($avisos->isNotEmpty())
        <div id="av-list" style="max-width:980px;margin:0 auto 30px;display:grid;gap:12px;">
            @foreach ($avisos as $aviso)
                <article class="av-item" wire:key="aviso-pub-{{ $aviso->id_aviso }}" style="background:#fff;border-left:5px solid #ed102d;border-radius:12px;padding:16px 20px;box-shadow:0 6px 20px rgba(0,0,0,.06);">
                    <strong class="av-title" style="display:block;font-size:15px;color:#111;">{{ $aviso->titulo_aviso }}</strong>
                    <p class="av-text" style="margin:6px 0 0;font-size:13px;color:#555;">{{ $aviso->texto_aviso }}</p>
                    @if ($aviso->fecha_publicacion)
                        <small class="av-date" style="display:block;margin-top:6px;font-size:11px;color:#888;">{{ $aviso->fecha_publicacion->format('d/m/Y') }}</small>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    <div class="events-grid">
        <article class="event-card">
            <img src="{{ asset('assets/img/eventos/evento-01.webp') }}" alt="Concierto de estudiantes">
            <div>
                <h3>Concierto de estudiantes</h3>
                <p><i class="far fa-calendar"></i> 17 de abril</p>
            </div>
        </article>

        <article class="event-card">
            <img src="{{ asset('assets/img/eventos/evento-02.webp') }}" alt="Presentación de guitarra">
            <div>
                <h3>Presentación de guitarra</h3>
                <p><i class="far fa-calendar"></i> 14 de mayo</p>
            </div>
        </article>

        <article class="event-card">
            <img src="{{ asset('assets/img/eventos/evento-03.webp') }}" alt="Recital de piano">
            <div>
                <h3>Recital de piano</h3>
                <p><i class="far fa-calendar"></i> 30 de mayo</p>
            </div>
        </article>

        <article class="event-card">
            <img src="{{ asset('assets/img/eventos/evento-04.webp') }}" alt="Festival de música">
            <div>
                <h3>Festival de música</h3>
                <p><i class="far fa-calendar"></i> 7 de junio</p>
            </div>
        </article>
    </div>
</section>

<!-- GALERÍA -->
<section class="gallery" id="galeria">

    <div class="section-heading">
        <h2>Nuestra <span>Galería</span></h2>
        <p>Momentos, experiencias y pasión por la música.</p>
    </div>

    <div class="gallery-grid">
        @forelse ($galeria as $item)
            @php
                $urlImg = asset($item->archivo_url ?: 'assets/img/galeria/galeria-01.webp');
            @endphp
            <a href="{{ $urlImg }}"
               wire:click.prevent="abrirModal('{{ $urlImg }}', '{{ addslashes($item->titulo_evento) }}')"
               class="gallery-item"
               wire:key="gal-home-{{ $item->id_galeria }}">
                <img src="{{ $urlImg }}"
                     alt="{{ $item->titulo_evento }}">
                <i class="bi bi-arrows-fullscreen"></i>
            </a>
        @empty
            @for ($i = 1; $i <= 8; $i++)
                @php
                    $urlImgDef = asset('assets/img/galeria/galeria-0'.$i.'.webp');
                @endphp
                <a href="{{ $urlImgDef }}"
                   wire:click.prevent="abrirModal('{{ $urlImgDef }}', 'Presentación musical DUNAMYS')"
                   class="gallery-item">
                    <img src="{{ $urlImgDef }}"
                         alt="Presentación musical DUNAMYS">
                    <i class="bi bi-arrows-fullscreen"></i>
                </a>
            @endfor
        @endforelse
    </div>

    <a href="{{ route('publico.galeria') }}" class="btn-primary">
        Explorar galería
        <i class="bi bi-images"></i>
    </a>

</section>

<!-- CONTACTO -->
<section class="contact" id="contacto">
    <div class="section-heading">
        <h2>Contácta<span>nos</span></h2>
        <p>Estamos listos para acompañarte en tu camino musical.</p>
    </div>

    <div class="contact-grid">

        <a class="contact-item"
           href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccion) }}"
           target="_blank" rel="noopener noreferrer">

            <i class="fas fa-map-marker-alt"></i>
            <h3>Dirección</h3>
            <p id="i-dir">{{ $direccion }}</p>
            <span class="contact-action">Ver ubicación <i class="fas fa-arrow-right"></i></span>
        </a>

        <div class="contact-item">
            <i class="fab fa-whatsapp"></i>
            <h3>Contáctanos por WhatsApp</h3>

            <a class="phone-link"
               id="i-tel"
               href="https://wa.me/51{{ $telefonoRaw }}?text=Hola%2C%20estoy%20interesado%28a%29%20en%20los%20cursos%20de%20DUNAMYS%20y%20quisiera%20recibir%20m%C3%A1s%20informaci%C3%B3n."
               target="_blank" rel="noopener noreferrer">
                {{ $telefonoVisual }}
                <i class="fas fa-external-link-alt"></i>
            </a>

            <a class="phone-link"
               href="https://wa.me/51999739090?text=Hola%2C%20estoy%20interesado%28a%29%20en%20los%20cursos%20de%20DUNAMYS%20y%20quisiera%20recibir%20m%C3%A1s%20informaci%C3%B3n."
               target="_blank" rel="noopener noreferrer">
                999 739 090
                <i class="fas fa-external-link-alt"></i>
            </a>

            <span class="contact-action">Escríbenos directamente</span>
        </div>

        <div class="contact-item">
            <i class="far fa-clock"></i>
            <h3>Horario de atención</h3>
            <p id="i-hor">
                Lunes a viernes<br>
                {{ $horarioApertura12 }} - {{ $horarioCierre12 }}
            </p>
        </div>

    </div>

    <a href="{{ route('publico.reclamaciones') }}" class="claims-link">
        <i class="bi bi-journal-bookmark-fill"></i>
        Libro de Reclamaciones
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

<!-- FOOTER -->
<footer class="footer">
    <a href="#inicio" class="footer-logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS" style="height:64px;width:auto;object-fit:contain;margin:auto;">
    </a>

    <div class="footer-links">
        <a href="#inicio">Inicio</a>
        <a href="#cursos">Cursos</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#eventos">Eventos</a>
        <a href="#contacto">Contacto</a>
    </div>

    <div id="footer-contacto-institucional" style="display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;justify-content:center;padding:8px 0 18px;font-size:13px;color:#ccc;">
        <span>📞 Teléfono: <a id="footer-tel" href="tel:+51{{ $telefonoRaw }}" style="color:#fff;font-weight:700;text-decoration:underline;">{{ $telefonoVisual }}</a></span>
        <span id="footer-hor">🕒 Lunes a viernes: {{ $horarioApertura12 }} – {{ $horarioCierre12 }}</span>
        <span>✉️ {{ $correoContacto }}</span>
    </div>

    <div class="social-links">
        <a href="https://www.facebook.com/share/1BhdSWgurk/"
           target="_blank" rel="noopener noreferrer"
           aria-label="Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>

        <a href="https://www.instagram.com/academiamusicaldunamys?jplx=MXUyZHV4ZHM0aDU5Mw=="
           target="_blank" rel="noopener noreferrer"
           aria-label="Instagram">
            <i class="fab fa-instagram"></i>
        </a>

        <a href="https://www.tiktok.com/@academiadunamys"
           target="_blank" rel="noopener noreferrer"
           aria-label="TikTok">
            <i class="fab fa-tiktok"></i>
        </a>
    </div>

    <p>© <span id="year">{{ now()->year }}</span> DUNAMYS Academia Musical. Todos los derechos reservados.</p>
</footer>
<div wire:key="detalle-curso-contenedor">
    @if ($cursoDetalle)
        <dialog class="curso-detalle" aria-labelledby="titulo-curso-detalle" wire:key="detalle-curso-{{ $cursoDetalle->id_curso }}"
            x-data x-init="$el.showModal()"
            x-on:cancel.prevent="$wire.set('idCursoDetalle', null)"
            x-on:click.self="$wire.set('idCursoDetalle', null)">
            <div class="curso-detalle__contenido">
                <header class="curso-detalle__banner">
                    <img src="{{ asset($cursoDetalle->imagen_url ?: 'assets/img/cursos/guitarra.webp') }}" alt="">
                    <button class="curso-detalle__salir" type="button" wire:click="$set('idCursoDetalle', null)" aria-label="Cerrar detalle" autofocus>×</button>
                    <div class="curso-detalle__cabecera">
                        <p>{{ $cursoDetalle->codigo_curso }}</p>
                        <h2 id="titulo-curso-detalle">{{ $cursoDetalle->nombre_curso }}</h2>
                        <div class="curso-detalle__etiquetas">
                            <span>{{ $cursoDetalle->instrumento?->nombre_instrumento ?? 'Música' }}</span>
                            <span>{{ $cursoDetalle->nivel }}</span>
                            <span>100% Virtual</span>
                        </div>
                    </div>
                </header>

                <div class="curso-detalle__cuerpo">
                    <dl class="curso-detalle__resumen">
                        <div><dt>Costo</dt><dd>S/ {{ number_format((float) $cursoDetalle->costo, 2) }}</dd></div>
                        <div><dt>Cupos disponibles</dt>
                            <dd class="{{ $cursoDetalle->cupos_disponibles > 0 ? 'curso-detalle__cupos--libre' : 'curso-detalle__cupos--lleno' }}">
                                <i class="fas fa-circle" aria-hidden="true"></i> {{ $cursoDetalle->cupos_disponibles }}
                            </dd>
                        </div>
                        <div><dt>Modalidad</dt><dd>Virtual — Acceso 24/7</dd></div>
                    </dl>
                    <dl class="curso-detalle__datos">
                        <div><dt><i class="fas fa-user" aria-hidden="true"></i> Docente a cargo</dt>
                            <dd>{{ $cursoDetalle->docente?->persona?->nombre_completo ?? 'Por confirmar' }}</dd>
                        </div>
                        <div><dt><i class="fas fa-book" aria-hidden="true"></i> Sesiones</dt><dd>{{ $cursoDetalle->sesiones->count() }}</dd></div>
                    </dl>
                    <section aria-labelledby="temario-curso-detalle">
                        <h3 id="temario-curso-detalle">Temario del curso</h3>
                        @if ($cursoDetalle->sesiones->isEmpty())
                            <p>El temario estará disponible cuando se publiquen las sesiones.</p>
                        @else
                            <ol class="curso-detalle__temario">
                                @foreach ($cursoDetalle->sesiones as $sesion)
                                    <li wire:key="curso-detalle-sesion-{{ $sesion->id_sesion }}">
                                        <span aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                        {{ $sesion->titulo_sesion }}
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </section>
                </div>

                <footer class="curso-detalle__pie">
                    <button type="button" wire:click="$set('idCursoDetalle', null)">Cerrar</button>
                    <a href="{{ route('publico.matricula', ['curso' => $cursoDetalle->id_curso]) }}">Matricularme en este curso</a>
                </footer>
            </div>
        </dialog>
    @endif
</div>
</div>
