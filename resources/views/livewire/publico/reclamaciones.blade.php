<div>
<header class="navbar">
    <a href="{{ route('home') }}" class="logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS Academia Musical" style="height:48px;width:auto;object-fit:contain;">
    </a>

    <nav class="nav-menu" id="claimsNav">
        <a href="{{ route('home') }}#inicio" class="nav-link">Inicio</a>
        <a href="{{ route('home') }}#cursos" class="nav-link">Cursos</a>
        <a href="{{ route('home') }}#nosotros" class="nav-link">Nosotros</a>
        <a href="{{ route('home') }}#eventos" class="nav-link">Eventos</a>
        <a href="{{ route('publico.galeria') }}" class="nav-link">Galería</a>
        <a href="{{ route('home') }}#contacto" class="nav-link">Contacto</a>
        <a href="{{ route('login') }}" class="nav-link"><i class="bi bi-person-circle"></i> Iniciar sesión</a>

        <a href="{{ route('publico.matricula') }}" class="nav-enroll">
            <i class="bi bi-pencil-square"></i> Matrículate ya
        </a>
    </nav>

    <button class="menu-toggle" id="claimsMenuToggle" aria-label="Abrir menú" onclick="document.getElementById('claimsNav').classList.toggle('open')">
        <i class="bi bi-list"></i>
    </button>
</header>

