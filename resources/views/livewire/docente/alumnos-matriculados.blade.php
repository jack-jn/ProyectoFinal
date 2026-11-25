<div>
  <header class="page-head">
    <div>
      <h1>Alumnos <span>matriculados</span></h1>
      <p>Control académico de estudiantes activos por instrumento y curso.</p>
    </div>
    <div class="head-actions">
      <span class="pill red">{{ $totalInscripcionesActivas }} matrícula(s) activa(s)</span>
    </div>
  </header>

  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />

  <section class="stats" id="studentStats">
    <div class="stat">
      <span class="stat-label">Alumnos matriculados</span>
      <span class="stat-chip red"><svg class="i"><use href="#i-users"/></svg></span>
      <span class="stat-num">{{ $totalInscripcionesActivas }}</span>
      <span class="stat-help">Activos en aula virtual</span>
    </div>
    <div class="stat">
      <span class="stat-label">Cuentas activas</span>
      <span class="stat-chip green"><svg class="i"><use href="#i-check"/></svg></span>
      <span class="stat-num">{{ $totalCuentasActivas }}</span>
      <span class="stat-help">Habilitadas en usuarios_sistema</span>
    </div>
    <div class="stat">
      <span class="stat-label">Cambio de clave pendiente</span>
      <span class="stat-chip neutral"><svg class="i"><use href="#i-user"/></svg></span>
      <span class="stat-num">{{ $totalCambioPendiente }}</span>
      <span class="stat-help">Pendientes de 1er ingreso</span>
    </div>
    <div class="stat">
      <span class="stat-label">Instrumentos</span>
      <span class="stat-chip gray"><svg class="i"><use href="#i-book"/></svg></span>
      <span class="stat-num">{{ $instrumentos->count() }}</span>
      <span class="stat-help">Categorías musicales</span>
    </div>
  </section>

  <!-- Resumen rápido por Cursos (clic para filtrar) -->
  @if ($cursosDisponibles->isNotEmpty())
    <section class="card" style="padding:16px 18px;margin-bottom:18px">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px">
        <div>
          <strong style="font-size:14px;color:var(--ink)">Resumen por cursos</strong>
          <small class="muted" style="display:block;font-size:12px">Haz clic en un curso para filtrar los alumnos matriculados</small>
        </div>
        @if ($filtroCurso !== 'all')
          <button type="button" class="link-btn" wire:click="seleccionarCurso('all')">Ver todos los cursos</button>
        @endif
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px">
        @foreach ($cursosDisponibles as $cursoResumen)
          @php
            $activoCurso = (string) $cursoResumen->id_curso === $filtroCurso;
            $inscritosCurso = (int) ($cursoResumen->alumnos_activos_count ?? 0);
          @endphp
          <button
            type="button"
            wire:key="resumen-curso-{{ $cursoResumen->id_curso }}"
            wire:click="seleccionarCurso('{{ $cursoResumen->id_curso }}')"
            style="text-align:left;padding:11px 13px;border-radius:12px;border:2px solid {{ $activoCurso ? 'var(--red)' : 'var(--line)' }};background:{{ $activoCurso ? 'var(--red-soft)' : '#fff' }};cursor:pointer;display:grid;gap:4px"
          >
            <div style="display:flex;justify-content:space-between;align-items:center;gap:6px">
              <small style="font-size:11px;font-weight:700;color:var(--red-dark);text-transform:uppercase">
                {{ $cursoResumen->instrumento?->nombre_instrumento ?? 'Curso' }}
              </small>
              <span class="pill {{ $inscritosCurso > 0 ? 'green' : '' }}">{{ $inscritosCurso }}/{{ $cursoResumen->cupos }}</span>
            </div>
            <strong style="font-size:13px;color:var(--ink)">{{ $cursoResumen->nombre_curso }}</strong>
          </button>
        @endforeach
      </div>
    </section>
  @endif

  <!-- Pestañas rápidas por Instrumento -->
  <section class="card" style="margin-bottom:18px">
    <div class="tabs-bar" role="tablist" aria-label="Filtrar alumnos por instrumento">
      <button
        type="button"
        class="tab-btn {{ $filtroInstrumento === 'all' ? 'is-active' : '' }}"
        wire:click="seleccionarInstrumento('all')"
      >
        Todos
      </button>
      @foreach ($instrumentos as $inst)
        <button
          type="button"
          class="tab-btn {{ $filtroInstrumento === $inst->nombre_instrumento ? 'is-active' : '' }}"
          wire:key="tab-inst-{{ $inst->id_instrumento }}"
          wire:click="seleccionarInstrumento('{{ $inst->nombre_instrumento }}')"
        >
          {{ $inst->nombre_instrumento }}
        </button>
      @endforeach
    </div>

    <div class="toolbar" style="margin-bottom:0">
      <div class="search">
        <svg class="i"><use href="#i-search"/></svg>
        <input
          class="control"
          type="search"
          id="qStudent"
          wire:model.live.debounce.300ms="busqueda"
          placeholder="Buscar por nombre, apellidos, DNI o código de matrícula..."
          aria-label="Buscar alumnos matriculados"
        >
      </div>

      <select class="control" id="filterStudentLevel" wire:model.live="filtroNivel" aria-label="Filtrar por nivel">
        <option value="all">Todos los niveles</option>
        <option value="Básico">Básico</option>
        <option value="Intermedio">Intermedio</option>
        <option value="Avanzado">Avanzado</option>
      </select>

      <select class="control" id="filterAccountState" wire:model.live="filtroEstadoCuenta" aria-label="Filtrar por estado de cuenta">
        <option value="all">Todos los estados</option>
        <option value="Activo">Activo</option>
        <option value="Inactivo">Inactivo</option>
        <option value="Sin cuenta">Sin cuenta</option>
      </select>

      @if ($busqueda !== '' || $filtroInstrumento !== 'all' || $filtroNivel !== 'all' || $filtroCurso !== 'all' || $filtroEstadoCuenta !== 'all')
        <button type="button" class="link-btn" id="btnClearStudentFilters" wire:click="limpiarFiltros">Limpiar filtros</button>
      @endif
    </div>
  </section>

  <section class="card">
    <div class="table-wrap">
      <table class="responsive-table">
        <thead>
          <tr>
            <th>Código Matrícula</th>
            <th>Estudiante</th>
            <th>DNI</th>
            <th>Contacto</th>
            <th>Instrumento / Curso(s)</th>
            <th>Estado de cuenta</th>
            <th>Fecha de validación</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($matriculasPaginadas as $matricula)
            @php
              $persona = $matricula->alumno?->persona;
              $usuario = $persona?->usuarioSistema;
              $instrumentosNombres = $matricula->detalleMatriculas
                  ->map(fn ($d) => $d->curso?->instrumento?->nombre_instrumento)
                  ->filter()
                  ->unique()
                  ->implode(', ');
              $cursosNombres = $matricula->detalleMatriculas
                  ->map(fn ($d) => $d->curso?->nombre_curso)
                  ->filter()
                  ->implode(', ');
              $pagoValidado = $matricula->pagos->where('estado_validacion', 'Validado')->sortByDesc('fecha_validacion')->first()
                  ?? $matricula->pagos->sortByDesc('id_pago')->first();
              $fechaValidacion = $pagoValidado?->fecha_validacion ?? $matricula->fecha_matricula;
              $estadoCuenta = $usuario?->estado_cuenta ?? 'Sin cuenta';
              $pillCuenta = $estadoCuenta === 'Activo' ? 'green' : ($estadoCuenta === 'Inactivo' ? 'red' : 'gold');
            @endphp
            <tr wire:key="doc-mat-{{ $matricula->id_matricula }}">
              <td class="col-code" data-label="Código Matrícula">{{ $matricula->codigo_matricula }}</td>
              <td data-label="Estudiante">
                <span class="course-name">{{ $persona?->nombre_completo ?? '—' }}</span>
                <small class="muted">{{ $usuario?->correo_usuario ?? $persona?->correo ?? '—' }}</small>
              </td>
              <td class="num" data-label="DNI">{{ $persona?->dni ?? '—' }}</td>
              <td class="num" data-label="Contacto">{{ $persona?->celular ?? '—' }}</td>
              <td data-label="Instrumento / Curso(s)">
                <span class="course-name" style="color:var(--red-dark)">{{ $instrumentosNombres ?: '—' }}</span>
                <small class="muted">{{ $cursosNombres ?: '—' }}</small>
              </td>
              <td data-label="Estado de cuenta">
                <span class="pill {{ $pillCuenta }}">{{ $estadoCuenta }}</span>
              </td>
              <td data-label="Fecha de validación">
                {{ $fechaValidacion?->format('d/m/Y H:i') ?? '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">
                <div class="empty">
                  <h2>No encontramos alumnos matriculados</h2>
                  <p>Prueba seleccionando otro instrumento o limpiando los filtros de búsqueda.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($matriculasPaginadas->hasPages())
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:14px 18px;border-top:1px solid var(--line);font-size:13px;color:var(--muted)">
        <span>
          Mostrando {{ $matriculasPaginadas->firstItem() }} a {{ $matriculasPaginadas->lastItem() }} de {{ $matriculasPaginadas->total() }} matrículas
        </span>
        <div style="display:flex;align-items:center;gap:8px">
          <button
            type="button"
            class="btn btn--outline btn--sm"
            wire:click="previousPage('alumnosPage')"
            @disabled($matriculasPaginadas->onFirstPage())
          >
            ← Anterior
          </button>
          <span style="font-weight:700;color:var(--ink)">
            Página {{ $matriculasPaginadas->currentPage() }} de {{ $matriculasPaginadas->lastPage() }}
          </span>
          <button
            type="button"
            class="btn btn--outline btn--sm"
            wire:click="nextPage('alumnosPage')"
            @disabled(! $matriculasPaginadas->hasMorePages())
          >
            Siguiente →
          </button>
        </div>
      </div>
    @endif
  </section>
</div>
