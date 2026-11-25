<div class="app">
  <!-- ================= BARRA LATERAL ================= -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
      <div class="brand">
        <img class="brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
        <span class="brand-name">Dunamys</span>
      </div>
      <button type="button" class="sidebar-toggle" id="btnToggleSidebar" onclick="document.body.classList.toggle('sidebar-collapsed')" aria-label="Minimizar o expandir barra lateral" title="Minimizar / expandir menú">
        <svg class="i"><use href="#i-menu"/></svg>
      </button>
      <button type="button" class="sidebar-close-btn" onclick="document.body.classList.remove('nav-open')" aria-label="Cerrar menú lateral">
        <svg class="i"><use href="#i-x"/></svg>
      </button>
    </div>

    <div class="user" title="Usuario: Docente / Dueño y Único Administrador">
      <div class="avatar"><svg class="i"><use href="#i-user"/></svg></div>
      <small>DUEÑO / ÚNICO ADMIN:</small>
      <strong>{{ auth()->user()?->persona?->nombre_completo ?? 'Docente Titular' }}</strong>
      <button type="button" class="nav-item" style="margin-top:8px" wire:click="editarAdministrador"
        onclick="document.body.classList.remove('nav-open')" aria-label="Editar datos del administrador" title="Editar datos del administrador">
        <svg class="i" aria-hidden="true"><use href="#i-user"/></svg><span class="nav-label">Datos del Administrador</span>
      </button>
    </div>

    <nav class="nav" aria-label="Menú principal">
      <button type="button" class="nav-item {{ $vistaActiva === 'cursos' ? 'is-active' : '' }}" id="navC" wire:click="cambiarVista('cursos')" onclick="document.body.classList.remove('nav-open')" title="Cursos">
        <svg class="i"><use href="#i-book"/></svg><span class="nav-label">Cursos</span><svg class="i chev"><use href="#i-right"/></svg>
      </button>
      <button type="button" class="nav-item {{ $vistaActiva === 'sesiones' ? 'is-active' : '' }}" id="navS" wire:click="cambiarVista('sesiones')" onclick="document.body.classList.remove('nav-open')" title="Gestión de cursos">
        <svg class="i"><use href="#i-sliders"/></svg><span class="nav-label">Gestión de cursos</span><svg class="i chev"><use href="#i-right"/></svg>
      </button>
      <button type="button" class="nav-item {{ $vistaActiva === 'alumnos' ? 'is-active' : '' }}" id="navA" wire:click="cambiarVista('alumnos')" onclick="document.body.classList.remove('nav-open')" title="Alumnos matriculados">
        <svg class="i"><use href="#i-users"/></svg><span class="nav-label">Alumnos matriculados</span><svg class="i chev"><use href="#i-right"/></svg>
      </button>
      <button type="button" class="nav-item {{ $vistaActiva === 'info' ? 'is-active' : '' }}" id="navI" wire:click="cambiarVista('info')" onclick="document.body.classList.remove('nav-open')" title="Información web institucional">
        <svg class="i"><use href="#i-globe"/></svg><span class="nav-label">Información web institucional</span><svg class="i chev"><use href="#i-right"/></svg>
      </button>
      @can('gestionar_usuarios')
        <button type="button" class="nav-item {{ $vistaActiva === 'usuarios' ? 'is-active' : '' }}" wire:click="cambiarVista('usuarios')" onclick="document.body.classList.remove('nav-open')" title="Usuarios y permisos">
          <svg class="i"><use href="#i-users"/></svg><span class="nav-label">Usuarios y permisos</span><svg class="i chev"><use href="#i-right"/></svg>
        </button>
      @endcan
      @can('atender_reclamaciones')
        @php $reclamosPendientes = \App\Models\Reclamacion::query()->where('estado', 'Pendiente')->count(); @endphp
        <a href="{{ route('docente.reclamaciones') }}" class="nav-item" onclick="document.body.classList.remove('nav-open')" title="Reclamaciones">
          <svg class="i"><use href="#i-file-text"/></svg>
          <span class="nav-label">Reclamaciones</span>
          @if ($reclamosPendientes > 0)
            <span class="nav-badge" style="margin-left:auto;">{{ $reclamosPendientes }}</span>
          @else
            <svg class="i chev"><use href="#i-right"/></svg>
          @endif
        </a>
      @endcan
    </nav>

    <div class="sidebar-foot" style="margin-bottom:10px">La música<span>transforma vidas</span></div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn--outline" style="width:100%;height:38px;font-size:13px" title="Cerrar sesión">
        <svg class="i"><use href="#i-logout"/></svg><span class="nav-label">Cerrar sesión</span>
      </button>
    </form>
  </aside>
  <div class="scrim" id="scrim" onclick="document.body.classList.remove('nav-open')"></div>

  <!-- ================= CONTENIDO PRINCIPAL ================= -->
  <main class="main">
    <div class="doc-mobile-topbar">
      <button type="button" class="btn btn--outline menu-btn" onclick="document.body.classList.toggle('nav-open')" aria-label="Abrir menú del docente">
        <svg class="i"><use href="#i-menu"/></svg> Menú
      </button>
      <strong style="font:700 16px/1 var(--font-display);color:var(--ink)">DUNAMYS · Docente</strong>
    </div>
    <x-toast-bridge :mensaje="$mensajeAdministrador" tipo="success" />

    @if ($editandoAdministrador)
      <div class="overlay is-open" wire:key="datos-administrador" wire:keydown.escape="cerrarDatosAdministrador">
        <div class="dialog dialog--sm" role="dialog" aria-modal="true" aria-labelledby="titulo-datos-administrador" x-data x-init="$refs.nombres.focus()">
          <div class="dialog__head">
            <h2 class="dialog__title" id="titulo-datos-administrador">Datos del Administrador</h2>
          </div>
          <form wire:submit="guardarAdministrador">
            <div class="dialog__body">
              @foreach (['nombres' => 'Nombres', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno'] as $campo => $etiqueta)
                <div class="field {{ $errors->has($campo) ? 'is-invalid' : '' }}" style="margin-bottom:10px" wire:key="administrador-{{ $campo }}">
                  <label for="administrador-{{ $campo }}">{{ $etiqueta }}</label>
                  <input class="control" id="administrador-{{ $campo }}" type="text" x-ref="{{ $campo }}" wire:model="{{ $campo }}" required maxlength="255">
                  @error($campo)<p class="error">{{ $message }}</p>@enderror
                </div>
              @endforeach
            </div>
            <div class="dialog__foot">
              <button class="btn btn--outline" type="button" wire:click="cerrarDatosAdministrador">Cancelar</button>
              <button class="btn btn--primary" type="submit" wire:loading.attr="disabled">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    @endif

    @if ($vistaActiva === 'cursos')
      <div id="viewC">
        <livewire:docente.cursos />
      </div>
    @elseif ($vistaActiva === 'sesiones')
      <div id="viewS">
        <livewire:docente.sesiones :id-curso-inicial="$idCursoAula" />
      </div>
    @elseif ($vistaActiva === 'alumnos')
      <div id="viewA">
        <livewire:docente.alumnos-matriculados />
      </div>
    @elseif ($vistaActiva === 'usuarios')
      @can('gestionar_usuarios')
        <livewire:docente.usuarios-permisos />
      @endcan
    @else
      <div id="viewI">
        <livewire:docente.info-web />
      </div>
    @endif
  </main>
</div>