<main>
    <section class="claims-hero">
        <div class="claims-hero-content">
            <span class="eyebrow">ATENCIÓN AL CONSUMIDOR</span>
            <h1>Libro de <span>Reclamaciones</span></h1>
            <p>
                En DUNAMYS valoramos tu opinión.
                Cuéntanos tu experiencia para ayudarnos a mejorar
                continuamente nuestros servicios.
            </p>
        </div>
    </section>

    <section class="claims-container">
        <div class="claims-information">
            <div class="claims-information-icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h2>Hoja de reclamación</h2>
                <p>
                    Completa los campos solicitados con información
                    clara y veraz para registrar tu solicitud.
                </p>
            </div>
            <div class="claims-notice">
                <i class="bi bi-info-circle"></i>
                <p>
                    Los campos marcados con asterisco (*) son obligatorios.
                    Revisa tus datos antes de enviar el formulario.
                </p>
            </div>
        </div>

        <form id="claimsForm" class="claims-form" wire:submit="enviarSolicitud" novalidate>
            <div class="claims-section-title">
                <span>01</span>
                <div>
                    <h3>Datos del proveedor</h3>
                    <p>Información de la academia.</p>
                </div>
            </div>

            <div class="claims-provider">
                <div>
                    <span>Razón comercial</span>
                    <strong>DUNAMYS Academia Musical</strong>
                </div>
                <div>
                    <span>Dirección</span>
                    <strong>Jr. Ica 252, Galería San Román, 3er piso, Juliaca, Perú.</strong>
                </div>
            </div>

            <div class="claims-section-title">
                <span>02</span>
                <div>
                    <h3>Tipo de solicitud</h3>
                    <p>Selecciona la naturaleza de tu comunicación.</p>
                </div>
            </div>

            <div class="claim-type-grid">
                <label class="claim-type-card">
                    <input type="radio" name="claimType" wire:model="tipo" value="Reclamo" required>
                    <i class="bi bi-exclamation-circle"></i>
                    <strong>Reclamo</strong>
                    <p>Disconformidad relacionada con un producto o servicio adquirido.</p>
                </label>

                <label class="claim-type-card">
                    <input type="radio" name="claimType" wire:model="tipo" value="Queja" required>
                    <i class="bi bi-chat-left-text"></i>
                    <strong>Queja</strong>
                    <p>Malestar o disconformidad relacionada con la atención recibida.</p>
                </label>
            </div>
            @error('tipo')<p style="color:#CE2127;font-size:13px;margin:4px 0 12px;">{{ $message }}</p>@enderror

            <div class="claims-section-title">
                <span>03</span>
                <div>
                    <h3>Datos del consumidor</h3>
                    <p>Información de la persona que presenta la solicitud.</p>
                </div>
            </div>

            <div class="claims-grid">
                <div class="claims-field">
                    <label for="consumerName">Nombres *</label>
                    <input type="text" id="consumerName" wire:model="nombres" name="consumerName" placeholder="Ingresa tus nombres" required>
                    @error('nombres')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field">
                    <label for="consumerLastName">Apellidos *</label>
                    <input type="text" id="consumerLastName" wire:model="apellidos" name="consumerLastName" placeholder="Ingresa tus apellidos" required>
                    @error('apellidos')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field">
                    <label for="consumerDni">DNI *</label>
                    <input type="text" id="consumerDni" wire:model="dni" name="consumerDni" placeholder="8 dígitos" maxlength="8" pattern="[0-9]{8}" required>
                    @error('dni')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field">
                    <label for="consumerPhone">Teléfono *</label>
                    <input type="tel" id="consumerPhone" wire:model="telefono" name="consumerPhone" placeholder="9 dígitos" maxlength="9" pattern="[0-9]{9}" required>
                    @error('telefono')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field full-width">
                    <label for="consumerEmail">Correo electrónico *</label>
                    <input type="email" id="consumerEmail" wire:model="correo" name="consumerEmail" placeholder="correo@ejemplo.com" required>
                    @error('correo')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field full-width">
                    <label for="consumerAddress">Domicilio *</label>
                    <input type="text" id="consumerAddress" wire:model="domicilio" name="consumerAddress" placeholder="Ingresa tu dirección" required>
                    @error('domicilio')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="claims-section-title">
                <span>04</span>
                <div>
                    <h3>Información del servicio</h3>
                    <p>Identifica el servicio relacionado con tu solicitud.</p>
                </div>
            </div>

            <div class="claims-grid">
                <div class="claims-field">
                    <label for="serviceType">Servicio *</label>
                    <select id="serviceType" wire:model="servicio" name="serviceType" required>
                        <option value="">Seleccionar servicio</option>
                        <option value="Curso de guitarra">Curso de guitarra</option>
                        <option value="Curso de piano">Curso de piano</option>
                        <option value="Curso de violín">Curso de violín</option>
                        <option value="Curso de canto">Curso de canto</option>
                        <option value="Curso de bajo eléctrico">Curso de bajo eléctrico</option>
                        <option value="Curso de batería">Curso de batería</option>
                        <option value="Curso de teclado">Curso de teclado</option>
                        <option value="Matrícula">Proceso de matrícula</option>
                        <option value="Otro">Otro servicio</option>
                    </select>
                    @error('servicio')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field">
                    <label for="serviceAmount">Monto involucrado (S/)</label>
                    <input type="number" id="serviceAmount" wire:model="monto_involucrado" name="serviceAmount" min="0" step="0.01" placeholder="Ej. 80.00">
                    @error('monto_involucrado')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
                <div class="claims-field full-width">
                    <label for="incidentDate">Fecha del incidente *</label>
                    <input type="date" id="incidentDate" wire:model="fecha_incidente" name="incidentDate" max="{{ now()->toDateString() }}" required>
                    @error('fecha_incidente')<small style="color:#CE2127;">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="claims-section-title">
                <span>05</span>
                <div>
                    <h3>Detalle de la solicitud</h3>
                    <p>Describe los hechos y el resultado que solicitas.</p>
                </div>
            </div>

            <div class="claims-field full-width">
                <label for="claimDescription">Descripción del reclamo o queja *</label>
                <textarea id="claimDescription" wire:model="descripcion" name="claimDescription" rows="5" maxlength="2000" placeholder="Describe detalladamente lo ocurrido..." required></textarea>
                <small class="character-counter"><span id="descriptionCount">{{ mb_strlen($descripcion) }}</span>/2000 caracteres</small>
                @error('descripcion')<small style="color:#CE2127;">{{ $message }}</small>@enderror
            </div>

            <div class="claims-field full-width">
                <label for="claimRequest">Pedido del consumidor *</label>
                <textarea id="claimRequest" wire:model="pedido_consumidor" name="claimRequest" rows="4" maxlength="1000" placeholder="Indica qué solución esperas recibir..." required></textarea>
                <small class="character-counter"><span id="requestCount">{{ mb_strlen($pedido_consumidor) }}</span>/1000 caracteres</small>
                @error('pedido_consumidor')<small style="color:#CE2127;">{{ $message }}</small>@enderror
            </div>

            <div class="claims-section-title">
                <span>06</span>
                <div>
                    <h3>Declaración y consentimiento</h3>
                    <p>Verifica la información antes de continuar.</p>
                </div>
            </div>

            <div x-data="{ acepta: @entangle('declaracion_veracidad').live }">
                <div class="claims-consent">
                    <label for="truthDeclaration" style="cursor:pointer;">
                        <input
                            type="checkbox"
                            id="truthDeclaration"
                            name="declaracion_veracidad"
                            wire:model.live="declaracion_veracidad"
                            x-model="acepta"
                            required
                        >
                        <span>
                            Declaro que la información proporcionada es verdadera y corresponde a los hechos descritos en esta solicitud. Asimismo, he leído la información sobre el tratamiento de mis datos personales y acepto su tratamiento para la gestión de esta solicitud.
                        </span>
                    </label>
                    @error('declaracion_veracidad')
                        <small x-show="!acepta" style="color:#CE2127;font-weight:700;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="claims-actions">
                    <a href="{{ route('home') }}#contacto" class="claims-cancel">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button
                        type="submit"
                        id="claimsSubmitBtn"
                        class="claims-submit"
                        x-bind:disabled="!acepta"
                        x-bind:style="!acepta ? 'opacity:0.5;cursor:not-allowed;pointer-events:none;transform:none;box-shadow:none;' : ''"
                        @disabled(! $declaracion_veracidad)
                        style="{{ ! $declaracion_veracidad ? 'opacity:0.5;cursor:not-allowed;pointer-events:none;transform:none;box-shadow:none;' : '' }}"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="enviarSolicitud">Enviar solicitud <i class="bi bi-send-fill"></i></span>
                        <span wire:loading wire:target="enviarSolicitud">Enviando...</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <div class="claims-success {{ $solicitudRegistrada ? 'open' : '' }}" id="claimsSuccess" role="dialog" aria-modal="true" aria-labelledby="successTitle">
        <div class="success-card">
            <i class="bi bi-check-circle-fill"></i>
            <h2 id="successTitle">Solicitud registrada ({{ $codigoGenerado }})</h2>
            <p id="successDescription">La información de {{ $nombreResumen }} ha sido registrada correctamente. Tipo de solicitud: {{ $tipoResumen }}. Código: {{ $codigoGenerado }}.</p>
            <button type="button" id="closeSuccess" wire:click="cerrarConfirmacion">Entendido</button>
        </div>
    </div>
</main>

<footer class="footer">
    <a href="{{ route('home') }}" class="footer-logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS Academia Musical" style="height:64px;width:auto;object-fit:contain;margin:auto;">
    </a>
    <div class="social-links">
        <a href="https://www.facebook.com/share/1BhdSWgurk/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/academiamusicaldunamys?jplx=MXUyZHV4ZHM0aDU5Mw==" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
    </div>
    <p>© <span id="claimsYear">{{ now()->year }}</span> DUNAMYS Academia Musical. Todos los derechos reservados.</p>
</footer>
</div>
