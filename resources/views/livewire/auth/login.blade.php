<div class="est-login-layout">
  <!-- Hero / Marca Izquierda -->
  <aside class="est-login-hero">
    <a href="{{ route('home') }}" class="est-brand-badge" style="text-decoration:none;">
      <img class="est-brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
      <span class="est-brand-name">Dunamys</span>
    </a>

    <div class="est-hero-content">
      <div class="est-hero-tag">Academia de Música</div>
      <h2 class="est-hero-title">La música <span>transforma vidas</span></h2>
      <p class="est-hero-desc">Bienvenido al aula virtual. Desarrolla tu talento con clases de teclado, guitarra, bajo y más con el acompañamiento de nuestros docentes.</p>
    </div>

    <div class="est-hero-footer">
      <strong>Academia Dunamys</strong> · Plataforma Educativa 2026
    </div>
    <div class="est-login-hero__glow"></div>
  </aside>

  <!-- Formulario Derecha -->
  <main class="est-login-panel">
    <div class="est-login-box">
      <div style="margin-bottom:20px;">
        <a href="{{ route('home') }}" class="est-back-link">
          <svg class="i"><use href="#i-left"/></svg>
          <span>Volver a la página principal</span>
        </a>
      </div>

      <div class="est-login-header">
        <h1>Iniciar <span>Sesión</span></h1>
        <p>Ingresa tus credenciales para acceder a tu aula virtual</p>
      </div>

      @if ($errorGeneral)
        <div id="estGeneralAlert" class="est-alert {{ $esAdvertencia ? 'est-alert--warning' : 'est-alert--danger' }}" role="alert" aria-live="polite">
          <svg class="i"><use href="#i-alert"/></svg>
          <span id="estGeneralAlertText">{{ $errorGeneral }}</span>
        </div>
      @endif

      @if (session('status'))
        <div class="est-alert est-alert--success" role="status" aria-live="polite">
          <svg class="i"><use href="#i-check"/></svg>
          <span>{{ session('status') }}</span>
        </div>
      @endif

      <form id="estLoginForm" wire:submit="ingresar" novalidate>
        <div class="est-form-group {{ $errors->has('correo_usuario') ? 'is-invalid' : '' }}" id="estGroupEmail">
          <label for="estEmail" class="est-label">Correo electrónico</label>
          <div class="est-input-wrap">
            <input
              type="email"
              id="estEmail"
              wire:model="correo_usuario"
              class="est-control"
              placeholder="tu.nombre@dunamys.pe"
              autocomplete="email"
              required
            >
          </div>
          <div class="est-error" id="estEmailError" role="alert">
            {{ $errors->first('correo_usuario') ?: 'Este campo es obligatorio' }}
          </div>
        </div>

        <div class="est-form-group {{ $errors->has('contrasena') ? 'is-invalid' : '' }}" id="estGroupPassword">
          <label for="estPassword" class="est-label">Contraseña</label>
          <div class="est-input-wrap">
            <input
              type="{{ $mostrarContrasena ? 'text' : 'password' }}"
              id="estPassword"
              wire:model="contrasena"
              class="est-control has-toggle"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            >
            <button
              type="button"
              class="est-toggle-pass"
              id="estTogglePass"
              wire:click="alternarVisibilidadClave"
              aria-label="{{ $mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña' }}"
              title="{{ $mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña' }}"
            >
              <svg class="i" id="estEyeIcon"><use href="#{{ $mostrarContrasena ? 'i-eye-off' : 'i-eye' }}"/></svg>
            </button>
          </div>
          <div class="est-error" id="estPasswordError" role="alert">
            {{ $errors->first('contrasena') ?: 'Este campo es obligatorio' }}
          </div>
        </div>

        <div class="est-form-meta">
          <a href="{{ route('recuperar-clave') }}" class="est-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn--primary" id="estSubmitBtn" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="ingresar">Ingresar</span>
          <span wire:loading wire:target="ingresar">Ingresando...</span>
        </button>
      </form>
    </div>
  </main>
</div>
