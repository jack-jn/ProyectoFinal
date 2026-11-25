<div>
<div class="app">

    <!-- MENÚ LATERAL -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <div class="brand">
                <img class="brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys">
                <span class="brand-name">Dunamys</span>
            </div>
            <button type="button" class="sidebar-toggle" id="btnToggleSidebarSec" onclick="document.body.classList.toggle('sidebar-collapsed')" aria-label="Minimizar o expandir barra lateral" title="Minimizar / expandir menú">
                <svg class="i"><use href="#i-menu"/></svg>
            </button>
            <button type="button" class="sidebar-close-btn" onclick="document.body.classList.remove('nav-open')" aria-label="Cerrar menú">
                <svg class="i"><use href="#i-x"/></svg>
            </button>
        </div>
        <div class="user">
            <div class="avatar">S</div>
            <small>SECRETARÍA</small>
            <strong>{{ auth()->user()->persona->nombre_completo }}</strong>
        </div>
        <nav class="nav" aria-label="Menú principal">
            @can('resumen')
            <button type="button" class="nav-item {{ $pantallaActiva === 'resumen' ? 'is-active' : '' }}" wire:click="cambiarPantalla('resumen')" onclick="document.body.classList.remove('nav-open')" title="Resumen">
                <span>▦</span>
                <span class="nav-label">Resumen</span>
            </button>
            @endcan

            @can('pendientes')
            <button type="button" class="nav-item {{ $pantallaActiva === 'pendientes' ? 'is-active' : '' }}" wire:click="cambiarPantalla('pendientes')" onclick="document.body.classList.remove('nav-open')" title="Pagos pendientes">
                <span>◷</span>
                <span class="nav-label">Pagos pendientes</span>
                <span class="nav-badge" id="pendingCount">{{ $pagos->where('estado_validacion', 'Pendiente')->count() }}</span>
            </button>
            @endcan

            @can('historial')
            <button type="button" class="nav-item {{ $pantallaActiva === 'historial' ? 'is-active' : '' }}" wire:click="cambiarPantalla('historial')" onclick="document.body.classList.remove('nav-open')" title="Historial de pagos">
                <span>▤</span>
                <span class="nav-label">Historial de pagos</span>
            </button>
            @endcan

            @can('acceso')
            <button type="button" class="nav-item {{ $pantallaActiva === 'acceso' ? 'is-active' : '' }}" wire:click="cambiarPantalla('acceso')" onclick="document.body.classList.remove('nav-open')" title="Pago aprobado y acceso">
                <span>✉</span>
                <span class="nav-label">Pago aprobado y acceso</span>
            </button>
            @endcan

            @can('atender_reclamaciones')
            @php $reclamosPendientes = \App\Models\Reclamacion::query()->where('estado', 'Pendiente')->count(); @endphp
            <a href="{{ route('secretaria.reclamaciones') }}" class="nav-item" onclick="document.body.classList.remove('nav-open')" title="Reclamaciones">
                <span>⚖</span>
                <span class="nav-label">Reclamaciones</span>
                @if ($reclamosPendientes > 0)
                    <span class="nav-badge">{{ $reclamosPendientes }}</span>
                @endif
            </a>
            @endcan
        </nav>

        <div class="sidebar-foot" style="margin-bottom:10px">
            <p>Institución <b>DUNAMYS</b></p>
            <small>SISTEMA DE GESTIÓN ACADÉMICA</small>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn--outline full" style="width:100%;height:38px;font-size:13px" title="Cerrar sesión">
                <svg class="i"><use href="#i-logout"/></svg><span class="nav-label">Cerrar sesión</span>
            </button>
        </form>
    </aside>
    <div class="scrim" id="secScrim" onclick="document.body.classList.remove('nav-open')"></div>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <div class="doc-mobile-topbar">
            <button type="button" class="btn btn--outline menu-btn" onclick="document.body.classList.toggle('nav-open')" aria-label="Abrir menú lateral">
                <svg class="i"><use href="#i-menu"/></svg> Menú
            </button>
            <strong style="font:700 16px/1 var(--font-display);color:var(--ink)">DUNAMYS · Secretaría</strong>
        </div>
        <header class="top-controls">
            <div style="display:flex;align-items:center;gap:12px;min-width:0">
                <div class="breadcrumb">
                    <span>Gestión</span>
                    <span>/</span>
                    <strong id="breadcrumbName">
                        @if ($pantallaActiva === 'resumen') Resumen de pagos
                        @elseif ($pantallaActiva === 'pendientes') Pagos pendientes
                        @elseif ($pantallaActiva === 'historial') Historial de pagos
                        @elseif ($pantallaActiva === 'acceso') Pago aprobado y acceso
                        @else Gestión de pagos
                        @endif
                    </strong>
                </div>
            </div>
        </header>

        @if ($pantallaActiva === 'sin_acceso')
            <p>No tienes permisos para consultar pagos.</p>
        @endif

        <!-- PANTALLA 1: RESUMEN (SIN BARRA DE FILTROS SEGÚN ALCANCE) -->
        @if ($pantallaActiva === 'resumen')
            <section id="screen-resumen" class="screen active">
                <div class="page-head">
                    <div>
                        <p class="overline">PANEL ADMINISTRATIVO</p>
                        <h1>Resumen de <span>pagos</span></h1>
                        <p>Control y seguimiento de las matrículas y pagos de DUNAMYS.</p>
                    </div>
                    @can('pendientes')
            <button type="button" class="btn btn--primary" wire:click="cambiarPantalla('pendientes')">
                        Revisar pagos
                    </button>
            @endcan
                </div>

                <div class="stats" id="stats">
                    <article class="stat">
                        <span class="stat-label">Total de registros</span>
                        <span class="stat-chip">▦</span>
                        <strong class="stat-num">{{ $pagos->count() }}</strong>
                        <small class="stat-help">Todos los movimientos</small>
                    </article>
                    <article class="stat">
                        <span class="stat-label">Pagos pendientes</span>
                        <span class="stat-chip gold">◷</span>
                        <strong class="stat-num">{{ $pagos->where('estado_validacion', 'Pendiente')->count() }}</strong>
                        <small class="stat-help">Esperando validación</small>
                    </article>
                    <article class="stat">
                        <span class="stat-label">Pagos validados</span>
                        <span class="stat-chip green">✓</span>
                        <strong class="stat-num">{{ $pagos->where('estado_validacion', 'Validado')->count() }}</strong>
                        <small class="stat-help">{{ $pagos->where('estado_validacion', 'Observado')->count() }} observados</small>
                    </article>
                    <article class="stat">
                        <span class="stat-label">Total recaudado</span>
                        <span class="stat-chip red">S/</span>
                        <strong class="stat-num">S/ {{ number_format($totalRecaudado, 2) }}</strong>
                        <small class="stat-help">Solo pagos validados</small>
                    </article>
                </div>

                <div class="callout">
                    <div class="callout-mark">ⓘ</div>
                    <div>
                        <strong>Gestión de comprobantes</strong>
                        <p>Revisa los comprobantes enviados por los alumnos y valida u observa sus pagos.</p>
                    </div>
                    @can('pendientes')
            <button type="button" class="btn btn--outline" wire:click="cambiarPantalla('pendientes')">
                        Ver pendientes
                    </button>
            @endcan
                </div>

                <div class="section-head">
                    <div>
                        <h2>Movimientos recientes</h2>
                        <p>Últimos registros de pagos y matrículas.</p>
                    </div>
                    @can('historial')
            <button type="button" class="text-btn" wire:click="cambiarPantalla('historial')">
                        Ver historial →
                    </button>
            @endcan
                </div>

                <div class="card">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>CÓDIGO</th>
                                    <th>ESTUDIANTE</th>
                                    <th>CURSOS</th>
                                    <th>MONTO</th>
                                    <th>FECHA</th>
                                    <th>ESTADO</th>
                                    <th>ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pagos->take(5) as $pago)
                                    @php
                                        $persona = $pago->matricula?->alumno?->persona;
                                        $cursosNombres = $pago->matricula?->detalleMatriculas->map(fn ($d) => $d->curso?->nombre_curso)->filter()->implode(', ');
                                        $pillClass = $pago->estado_validacion === 'Validado' ? 'green' : ($pago->estado_validacion === 'Observado' ? 'red' : 'gold');
                                    @endphp
                                    <tr wire:key="rec-pago-{{ $pago->id_pago }}">
                                        <td class="td-id">{{ $pago->matricula?->codigo_matricula }}</td>
                                        <td class="student-cell">
                                            <strong>{{ $persona?->nombre_completo }}</strong>
                                            <small>DNI: {{ $persona?->dni }}</small>
                                        </td>
                                        <td>{{ $cursosNombres }}</td>
                                        <td class="num">S/ {{ number_format((float) $pago->monto_pagado, 2) }}</td>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $pago->estado_validacion }}</span></td>
                                        <td>
                                            <button type="button" class="row-action" wire:click="verDetallePago({{ $pago->id_pago }})">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;padding:24px">No hay pagos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <!-- PANTALLA 2: PAGOS PENDIENTES -->
        @if ($pantallaActiva === 'pendientes')
            @php
                $cursosPendientesLista = $filtroInstrumentoPendientes === 'todos'
                    ? $cursos
                    : $cursos->filter(fn ($c) => $c->instrumento?->nombre_instrumento === $filtroInstrumentoPendientes);
            @endphp
            <section id="screen-pendientes" class="screen active">
                <div class="page-head">
                    <div>
                        <p class="overline">VALIDACIÓN DE PAGOS</p>
                        <h1>Pagos <span>pendientes</span></h1>
                        <p>Comprueba los comprobantes enviados por los alumnos.</p>
                    </div>
                    <div class="header-kpi">
                        <span>Resultados:</span>
                        <b id="pendingTotal">{{ $pendientes->count() }}</b>
                    </div>
                </div>

                <div class="card">
                    <div class="table-toolbar sec-filters-bar">
                        <div class="search">
                            <span>⌕</span>
                            <input
                                type="search"
                                id="pendingSearch"
                                wire:model.live.debounce.300ms="busquedaPendientes"
                                class="control"
                                placeholder="Buscar alumno, DNI, matrícula o N° operación..."
                            >
                        </div>

                        <select wire:model.live="filtroEstadoPendientes" class="control status-select" aria-label="Filtrar por estado de pago">
                            <option value="Pendiente">Pendientes</option>
                            <option value="Observado">Observados</option>
                            <option value="Validado">Validados (Pagados)</option>
                            <option value="todos">Todos los estados</option>
                        </select>

                        <select wire:model.live="filtroInstrumentoPendientes" class="control status-select" aria-label="Filtrar por instrumento">
                            <option value="todos">Todos los instrumentos</option>
                            @foreach ($instrumentos as $inst)
                                <option value="{{ $inst->nombre_instrumento }}">{{ $inst->nombre_instrumento }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filtroCursoPendientes" class="control status-select" aria-label="Filtrar por curso">
                            <option value="todos">Todos los cursos</option>
                            @foreach ($cursosPendientesLista as $c)
                                <option value="{{ $c->id_curso }}">{{ $c->nombre_curso }}</option>
                            @endforeach
                        </select>

                        <div class="sec-date-range">
                            <input type="date" wire:model.live="fechaDesdePendientes" class="control" aria-label="Fecha desde" title="Desde">
                            <span>a</span>
                            <input type="date" wire:model.live="fechaHastaPendientes" class="control" aria-label="Fecha hasta" title="Hasta">
                        </div>

                        <select class="control status-select" wire:model.live="filtroEstadoCuentaPendientes" aria-label="Estado de cuenta">
                            <option value="todos">Todas las cuentas</option>
                            <option value="Activo">Cuenta activa</option>
                            <option value="Inactivo">Cuenta inactiva</option>
                            <option value="sin_cuenta">Sin cuenta</option>
                        </select>
                        <button type="button" class="row-action" style="color:var(--red);border-color:var(--red)" wire:click="limpiarFiltrosPendientes">
                            Limpiar filtros
                        </button>
                    </div>

                    @if ($pendientes->isEmpty())
                        <div class="empty">
                            <div class="empty-ico">✓</div>
                            <strong>No hay pagos que coincidan</strong>
                            <p>No existen comprobantes con los filtros seleccionados.</p>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>MATRÍCULA</th>
                                        <th>ESTUDIANTE</th>
                                        <th>CURSOS</th>
                                        <th>N° OPERACIÓN</th>
                                        <th>MONTO</th>
                                        <th>FECHA</th>
                                        <th>ESTADO</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendientes as $pago)
                                        @php
                                            $persona = $pago->matricula?->alumno?->persona;
                                            $cursosNombres = $pago->matricula?->detalleMatriculas->map(fn ($d) => $d->curso?->nombre_curso)->filter()->implode(', ');
                                            $pillClass = $pago->estado_validacion === 'Validado' ? 'green' : ($pago->estado_validacion === 'Observado' ? 'red' : 'gold');
                                        @endphp
                                        <tr wire:key="pend-pago-{{ $pago->id_pago }}">
                                            <td class="td-id">{{ $pago->matricula?->codigo_matricula }}</td>
                                            <td class="student-cell">
                                                <strong>{{ $persona?->nombre_completo }}</strong>
                                                <small>DNI: {{ $persona?->dni }}</small>
                                            </td>
                                            <td>{{ $cursosNombres }}</td>
                                            <td>{{ $pago->numero_operacion ?: '—' }}</td>
                                            <td class="num">S/ {{ number_format((float) $pago->monto_pagado, 2) }}</td>
                                            <td>{{ $pago->fecha_pago?->format('d/m/Y H:i') }}</td>
                                            <td><span class="pill {{ $pillClass }}">{{ $pago->estado_validacion }}</span></td>
                                            <td>
                                                <button type="button" class="row-action" wire:click="verDetallePago({{ $pago->id_pago }})">
                                                    Revisar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <!-- PANTALLA 3: HISTORIAL -->
        @if ($pantallaActiva === 'historial')
            @php
                $cursosHistorialLista = $filtroInstrumentoHistorial === 'todos'
                    ? $cursos
                    : $cursos->filter(fn ($c) => $c->instrumento?->nombre_instrumento === $filtroInstrumentoHistorial);
            @endphp
            <section id="screen-historial" class="screen active">
                <div class="page-head">
                    <div>
                        <p class="overline">REGISTRO FINANCIERO</p>
                        <h1>Historial de <span>pagos</span></h1>
                        <p>Consulta todos los movimientos registrados.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="table-toolbar sec-filters-bar">
                        <div class="search">
                            <span>⌕</span>
                            <input
                                type="search"
                                id="historySearch"
                                wire:model.live.debounce.300ms="busquedaHistorial"
                                class="control"
                                placeholder="Buscar alumno, DNI, matrícula o N° operación..."
                            >
                        </div>

                        <select id="historyStatus" wire:model.live="filtroEstadoHistorial" class="control status-select" aria-label="Filtrar por estado de pago">
                            <option value="todos">Todos los estados</option>
                            <option value="Pendiente">Pendientes</option>
                            <option value="Validado">Validados (Pagados)</option>
                            <option value="Observado">Observados</option>
                        </select>

                        <select wire:model.live="filtroInstrumentoHistorial" class="control status-select" aria-label="Filtrar por instrumento">
                            <option value="todos">Todos los instrumentos</option>
                            @foreach ($instrumentos as $inst)
                                <option value="{{ $inst->nombre_instrumento }}">{{ $inst->nombre_instrumento }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filtroCursoHistorial" class="control status-select" aria-label="Filtrar por curso">
                            <option value="todos">Todos los cursos</option>
                            @foreach ($cursosHistorialLista as $c)
                                <option value="{{ $c->id_curso }}">{{ $c->nombre_curso }}</option>
                            @endforeach
                        </select>

                        <div class="sec-date-range">
                            <input type="date" wire:model.live="fechaDesdeHistorial" class="control" aria-label="Fecha desde" title="Desde">
                            <span>a</span>
                            <input type="date" wire:model.live="fechaHastaHistorial" class="control" aria-label="Fecha hasta" title="Hasta">
                        </div>

                        <select class="control status-select" wire:model.live="filtroEstadoCuentaHistorial" aria-label="Estado de cuenta">
                            <option value="todos">Todas las cuentas</option>
                            <option value="Activo">Cuenta activa</option>
                            <option value="Inactivo">Cuenta inactiva</option>
                            <option value="sin_cuenta">Sin cuenta</option>
                        </select>
                        <button type="button" class="row-action" style="color:var(--red);border-color:var(--red)" wire:click="limpiarFiltrosHistorial">
                            Limpiar filtros
                        </button>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>CÓDIGO</th>
                                    <th>ESTUDIANTE</th>
                                    <th>CURSOS</th>
                                    <th>MÉTODO</th>
                                    <th>MONTO</th>
                                    <th>FECHA</th>
                                    <th>ESTADO</th>
                                    <th>ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historial as $pago)
                                    @php
                                        $persona = $pago->matricula?->alumno?->persona;
                                        $cursosNombres = $pago->matricula?->detalleMatriculas->map(fn ($d) => $d->curso?->nombre_curso)->filter()->implode(', ');
                                        $pillClass = $pago->estado_validacion === 'Validado' ? 'green' : ($pago->estado_validacion === 'Observado' ? 'red' : 'gold');
                                    @endphp
                                    <tr wire:key="hist-pago-{{ $pago->id_pago }}">
                                        <td class="td-id">{{ $pago->matricula?->codigo_matricula }}</td>
                                        <td class="student-cell">
                                            <strong>{{ $persona?->nombre_completo }}</strong>
                                            <small>DNI: {{ $persona?->dni }}</small>
                                        </td>
                                        <td>{{ $cursosNombres }}</td>
                                        <td>{{ $pago->metodo_pago }}</td>
                                        <td class="num">S/ {{ number_format((float) $pago->monto_pagado, 2) }}</td>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $pago->estado_validacion }}</span></td>
                                        <td>
                                            <button type="button" class="row-action" wire:click="verDetallePago({{ $pago->id_pago }})">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="text-align:center;padding:24px">No se encontraron resultados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <!-- PANTALLA 4: PAGO APROBADO Y ACCESO -->
        @if ($pantallaActiva === 'acceso')
            @php
                $cursosAccesosLista = $filtroInstrumentoAccesos === 'todos'
                    ? $cursos
                    : $cursos->filter(fn ($c) => $c->instrumento?->nombre_instrumento === $filtroInstrumentoAccesos);
            @endphp
            <section id="screen-acceso" class="screen active">
                <div class="page-head">
                    <div>
                        <p class="overline">GESTIÓN DE MATRÍCULAS</p>
                        <h1>Pago aprobado y <span>acceso</span></h1>
                        <p>Listado de alumnos con pago validado y estado de sus cuentas de acceso.</p>
                    </div>
                    <span class="pill green">PAGOS VALIDADOS</span>
                </div>

                <div class="card">
                    <div class="table-toolbar sec-filters-bar">
                        <div class="search">
                            <span>⌕</span>
                            <input
                                type="search"
                                id="accessSearch"
                                wire:model.live.debounce.300ms="busquedaAccesos"
                                class="control"
                                placeholder="Buscar alumno, DNI, matrícula o N° operación..."
                            >
                        </div>

                        <select wire:model.live="filtroEstadoPagoAccesos" class="control status-select" aria-label="Filtrar por estado de pago">
                            <option value="Validado">Validados (Pagados)</option>
                            <option value="Pendiente">Pendientes</option>
                            <option value="Observado">Observados</option>
                            <option value="todos">Todos los estados</option>
                        </select>

                        <select wire:model.live="filtroInstrumentoAccesos" class="control status-select" aria-label="Filtrar por instrumento">
                            <option value="todos">Todos los instrumentos</option>
                            @foreach ($instrumentos as $inst)
                                <option value="{{ $inst->nombre_instrumento }}">{{ $inst->nombre_instrumento }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filtroCursoAccesos" class="control status-select" aria-label="Filtrar por curso">
                            <option value="todos">Todos los cursos</option>
                            @foreach ($cursosAccesosLista as $c)
                                <option value="{{ $c->id_curso }}">{{ $c->nombre_curso }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filtroEstadoCuentaAccesos" class="control status-select" aria-label="Filtrar por estado de cuenta">
                            <option value="todos">Todas las cuentas</option>
                            <option value="Activo">Cuenta Activa</option>
                            <option value="Inactivo">Cuenta Inactiva</option>
                            <option value="Sin cuenta">Sin cuenta</option>
                        </select>

                        <div class="sec-date-range">
                            <input type="date" wire:model.live="fechaDesdeAccesos" class="control" aria-label="Fecha desde" title="Desde">
                            <span>a</span>
                            <input type="date" wire:model.live="fechaHastaAccesos" class="control" aria-label="Fecha hasta" title="Hasta">
                        </div>

                            <button type="button" class="row-action" style="color:var(--red);border-color:var(--red)" wire:click="limpiarFiltrosAccesos">
                            Limpiar filtros
                        </button>
                    </div>

                    @if ($accesosPaginados->isEmpty())
                        <div class="empty">
                            <div class="empty-ico">✓</div>
                            <strong>No hay alumnos con pago validado</strong>
                            <p>Aún no se encontraron matrículas con los criterios de búsqueda.</p>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>MATRÍCULA</th>
                                        <th>ESTUDIANTE</th>
                                        <th>DNI</th>
                                        <th>CURSO(S)</th>
                                        <th>CORREO DE ACCESO</th>
                                        <th>ESTADO CUENTA</th>
                                        <th>FECHA VALIDACIÓN</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accesosPaginados as $pagoVal)
                                        @php
                                            $personaVal = $pagoVal->matricula?->alumno?->persona;
                                            $usuarioVal = $personaVal?->usuarioSistema;
                                            $cursosVal = $pagoVal->matricula?->detalleMatriculas->map(fn ($d) => $d->curso?->nombre_curso)->filter()->implode(', ');
                                            $estadoCuenta = $usuarioVal?->estado_cuenta ?? 'Sin cuenta';
                                            $pillCuenta = $estadoCuenta === 'Activo' ? 'green' : 'red';
                                        @endphp
                                        <tr wire:key="acceso-pago-{{ $pagoVal->id_pago }}">
                                            <td class="td-id">{{ $pagoVal->matricula?->codigo_matricula }}</td>
                                            <td class="student-cell">
                                                <strong>{{ $personaVal?->nombre_completo ?? '—' }}</strong>
                                                @if ($usuarioVal?->debe_cambiar_contrasena)
                                                    <small>Cambio pendiente en 1er ingreso</small>
                                                @endif
                                            </td>
                                            <td class="num">{{ $personaVal?->dni ?? '—' }}</td>
                                            <td>{{ $cursosVal ?: '—' }}</td>
                                            <td>
                                                <strong>{{ $usuarioVal?->correo_usuario ?? $personaVal?->correo ?? '—' }}</strong>
                                            </td>
                                            <td>
                                                <span class="pill {{ $pillCuenta }}">{{ $estadoCuenta }}</span>
                                            </td>
                                            <td>{{ $pagoVal->fecha_validacion?->format('d/m/Y H:i') ?? $pagoVal->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td>
                                                <button type="button" class="row-action" wire:click="verDetallePago({{ $pagoVal->id_pago }})">
                                                    Ver detalle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($accesosPaginados->hasPages())
                            <div class="table-footer" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                                <span>
                                    Mostrando {{ $accesosPaginados->firstItem() }} a {{ $accesosPaginados->lastItem() }} de {{ $accesosPaginados->total() }} alumnos validados
                                </span>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <button
                                        type="button"
                                        class="row-action"
                                        wire:click="previousPage('accesosPage')"
                                        @disabled($accesosPaginados->onFirstPage())
                                    >
                                        ← Anterior
                                    </button>
                                    <span style="font-weight:700;color:#252323">
                                        Página {{ $accesosPaginados->currentPage() }} de {{ $accesosPaginados->lastPage() }}
                                    </span>
                                    <button
                                        type="button"
                                        class="row-action"
                                        wire:click="nextPage('accesosPage')"
                                        @disabled(! $accesosPaginados->hasMorePages())
                                    >
                                        Siguiente →
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </section>
        @endif

        <footer>
            <span>© {{ now()->year }} DUNAMYS. Todos los derechos reservados.</span>
            <span>Sistema de gestión de pagos</span>
        </footer>
    </main>
</div>

<!-- MODAL: DETALLE DE PAGO -->
@if ($modalDetalleAbierto && $pagoDetalle)
    @php
        $personaDet = $pagoDetalle->matricula?->alumno?->persona;
        $cursosDet = $pagoDetalle->matricula?->detalleMatriculas->map(fn ($d) => $d->curso?->nombre_curso)->filter()->implode(', ');
        $urlComprobante = route('comprobantes.ver', $pagoDetalle->id_pago);
        $esImagenComprobante = in_array(strtolower(pathinfo($pagoDetalle->comprobante_url, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    @endphp
    <div class="overlay is-open" id="detailOverlay" wire:key="detalle-pago-{{ $pagoDetalle->id_pago }}"
        wire:click.self="cerrarModalDetalle" wire:keydown.escape="cerrarModalDetalle">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="titulo-detalle-pago">
            <div class="dialog-head">
                <h2 id="titulo-detalle-pago">Detalle del comprobante ({{ $pagoDetalle->matricula?->codigo_matricula }})</h2>
                <button class="icon-button" type="button" wire:click="cerrarModalDetalle" aria-label="Cerrar detalle" x-data x-init="$el.focus()">✕</button>
            </div>

            <div class="dialog-body" id="detailBody">
                <div class="detail-grid">
                    <div class="detail-field">
                        <small>ESTUDIANTE</small>
                        <strong>{{ $personaDet?->nombre_completo }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>DNI / CELULAR</small>
                        <strong>{{ $personaDet?->dni }} · {{ $personaDet?->celular }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>CORREO</small>
                        <strong>{{ $personaDet?->correo }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>CURSOS</small>
                        <strong>{{ $cursosDet }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>MÉTODO / N° OPERACIÓN</small>
                        <strong>{{ $pagoDetalle->metodo_pago }} · {{ $pagoDetalle->numero_operacion ?: 'Sin N°' }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>MONTO TOTAL DE MATRÍCULA</small>
                        <strong>S/ {{ number_format((float) $pagoDetalle->matricula?->monto_total, 2) }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>FECHA DEL PAGO</small>
                        <strong>{{ $pagoDetalle->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>ESTADO DE VALIDACIÓN</small>
                        <strong>{{ $pagoDetalle->estado_validacion }}</strong>
                    </div>
                    <div class="detail-field">
                        <small>MONTO PAGADO</small>
                        <strong>S/ {{ number_format((float) $pagoDetalle->monto_pagado, 2) }}</strong>
                    </div>
                </div>

                @if ($apoderadoDetalle)
                    <div class="detail-field" style="margin-top:12px">
                        <small>APODERADO · {{ $apoderadoDetalle->parentesco }}</small>
                        <strong>{{ $apoderadoDetalle->persona?->nombre_completo }}</strong>
                        <span>DNI {{ $apoderadoDetalle->persona?->dni }} · {{ $apoderadoDetalle->persona?->celular ?? 'Sin celular' }}</span>
                        @if ($apoderadoDetalle->persona?->correo)
                            <span>{{ $apoderadoDetalle->persona->correo }}</span>
                        @endif
                    </div>
                @endif

                <div class="voucher-display">
                    <strong>Comprobante adjunto</strong>
                    @if ($esImagenComprobante)
                        <img src="{{ $urlComprobante }}" alt="Vista previa del comprobante" loading="lazy"
                            style="display:block;width:100%;max-height:260px;object-fit:contain;margin-top:8px">
                    @endif
                    <a href="{{ $urlComprobante }}" target="_blank" rel="noopener" class="btn btn--outline" style="margin-top:8px">Ver comprobante</a>
                    <a href="{{ $urlComprobante }}" download class="btn btn--outline" style="margin-top:8px">Descargar comprobante</a>
                </div>

                @if ($pagoDetalle->observacion)
                    <div class="warning-box">
                        <strong>Observación registrada:</strong>
                        {{ $pagoDetalle->observacion }}
                    </div>
                @endif
            </div>

            <div class="dialog-foot" id="detailFoot">
                <button type="button" class="btn btn--outline" wire:click="cerrarModalDetalle">Cerrar</button>
                @if ($pagoDetalle->estado_validacion === 'Pendiente' && auth()->user()->canAny(['secretaria.validar_pagos', 'validar_observar']))
                    <button type="button" class="btn btn--primary" wire:click="abrirModalObservar({{ $pagoDetalle->id_pago }})">
                        Observar comprobante
                    </button>
                    <button type="button" class="btn btn--green" wire:click="validarPago({{ $pagoDetalle->id_pago }})">
                        Validar pago
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- MODAL: OBSERVAR / RECHAZAR PAGO -->
@if ($modalObservarAbierto)
    <div class="overlay is-open" id="rejectOverlay">
        <div class="dialog small-dialog">
            <div class="dialog-head">
                <h2>Observar comprobante</h2>
                <button class="icon-button" type="button" wire:click="cerrarModalObservar">✕</button>
            </div>

            <form id="rejectForm" wire:submit="observarPago" novalidate>
                <div class="dialog-body">
                    <p class="muted">
                        Indica el motivo por el cual el comprobante fue observado para que el alumno pueda volver a subirlo.
                    </p>

                    <label class="field-title" for="rejectReason">
                        Motivo de la observación *
                    </label>

                    <textarea
                        class="control"
                        id="rejectReason"
                        wire:model="motivo_observacion"
                        rows="4"
                        placeholder="Describe el motivo..."
                        required
                    ></textarea>

                    @error('motivo_observacion')
                        <p class="field-error" id="rejectError">{{ $message }}</p>
                    @enderror
                </div>

                <div class="dialog-foot">
                    <button type="button" class="btn btn--outline" wire:click="cerrarModalObservar">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn--primary">
                        Confirmar observación
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- MODAL: CONFIRMACIÓN DE VALIDACIÓN Y ENVÍO DE CORREO -->
@if ($modalConfirmacionCorreoAbierto)
    <div class="overlay is-open" id="emailConfirmOverlay" wire:click.self="cerrarModalConfirmacionCorreo" wire:keydown.escape="cerrarModalConfirmacionCorreo">
        <div class="dialog dialog--sm" role="dialog" aria-modal="true" aria-labelledby="tituloConfirmacionCorreo">
            <div class="dialog-head">
                <div>
                    <h3 id="tituloConfirmacionCorreo">
                        {{ $correoConfirmacionEnviado ? '¡Alumno validado y correo enviado!' : 'Alumno validado (correo pendiente)' }}
                    </h3>
                </div>
                <button class="icon-button" type="button" wire:click="cerrarModalConfirmacionCorreo" aria-label="Cerrar confirmación">✕</button>
            </div>

            <div class="dialog-body">
                @if ($correoConfirmacionEnviado)
                    <p style="margin:0 0 12px;line-height:1.55;">
                        El pago de <strong>{{ $correoConfirmacionNombre }}</strong> ha sido validado correctamente. Las credenciales de acceso fueron enviadas al correo <strong>{{ $correoConfirmacionDestino }}</strong>.
                    </p>
                    <p style="margin:0;font-size:13px;color:var(--muted);background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:10px 12px;">
                        Por seguridad, la contraseña temporal solo fue enviada al correo del estudiante.
                    </p>
                @else
                    <p style="margin:0;line-height:1.55;color:var(--red-dark);">
                        El pago fue validado y el acceso quedó activo, pero no se pudo enviar el correo a <strong>{{ $correoConfirmacionDestino }}</strong>. Verifica la configuración SMTP en el archivo <code>.env</code>.
                    </p>
                @endif
            </div>

            <div class="dialog-foot">
                <button type="button" class="btn btn--primary" wire:click="cerrarModalConfirmacionCorreo">
                    Entendido / Cerrar
                </button>
            </div>
        </div>
    </div>
@endif

<x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />
</div>
