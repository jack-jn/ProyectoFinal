<div class="app">
  <!-- ================= BARRA LATERAL ================= -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
      <div class="brand">
        <img class="brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
        <span class="brand-name">Dunamys</span>
      </div>
      <button type="button" class="sidebar-toggle" onclick="document.body.classList.toggle('sidebar-collapsed')" aria-label="Minimizar o expandir barra lateral" title="Minimizar / expandir menú">
        <svg class="i"><use href="#i-menu"/></svg>
      </button>
      <button type="button" class="sidebar-close-btn" onclick="document.body.classList.remove('nav-open')" aria-label="Cerrar menú lateral">
        <svg class="i"><use href="#i-x"/></svg>
      </button>
    </div>

    <div class="user">
      <div class="avatar">{{ $esDocente ? 'D' : 'S' }}</div>
      <small>{{ $esDocente ? 'DUEÑO / ÚNICO ADMIN:' : 'SECRETARÍA' }}</small>
      <strong>{{ auth()->user()?->persona?->nombre_completo ?? ($esDocente ? 'Docente Titular' : 'Secretaría') }}</strong>
    </div>

    <nav class="nav" aria-label="Menú principal">
      @if ($esDocente)
        <a href="{{ route('docente.panel') }}" class="nav-item" title="Panel Docente">
          <svg class="i"><use href="#i-book"/></svg><span class="nav-label">Panel Docente</span><svg class="i chev"><use href="#i-right"/></svg>
        </a>
      @else
        <a href="{{ route('secretaria.panel') }}" class="nav-item" title="Panel Secretaría">
          <span>▦</span><span class="nav-label">Panel Secretaría</span>
        </a>
      @endif

      @can('atender_reclamaciones')
        <a href="{{ route($esDocente ? 'docente.reclamaciones' : 'secretaria.reclamaciones') }}" class="nav-item is-active" title="Reclamaciones">
          <svg class="i"><use href="#i-file-text"/></svg>
          <span class="nav-label">Reclamaciones</span>
          @if ($pendientesCount > 0)
            <span class="nav-badge">{{ $pendientesCount }}</span>
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
      <button type="button" class="btn btn--outline menu-btn" onclick="document.body.classList.toggle('nav-open')" aria-label="Abrir menú lateral">
        <svg class="i"><use href="#i-menu"/></svg> Menú
      </button>
      <strong style="font:700 16px/1 var(--font-display);color:var(--ink)">DUNAMYS · {{ $esDocente ? 'Docente' : 'Secretaría' }}</strong>
    </div>
    <header class="page-head" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div>
          <h1 style="margin:0;font:700 26px/1.2 var(--font-display);">Libro de <span style="color:var(--red);">Reclamaciones</span></h1>
          <p style="margin:4px 0 0;color:var(--muted);font-size:14px;">Historial y atención de reclamos y quejas registrados en la web pública.</p>
        </div>
      </div>
    </header>

    <!-- Contadores rápidos -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
      <div class="card" style="padding:16px 18px;border:1px solid var(--line);border-radius:14px;background:#fff;">
        <small style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;">Total</small>
        <strong style="font:700 24px/1.2 var(--font-display);color:var(--ink);">{{ $totalReclamaciones }}</strong>
      </div>
      <div class="card" style="padding:16px 18px;border:1px solid var(--gold-border,#F7D794);border-radius:14px;background:var(--gold-soft,#FEF3DC);">
        <small style="display:block;font-size:12px;font-weight:700;color:var(--gold-dark,#855400);text-transform:uppercase;">Pendientes</small>
        <strong style="font:700 24px/1.2 var(--font-display);color:var(--gold-dark,#855400);">{{ $pendientesCount }}</strong>
      </div>
      <div class="card" style="padding:16px 18px;border:1px solid var(--green-border,#B4E6CE);border-radius:14px;background:var(--green-soft,#E3F6ED);">
        <small style="display:block;font-size:12px;font-weight:700;color:var(--green-dark,#0F683F);text-transform:uppercase;">Atendidos</small>
        <strong style="font:700 24px/1.2 var(--font-display);color:var(--green-dark,#0F683F);">{{ $atendidosCount }}</strong>
      </div>
    </div>

    <!-- Barra de filtros -->
    <div class="card" style="padding:16px 18px;border:1px solid var(--line);border-radius:14px;background:#fff;margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
      <input
        type="search"
        class="control"
        wire:model.live.debounce.300ms="busqueda"
        placeholder="Buscar por código, DNI o nombre..."
        style="flex:1 1 240px;"
      >
      <select class="control" wire:model.live="filtroEstado" style="width:auto;min-width:160px;">
        <option value="todos">Estado: Todos</option>
        <option value="Pendiente">Pendiente</option>
        <option value="Atendido">Atendido</option>
      </select>
      <select class="control" wire:model.live="filtroTipo" style="width:auto;min-width:160px;">
        <option value="todos">Tipo: Todos</option>
        <option value="Reclamo">Reclamo</option>
        <option value="Queja">Queja</option>
      </select>
      <button type="button" class="btn btn--outline btn--sm" wire:click="limpiarFiltros">Limpiar</button>
    </div>

    <!-- Tabla de historial -->
    <div class="card" style="border:1px solid var(--line);border-radius:16px;background:#fff;overflow:hidden;">
      <div class="table-wrap">
        <table class="responsive-table" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:var(--surface);border-bottom:1px solid var(--line);text-align:left;font-size:12px;text-transform:uppercase;color:var(--muted);">
              <th style="padding:12px 14px;">Código</th>
              <th style="padding:12px 14px;">Fecha</th>
              <th style="padding:12px 14px;">Tipo</th>
              <th style="padding:12px 14px;">Reclamante</th>
              <th style="padding:12px 14px;">Servicio</th>
              <th style="padding:12px 14px;">Estado</th>
              <th style="padding:12px 14px;text-align:right;">Acción</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($reclamaciones as $rec)
              <tr wire:key="rec-row-{{ $rec->id_reclamacion }}" style="border-bottom:1px solid var(--line);">
                <td data-label="Código" style="padding:12px 14px;font-weight:700;color:var(--red);">{{ $rec->codigo }}</td>
                <td data-label="Fecha" style="padding:12px 14px;">{{ $rec->created_at?->format('d/m/Y H:i') }}</td>
                <td data-label="Tipo" style="padding:12px 14px;">
                  <span class="pill {{ $rec->tipo === 'Reclamo' ? 'red' : 'gold' }}">{{ $rec->tipo }}</span>
                </td>
                <td data-label="Reclamante" style="padding:12px 14px;">
                  <strong>{{ $rec->nombres }} {{ $rec->apellidos }}</strong>
                  <div style="font-size:12px;color:var(--muted);">DNI: {{ $rec->dni }}</div>
                </td>
                <td data-label="Servicio" style="padding:12px 14px;">{{ $rec->servicio }}</td>
                <td data-label="Estado" style="padding:12px 14px;">
                  <span class="pill {{ $rec->estado === 'Atendido' ? 'green' : 'gold' }}">{{ $rec->estado }}</span>
                </td>
                <td data-label="Acción" style="padding:12px 14px;text-align:right;">
                  <button type="button" class="btn btn--outline btn--sm" wire:click="abrirDetalle({{ $rec->id_reclamacion }})">
                    Ver / Atender
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" style="padding:28px;text-align:center;color:var(--muted);">
                  No se encontraron reclamaciones con los filtros seleccionados.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($reclamaciones->hasPages())
        <div style="padding:14px 18px;border-top:1px solid var(--line);">
          {{ $reclamaciones->links() }}
        </div>
      @endif
    </div>
  </main>

  <!-- ================= MODAL DE DETALLE Y ATENCIÓN ================= -->
  @if ($modalDetalleAbierto && $reclamacionDetalle)
    <div class="overlay is-open" id="reclamoModalOverlay" wire:click.self="cerrarDetalle" wire:keydown.escape="cerrarDetalle">
      <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="tituloReclamoModal">
        <div class="dialog__head">
          <h2 class="dialog__title" id="tituloReclamoModal">
            {{ $reclamacionDetalle->tipo }} {{ $reclamacionDetalle->codigo }}
          </h2>
          <span class="pill {{ $reclamacionDetalle->estado === 'Atendido' ? 'green' : 'gold' }}" style="margin-left:8px;">
            {{ $reclamacionDetalle->estado }}
          </span>
          <button type="button" class="dialog__close" wire:click="cerrarDetalle" aria-label="Cerrar">✕</button>
        </div>

        <form wire:submit="marcarComoAtendido">
          <div class="dialog__body" style="display:flex;flex-direction:column;gap:14px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px;">
              <div><small style="color:var(--muted);display:block;">Reclamante</small><strong>{{ $reclamacionDetalle->nombres }} {{ $reclamacionDetalle->apellidos }}</strong></div>
              <div><small style="color:var(--muted);display:block;">DNI</small><strong>{{ $reclamacionDetalle->dni }}</strong></div>
              <div><small style="color:var(--muted);display:block;">Teléfono</small><strong>{{ $reclamacionDetalle->telefono }}</strong></div>
              <div><small style="color:var(--muted);display:block;">Correo electrónico</small><strong>{{ $reclamacionDetalle->correo }}</strong></div>
              <div style="grid-column:1/-1;"><small style="color:var(--muted);display:block;">Domicilio</small><strong>{{ $reclamacionDetalle->domicilio }}</strong></div>
              <div><small style="color:var(--muted);display:block;">Servicio</small><strong>{{ $reclamacionDetalle->servicio }}</strong></div>
              <div><small style="color:var(--muted);display:block;">Monto involucrado</small><strong>{{ $reclamacionDetalle->monto_involucrado !== null ? 'S/ '.number_format((float) $reclamacionDetalle->monto_involucrado, 2) : 'No especificado' }}</strong></div>
              <div><small style="color:var(--muted);display:block;">Fecha incidente</small><strong>{{ $reclamacionDetalle->fecha_incidente?->format('d/m/Y') }}</strong></div>
            </div>

            <div>
              <strong style="display:block;font-size:13px;margin-bottom:4px;">Descripción del reclamo o queja</strong>
              <p style="margin:0;padding:10px 12px;background:var(--surface);border:1px solid var(--line);border-radius:10px;font-size:14px;">{{ $reclamacionDetalle->descripcion }}</p>
            </div>

            <div>
              <strong style="display:block;font-size:13px;margin-bottom:4px;">Pedido del consumidor</strong>
              <p style="margin:0;padding:10px 12px;background:var(--surface);border:1px solid var(--line);border-radius:10px;font-size:14px;">{{ $reclamacionDetalle->pedido_consumidor }}</p>
            </div>

            @if ($reclamacionDetalle->estado === 'Atendido' && $reclamacionDetalle->atendidoPor)
              <div style="font-size:12.5px;color:var(--green-dark);background:var(--green-soft);border:1px solid var(--green-border);border-radius:10px;padding:10px 12px;">
                Atendido por <strong>{{ $reclamacionDetalle->atendidoPor->persona?->nombre_completo ?? $reclamacionDetalle->atendidoPor->correo_usuario }}</strong>
                el {{ $reclamacionDetalle->fecha_atencion?->format('d/m/Y H:i') }}.
              </div>
            @endif

            <div class="field {{ $errors->has('respuesta') ? 'is-invalid' : '' }}">
              <label for="respuestaReclamacion" style="display:block;font-weight:600;margin-bottom:6px;">Respuesta / Detalle de atención *</label>
              <textarea
                id="respuestaReclamacion"
                class="control"
                wire:model="respuesta"
                rows="4"
                placeholder="Registra la resolución o respuesta brindada al reclamante..."
                required
              ></textarea>
              @error('respuesta')<p class="error">{{ $message }}</p>@enderror
            </div>

            <label style="display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer;">
              <input type="checkbox" wire:model="enviarCorreo">
              <span>Enviar respuesta al correo del reclamante ({{ $reclamacionDetalle->correo }})</span>
            </label>
          </div>

          <div class="dialog__foot">
            <button type="button" class="btn btn--outline" wire:click="cerrarDetalle">Cerrar</button>
            <button type="submit" class="btn btn--primary" wire:loading.attr="disabled">
              Marcar como atendido
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />
</div>
