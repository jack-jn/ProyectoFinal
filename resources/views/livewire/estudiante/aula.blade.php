<div>
  <div class="app">
    <!-- ================= BARRA LATERAL ================= -->
    <aside class="sidebar" id="estSidebar">
      <div class="sidebar-top">
        <div class="brand">
          <img class="brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
          <span class="brand-name">Dunamys</span>
        </div>
        <button type="button" class="sidebar-toggle" id="btnToggleSidebarEst" onclick="document.body.classList.toggle('sidebar-collapsed')" aria-label="Minimizar o expandir barra lateral" title="Minimizar / expandir menú">
          <svg class="i"><use href="#i-menu"/></svg>
        </button>
        <button type="button" class="sidebar-close-btn" onclick="document.body.classList.remove('nav-open')" aria-label="Cerrar menú lateral">
          <svg class="i"><use href="#i-x"/></svg>
        </button>
      </div>

      <div class="user">
        <div class="avatar" id="estUserAvatar">{{ $inicialAlumno }}</div>
        <small>USUARIO: Alumno</small>
        <strong id="estUserName">{{ $nombreAlumno }}</strong>
      </div>

      <nav class="nav" aria-label="Menú principal del estudiante">
        <button
          type="button"
          class="nav-item {{ $vistaActiva === 'cursos' ? 'is-active' : '' }}"
          id="estNavCursos"
          wire:click="cambiarVista('cursos')"
          onclick="document.body.classList.remove('nav-open')"
        >
          <svg class="i"><use href="#i-grid"/></svg>
          <span class="nav-label">Mis cursos</span>
          <svg class="i chev"><use href="#i-right"/></svg>
        </button>
        @if ($vistaActiva !== 'cursos')
          <button
            type="button"
            class="nav-item {{ $vistaActiva === 'sesiones' ? 'is-active' : '' }}"
            id="estNavSesiones"
            wire:click="cambiarVista('sesiones')"
            onclick="document.body.classList.remove('nav-open')"
          >
            <svg class="i"><use href="#i-book"/></svg>
            <span class="nav-label">Sesiones</span>
            <svg class="i chev"><use href="#i-right"/></svg>
          </button>
          <button
            type="button"
            class="nav-item {{ in_array($vistaActiva, ['tareas', 'detalle_entrega'], true) ? 'is-active' : '' }}"
            id="estNavTareas"
            wire:click="cambiarVista('tareas')"
            onclick="document.body.classList.remove('nav-open')"
          >
            <svg class="i"><use href="#i-pencil"/></svg>
            <span class="nav-label">Tareas</span>
            <svg class="i chev"><use href="#i-right"/></svg>
          </button>
        @endif
      </nav>

      <div class="sidebar-foot">
        La música<span>transforma vidas</span>
      </div>

      <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="sidebar-logout" id="estLogoutBtn" style="width:100%;">
          <svg class="i"><use href="#i-logout"/></svg>
          <span>Cerrar sesión</span>
        </button>
      </form>
    </aside>
    <div class="scrim" id="estScrim" onclick="document.body.classList.remove('nav-open')"></div>

    <!-- ================= CONTENIDO PRINCIPAL ================= -->
    <main class="main">
      <div class="doc-mobile-topbar">
        <button
          type="button"
          class="btn btn--outline menu-btn"
          id="estMenuBtn"
          aria-label="Abrir menú de navegación"
          onclick="document.body.classList.toggle('nav-open')"
        >
          <svg class="i"><use href="#i-menu"/></svg> Menú
        </button>
        <strong style="font:700 16px/1 var(--font-display);color:var(--ink)">DUNAMYS · Alumno</strong>
      </div>
      <!-- Cabecera de Página -->
      <header class="page-head">
        <div style="display:flex;align-items:center;gap:14px;">
          <div>
            @if ($accesoNoAutorizado)
              <h1 id="estPageTitle">Acceso <span>Restringido</span></h1>
              <p id="estPageSubtitle">Verificación de matrícula del alumno</p>
            @elseif ($vistaActiva === 'cursos')
              <h1 id="estPageTitle">Mis <span>cursos</span></h1>
              <p id="estPageSubtitle">Elige el curso al que quieres entrar. También puedes revisar tus cursos concluidos.</p>
            @elseif ($vistaActiva === 'sesiones')
              <h1 id="estPageTitle">Mis <span class="nav-label">Sesiones</span></h1>
              <p id="estPageSubtitle">Explora el temario ordenado, visualiza lecciones y descarga tus materiales de estudio.</p>
            @elseif ($vistaActiva === 'tareas')
              <h1 id="estPageTitle">Mis <span class="nav-label">Tareas</span></h1>
              <p id="estPageSubtitle">Entrega tus evidencias de práctica, consulta el estado de revisión y retroalimentación.</p>
            @else
              <h1 id="estPageTitle">Detalle de <span>Entrega</span></h1>
              <p id="estPageSubtitle">Consulta la retroalimentación, calificación y estado de tu tarea.</p>
            @endif
          </div>
        </div>

        @if ($vistaActiva !== 'cursos')
          <div class="head-actions" id="estHeadActions">
            @if ($cursoActivo)
              <div class="course-chip" id="estCourseChip" title="Curso matriculado">
                <span class="course-chip__dot"></span>
                <span id="estCourseChipText">{{ $cursoActivo->codigo_curso }} · {{ $cursoActivo->nombre_curso }}</span>
              </div>
            @endif

            @if ($detallesActivos->isNotEmpty())
              <button
                type="button"
                class="btn btn--outline btn--sm"
                id="estChangeCourseBtn"
                wire:click="cambiarVista('cursos')"
                title="Volver a la selección de cursos"
              >
                <svg class="i"><use href="#i-left"/></svg> Cambiar de curso
              </button>
            @endif
          </div>
        @endif
      </header>

      @if ($vistaActiva !== 'cursos' && ! $accesoNoAutorizado && $cursoActivo && $totalSesiones > 0)
        <div class="est-course-progress {{ $porcentajeProgreso === 100 ? 'is-completed' : '' }}" id="estCourseProgress">
          <div class="est-course-progress__info">
            <span class="est-course-progress__text" id="estProgressText">
              Progreso del curso · {{ $sesionesCompletadasCount }} de {{ $totalSesiones }} sesiones completadas
            </span>
            <span class="est-course-progress__percent" id="estProgressPercent">{{ $porcentajeProgreso }}%</span>
          </div>
          <div class="est-course-progress__track" role="progressbar" id="estProgressBar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $porcentajeProgreso }}" aria-label="Progreso general del curso">
            <div class="est-course-progress__fill" id="estProgressFill" style="width:{{ $porcentajeProgreso }}%;"></div>
          </div>
        </div>
      @endif

      <!-- Contenedor dinámico -->
      <div id="estAppContent">
        @if ($accesoNoAutorizado)
          <div class="est-detail-panel">
            <div class="est-state-panel">
              <div class="est-state-icon danger">
                <svg class="i" style="width:32px;height:32px;"><use href="#i-alert"/></svg>
              </div>
              <h3>No tienes autorización para acceder a esta sesión</h3>
              <p>Esta sesión pertenece a un curso en el que no te encuentras matriculado actualmente. Si consideras que es un error, comunícate con la administración de Dunamys.</p>
              <button type="button" class="btn btn--primary" wire:click="cambiarVista('sesiones')">
                <svg class="i"><use href="#i-left"/></svg> Volver a mis sesiones
              </button>
            </div>
          </div>
        @elseif ($vistaActiva === 'cursos')
          <section class="est-courses-section">
            <h2 class="est-section-label">En curso</h2>
            @if ($cursosEnProgresoPorInstrumento->isEmpty())
              <div class="est-empty-courses">
                {{ $detallesActivos->isNotEmpty() ? 'No tienes cursos en progreso.' : 'Aún no estás matriculado en ningún curso activo.' }}
              </div>
            @else
              @foreach ($cursosEnProgresoPorInstrumento as $instrumento => $itemsCurso)
                <h3 class="est-group-title" wire:key="grupo-inst-{{ \Illuminate\Support\Str::slug($instrumento) }}">
                  {{ $instrumento }}
                  <span class="est-group-count">· {{ $itemsCurso->count() }} {{ $itemsCurso->count() === 1 ? 'curso' : 'cursos' }}</span>
                </h3>
                <div class="est-courses-grid">
                  @foreach ($itemsCurso as $itemCurso)
                    @php $cItem = $itemCurso['curso']; @endphp
                    <button
                      type="button"
                      class="est-course-card"
                      wire:key="card-curso-{{ $itemCurso['detalle']->id_detalle_matricula }}"
                      wire:click="cambiarCurso({{ $itemCurso['detalle']->id_detalle_matricula }})"
                    >
                      <div class="est-course-card__top">
                        <span class="est-course-card__code">{{ $cItem?->codigo_curso }}</span>
                        <span class="pill neutral">{{ $cItem?->modalidad ?? 'Virtual' }}</span>
                      </div>
                      <h3 class="est-course-card__title">{{ $cItem?->nombre_curso }}</h3>
                      <div class="est-course-card__meta">
                        <span class="est-course-card__meta-item">{{ $cItem?->nivel ?? 'Básico' }}</span>
                        <span class="est-course-card__meta-item">
                          {{ $cItem?->docente?->persona ? 'Prof. ' . $cItem->docente->persona->nombres . ' ' . $cItem->docente->persona->apellido_paterno : 'Prof. Dunamys' }}
                        </span>
                      </div>
                      <div class="est-course-card__progress">
                        <div class="est-course-card__progress-info">
                          <span>{{ $itemCurso['completadas'] }} de {{ $itemCurso['total'] }} sesiones</span>
                          <strong>{{ $itemCurso['porcentaje'] }}%</strong>
                        </div>
                        <div class="est-course-card__progress-track">
                          <div class="est-course-card__progress-fill" style="width:{{ $itemCurso['porcentaje'] }}%;"></div>
                        </div>
                      </div>
                      <span class="est-course-card__cta">Entrar al curso →</span>
                    </button>
                  @endforeach
                </div>
              @endforeach
            @endif
          </section>

          <section class="est-courses-section">
            <h2 class="est-section-label">Concluidos</h2>
            @if ($cursosConcluidos->isEmpty())
              <div class="est-empty-courses">Aún no tienes cursos concluidos.</div>
            @else
              <div class="est-courses-grid" style="margin-top:12px;">
                @foreach ($cursosConcluidos as $itemCurso)
                  @php $cItem = $itemCurso['curso']; @endphp
                  <button
                    type="button"
                    class="est-course-card est-course-card--concluido"
                    wire:key="card-curso-concluido-{{ $itemCurso['detalle']->id_detalle_matricula }}"
                    wire:click="cambiarCurso({{ $itemCurso['detalle']->id_detalle_matricula }})"
                  >
                    <div class="est-course-card__top">
                      <span class="est-course-card__code">{{ $cItem?->codigo_curso }}</span>
                      <span class="pill green">Concluido</span>
                    </div>
                    <h3 class="est-course-card__title">{{ $cItem?->nombre_curso }}</h3>
                    <div class="est-course-card__meta">
                      <span class="est-course-card__meta-item">{{ $cItem?->nivel ?? 'Básico' }}</span>
                      <span class="est-course-card__meta-item">
                        {{ $cItem?->docente?->persona ? 'Prof. ' . $cItem->docente->persona->nombres . ' ' . $cItem->docente->persona->apellido_paterno : 'Prof. Dunamys' }}
                      </span>
                    </div>
                    <div class="est-course-card__progress">
                      <div class="est-course-card__progress-info">
                        <span>{{ $itemCurso['completadas'] }} de {{ $itemCurso['total'] }} sesiones</span>
                        <strong>{{ $itemCurso['porcentaje'] }}%</strong>
                      </div>
                      <div class="est-course-card__progress-track">
                        <div class="est-course-card__progress-fill" style="width:{{ $itemCurso['porcentaje'] }}%;"></div>
                      </div>
                    </div>
                    <span class="est-course-card__cta">Ver curso →</span>
                  </button>
                @endforeach
              </div>
            @endif
          </section>
        @elseif (! $cursoActivo)
          <div class="est-detail-panel">
            <div class="est-state-panel">
              <div class="est-state-icon lock">
                <svg class="i" style="width:32px;height:32px;"><use href="#i-lock"/></svg>
              </div>
              <h3>No tienes cursos con acceso activo</h3>
              <p>Una vez que Secretaría valide tu comprobante de pago, tus cursos matriculados aparecerán aquí automáticamente.</p>
            </div>
          </div>
        @elseif ($vistaActiva === 'sesiones')
          @php
            $estadoSesionActiva = $sesionActiva ? ($estadosSesiones[$sesionActiva->id_sesion] ?? 'En proceso') : 'En proceso';
            $nombreDocente = $cursoActivo->docente?->persona
              ? 'Prof. ' . $cursoActivo->docente->persona->nombres . ' ' . $cursoActivo->docente->persona->apellido_paterno
              : 'Prof. Dunamys';
          @endphp

          <div class="est-master-detail">
            <aside class="est-side-list" aria-label="Lista de sesiones del curso">
              <div class="est-side-list__head">
                <h2>Sesiones del curso</h2>
                <p>{{ $cursoActivo->codigo_curso }} · {{ $cursoActivo->nombre_curso }}</p>
              </div>

              @foreach ($sesiones as $sesionItem)
                @php
                  $st = $estadosSesiones[$sesionItem->id_sesion] ?? 'Bloqueada';
                  $isSelected = $sesionActiva && $sesionActiva->id_sesion === $sesionItem->id_sesion;
                  $badgeClass = $st === 'Completada' ? 'green' : ($st === 'En proceso' ? 'blue' : 'neutral');
                @endphp
                <button
                  type="button"
                  wire:key="sesion-nav-{{ $sesionItem->id_sesion }}"
                  wire:click="seleccionarSesion({{ $sesionItem->id_sesion }})"
                  class="est-item-card {{ $isSelected ? 'is-active' : '' }} {{ $st === 'Bloqueada' ? 'is-locked' : '' }}"
                >
                  <div class="est-item-card__badge">{{ $sesionItem->numero_orden }}</div>
                  <div class="est-item-card__info">
                    <div class="est-item-card__top">
                      <span class="est-item-card__num">Sesión {{ $sesionItem->numero_orden }}</span>
                      <span class="pill {{ $badgeClass }}">
                        @if ($st === 'Completada')
                          <svg class="i" style="width:14px;height:14px;"><use href="#i-check"/></svg>
                        @elseif ($st === 'Bloqueada')
                          <svg class="i" style="width:14px;height:14px;"><use href="#i-lock"/></svg>
                        @endif
                        {{ $st }}
                      </span>
                    </div>
                    <strong class="est-item-card__title">{{ $sesionItem->titulo_sesion }}</strong>
                  </div>
                </button>
              @endforeach
            </aside>

            <section class="est-detail-panel" aria-label="Detalle de sesión">
              @if (! $sesionActiva)
                <div class="est-state-panel">
                  <h3>Este curso aún no tiene sesiones publicadas</h3>
                </div>
              @elseif ($estadoSesionActiva === 'Bloqueada')
                <div class="est-state-panel">
                  <div class="est-state-icon lock">
                    <svg class="i" style="width:32px;height:32px;"><use href="#i-lock"/></svg>
                  </div>
                  <h3>Sesión {{ $sesionActiva->numero_orden }}: {{ $sesionActiva->titulo_sesion }}</h3>
                  <p>Debes completar la sesión anterior para acceder a esta. Entrega la tarea pendiente para desbloquear el contenido y materiales.</p>
                </div>
              @else
                <div class="est-detail-hero">
                  <div class="est-detail-hero__main">
                    <span class="pill {{ $estadoSesionActiva === 'Completada' ? 'green' : 'blue' }}">
                      {{ $estadoSesionActiva === 'Completada' ? '✔ Sesión completada' : 'En proceso' }}
                    </span>
                    <h2>Sesión {{ $sesionActiva->numero_orden }}: {{ $sesionActiva->titulo_sesion }}</h2>
                    <div class="est-detail-hero__meta">
                      <span>📅 Publicada: {{ $sesionActiva->created_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}</span>
                      <span>•</span>
                      <span>Docente: {{ $nombreDocente }}</span>
                      <span>•</span>
                      <span>{{ $sesionActiva->lecciones->count() }} lección(es) disponibles</span>
                    </div>
                  </div>
                  <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @if ($estadoSesionActiva !== 'Completada')
                      <button
                        type="button"
                        class="btn btn--outline"
                        wire:click="marcarSesionCompletada({{ $sesionActiva->id_sesion }})"
                      >
                        <svg class="i"><use href="#i-check"/></svg>
                        <span>Marcar completada</span>
                      </button>
                    @endif
                    @if ($sesionActiva->tareaAsignada)
                      <button
                        type="button"
                        class="btn btn--primary"
                        wire:click="cambiarVista('tareas', {{ $sesionActiva->id_sesion }})"
                      >
                        <span>Ir a la tarea de esta sesión</span>
                        <svg class="i"><use href="#i-right"/></svg>
                      </button>
                    @endif
                  </div>
                </div>

                <div class="est-detail-body">
                  <div class="est-desc-box">
                    <strong>Acerca de esta sesión:</strong>
                    <div>{{ $sesionActiva->lecciones->first()?->descripcion ?? $sesionActiva->titulo_sesion }}</div>
                  </div>

                  @can('ver_recursos')
                  <div class="est-section-title">
                    <span>Lecciones de la sesión</span>
                    <small class="muted" style="font-weight:normal;font-size:12.5px;">{{ $sesionActiva->lecciones->count() }} {{ $sesionActiva->lecciones->count() === 1 ? 'lección' : 'lecciones' }}</small>
                  </div>

                  <div class="est-materials-grid">
                    @forelse ($sesionActiva->lecciones as $leccion)
                      <article class="est-lesson-card" wire:key="leccion-alumno-{{ $leccion->id_leccion }}">
                        <div class="est-lesson-card__head">
                          <span class="est-lesson-card__num">Lección {{ str_pad((string) $leccion->numero_orden, 3, '0', STR_PAD_LEFT) }}</span>
                          <div class="est-lesson-card__meta">
                            @foreach ($leccion->recursos->pluck('tipoRecurso.nombre_tipo')->filter()->unique() as $tipoNombre)
                              <span class="pill {{ $tipoNombre === 'PDF' ? 'blue' : ($tipoNombre === 'MP4' ? 'red' : 'neutral') }}" wire:key="pill-lec-{{ $leccion->id_leccion }}-{{ $tipoNombre }}">{{ $tipoNombre }}</span>
                            @endforeach
                          </div>
                        </div>
                        <div class="est-lesson-card__content">
                          <div class="est-lesson-field">
                            <span class="est-lesson-label">Título de la lección</span>
                            <strong class="est-lesson-title">{{ $leccion->titulo_leccion }}</strong>
                          </div>
                          @if ($leccion->descripcion)
                            <div class="est-lesson-field">
                              <span class="est-lesson-label">Descripción</span>
                              <p class="est-lesson-desc">{{ $leccion->descripcion }}</p>
                            </div>
                          @endif
                          <x-recursos-sesion :recursos="$leccion->recursos" :titulo="$leccion->titulo_leccion" />
                        </div>
                      </article>
                    @empty
                      <p class="muted">Esta sesión aún no tiene lecciones adicionales asignadas.</p>
                    @endforelse
                  </div>
                  @endcan
                </div>
              @endif
            </section>
          </div>

        @elseif ($vistaActiva === 'tareas')
          @php
            $estadoSesionActiva = $sesionActiva ? ($estadosSesiones[$sesionActiva->id_sesion] ?? 'En proceso') : 'En proceso';
            $tareaActiva = $sesionActiva?->tareaAsignada;
            $entregaActiva = $tareaActiva ? $entregasPorTarea->get($tareaActiva->id_tarea) : null;
          @endphp

          <div class="est-master-detail">
            <aside class="est-side-list" aria-label="Lista de tareas del curso">
              <div class="est-side-list__head">
                <h2>Tareas del curso</h2>
                <p>Estado de entregas y evaluaciones</p>
              </div>

              @foreach ($sesiones as $sesionItem)
                @php
                  $st = $estadosSesiones[$sesionItem->id_sesion] ?? 'Bloqueada';
                  $tareaItem = $sesionItem->tareaAsignada;
                  $entregaItem = $tareaItem ? $entregasPorTarea->get($tareaItem->id_tarea) : null;
                  $isSelected = $sesionActiva && $sesionActiva->id_sesion === $sesionItem->id_sesion;

                  $estadoTarea = 'Por entregar';
                  $pillClass = 'neutral';
                  if ($st === 'Bloqueada') {
                      $estadoTarea = 'Bloqueada';
                      $pillClass = 'neutral';
                  } elseif ($entregaItem) {
                      $estadoTarea = $entregaItem->estado_revision;
                      $pillClass = $estadoTarea === 'Aprobada' ? 'green' : ($estadoTarea === 'Observada' ? 'red' : 'gold');
                  }
                @endphp
                <button
                  type="button"
                  wire:key="tarea-nav-{{ $sesionItem->id_sesion }}"
                  wire:click="seleccionarSesion({{ $sesionItem->id_sesion }})"
                  class="est-item-card {{ $isSelected ? 'is-active' : '' }} {{ $st === 'Bloqueada' ? 'is-locked' : '' }}"
                >
                  <div class="est-item-card__badge">{{ $sesionItem->numero_orden }}</div>
                  <div class="est-item-card__info">
                    <div class="est-item-card__top">
                      <span class="est-item-card__num">Tarea Sesión {{ $sesionItem->numero_orden }}</span>
                      <span class="pill {{ $pillClass }}">{{ $estadoTarea }}</span>
                    </div>
                    <strong class="est-item-card__title">{{ $tareaItem?->titulo_tarea ?? $sesionItem->titulo_sesion }}</strong>
                  </div>
                </button>
              @endforeach
            </aside>

            <section class="est-detail-panel" aria-label="Detalle y entrega de tarea">
              @if ($estadoSesionActiva === 'Bloqueada')
                <div class="est-state-panel">
                  <div class="est-state-icon lock">
                    <svg class="i" style="width:32px;height:32px;"><use href="#i-lock"/></svg>
                  </div>
                  <h3>Tarea Sesión {{ $sesionActiva?->numero_orden }}: Bloqueada</h3>
                  <p>Para desbloquear esta tarea, primero debes entregar y completar la sesión anterior.</p>
                </div>
              @elseif (! $tareaActiva)
                <div class="est-state-panel">
                  <h3>Esta sesión no tiene tarea asignada</h3>
                </div>
              @else
                @php
                  $isDelivered = $entregaActiva && $entregaActiva->estado_revision !== 'Observada';
                  $isObserved = $entregaActiva && $entregaActiva->estado_revision === 'Observada';
                  $plazoVencido = $tareaActiva->fecha_limite && now()->startOfDay()->gt($tareaActiva->fecha_limite);
                @endphp

                <div class="est-detail-hero">
                  <div class="est-detail-hero__main">
                    @if ($tareaActiva->fecha_limite)
                      <div class="est-deadline-badge {{ $plazoVencido ? '' : 'ontime' }}">
                        <span>📅 Fecha límite de entrega: {{ $tareaActiva->fecha_limite->format('Y-m-d') }} 11:59 PM</span>
                        <span class="pill {{ $plazoVencido ? 'red' : 'neutral' }}">
                          {{ $plazoVencido ? 'Plazo vencido' : 'Dentro del plazo' }}
                        </span>
                      </div>
                    @endif
                    <h2>Tarea Sesión {{ $sesionActiva->numero_orden }}: {{ $tareaActiva->titulo_tarea }}</h2>
                    <div class="est-detail-hero__meta">
                      <span>Curso: {{ $cursoActivo->nombre_curso }}</span>
                    </div>
                  </div>
                  @if ($entregaActiva)
                    <div>
                      <button
                        type="button"
                        class="btn btn--outline btn--sm"
                        wire:click="cambiarVista('detalle_entrega', {{ $sesionActiva->id_sesion }})"
                      >
                        <svg class="i"><use href="#i-eye"/></svg> Ver detalles
                      </button>
                    </div>
                  @endif
                </div>

                <div class="est-detail-body">
                  <div class="est-task-consigna">
                    <strong style="display:block;font-size:12.5px;color:var(--red);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
                      Instrucciones del docente:
                    </strong>
                    <p style="margin:0;font-size:14.5px;line-height:1.6;color:var(--ink-2);">{{ $tareaActiva->descripcion }}</p>

                    @can('ver_recursos')
                      <x-recursos-sesion :recursos="$tareaActiva->recursos" :titulo="$tareaActiva->titulo_tarea" />
                    @endcan
                  </div>

                  @if ($isDelivered)
                    <div style="background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:24px;margin-top:20px;text-align:center;">
                      <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;">
                        <span class="pill {{ $entregaActiva->estado_revision === 'Aprobada' ? 'green' : 'gold' }}" style="font-size:13px;padding:5px 12px;">
                          {{ $entregaActiva->estado_revision === 'Aprobada' ? '✔ Tarea Aprobada' : '⏱ Entrega en revisión (Pendiente)' }}
                        </span>
                      </div>
                      <p style="margin:0 0 16px;font-size:14px;color:var(--ink-2);">
                        Tu tarea fue enviada el <strong>{{ $entregaActiva->fecha_entrega?->format('d/m/Y H:i') }}</strong>.
                        <br>
                        Enlace enviado: <a href="{{ $entregaActiva->video_evidencia }}" target="_blank" rel="noopener" class="est-link">{{ $entregaActiva->video_evidencia }}</a>
                      </p>
                      <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                        <button type="button" class="btn btn--green" disabled>
                          <svg class="i"><use href="#i-check"/></svg> Tarea entregada ✓
                        </button>
                        <button
                          type="button"
                          class="btn btn--primary"
                          wire:click="cambiarVista('detalle_entrega', {{ $sesionActiva->id_sesion }})"
                        >
                          <svg class="i"><use href="#i-eye"/></svg> Ver detalles de la entrega
                        </button>
                      </div>
                    </div>
                  @elseif (auth()->user()->can('entregar_tareas'))
                    @if ($isObserved)
                      <div class="est-alert est-alert--warning" style="margin:16px 0;">
                        <svg class="i"><use href="#i-alert"/></svg>
                        <div>
                          <strong>Tarea Observada por el docente:</strong>
                          <div>"{{ $entregaActiva->comentario_docente }}"</div>
                          <div style="margin-top:4px;font-size:12.5px;">Ingresa tu nuevo enlace de video a continuación para volver a entregar.</div>
                        </div>
                      </div>
                    @endif

                    <div class="est-section-title" style="margin-top:20px;">
                      <span>{{ $isObserved ? 'Volver a entregar tarea' : 'Entregar tarea' }}</span>
                      <small class="muted" style="font-weight:normal;font-size:12px;">Pega el enlace a tu video de práctica en YouTube o Google Drive</small>
                    </div>

                    <div class="est-link-section" style="background:var(--surface);border:1.5px solid var(--line);border-radius:14px;padding:18px;margin-top:0;">
                      <label for="inputVideoEvidencia" class="est-link-section-title">
                        <svg class="i" style="width:16px;height:16px;"><use href="#i-link"/></svg>
                        <span>Enlace de video de evidencia (YouTube / Google Drive) *</span>
                      </label>
                      <div class="est-link-input-group">
                        <input
                          type="url"
                          id="inputVideoEvidencia"
                          wire:model="video_evidencia"
                          class="est-link-input"
                          placeholder="https://www.youtube.com/watch?v=... o https://drive.google.com/..."
                        >
                      </div>
                      @error('video_evidencia')
                        <div class="est-error" style="display:flex;margin-top:8px;" role="alert">
                          {{ $message }}
                        </div>
                      @enderror
                    </div>

                    <div class="est-task-actions-row">
                      <button
                        type="button"
                        class="btn btn--soft-red"
                        wire:click="limpiarEnlaceTarea"
                      >
                        <svg class="i"><use href="#i-x"/></svg> Cancelar
                      </button>
                      <button
                        type="button"
                        class="btn btn--green"
                        wire:click="entregarTarea({{ $sesionActiva->id_sesion }})"
                      >
                        <svg class="i"><use href="#i-upload"/></svg>
                        {{ $isObserved ? 'Reenviar tarea' : 'Entregar tarea' }}
                      </button>
                    </div>
                  @endif
                </div>
              @endif
            </section>
          </div>

        @elseif ($vistaActiva === 'detalle_entrega')
          @php
            $tareaActiva = $sesionActiva?->tareaAsignada;
            $entregaActiva = $tareaActiva ? $entregasPorTarea->get($tareaActiva->id_tarea) : null;
            $nombreDocente = $cursoActivo->docente?->persona
              ? 'Prof. ' . $cursoActivo->docente->persona->nombres . ' ' . $cursoActivo->docente->persona->apellido_paterno
              : 'Prof. Dunamys';
          @endphp

          @if (! $sesionActiva || ! $tareaActiva || ! $entregaActiva)
            <div class="est-detail-panel">
              <div class="est-state-panel">
                <h3>Esta tarea aún no ha sido entregada</h3>
                <p>Debes adjuntar tu enlace de video de evidencia para consultar los detalles y la revisión.</p>
                <button type="button" class="btn btn--primary" wire:click="cambiarVista('tareas')">
                  Ir a entregar tarea
                </button>
              </div>
            </div>
          @else
            @php
              $step1Class = 'completed';
              $step2Class = $entregaActiva->estado_revision !== 'Pendiente' ? 'completed' : 'current';
              $step3Class = $entregaActiva->estado_revision === 'Aprobada'
                  ? 'completed'
                  : ($entregaActiva->estado_revision === 'Observada' ? 'observed' : '');
              $step3Label = $entregaActiva->estado_revision === 'Aprobada'
                  ? '✔ Aprobado'
                  : ($entregaActiva->estado_revision === 'Observada' ? '⚠ Observado' : 'Por calificar');
              $step3Icon = $entregaActiva->estado_revision === 'Aprobada'
                  ? '✓'
                  : ($entregaActiva->estado_revision === 'Observada' ? '!' : '3');
            @endphp

            <div class="est-full-detail">
              <div class="est-detail-backbar">
                <button type="button" class="btn btn--ghost btn--sm" wire:click="cambiarVista('tareas', {{ $sesionActiva->id_sesion }})">
                  <svg class="i"><use href="#i-left"/></svg>
                  <span>Volver a tareas</span>
                </button>
                <span style="font-weight:700;font-size:15px;">Tarea Sesión {{ $sesionActiva->numero_orden }}: {{ $tareaActiva->titulo_tarea }}</span>
                <span class="pill {{ $entregaActiva->estado_revision === 'Aprobada' ? 'green' : ($entregaActiva->estado_revision === 'Observada' ? 'red' : 'gold') }}">
                  {{ $entregaActiva->estado_revision }}
                </span>
              </div>

              <div class="est-stats-grid">
                <div class="est-stat-box highlight">
                  <small>Fecha límite de entrega</small>
                  <strong>{{ $tareaActiva->fecha_limite?->format('Y-m-d') ?? 'Sin límite' }} 11:59 PM</strong>
                </div>
                <div class="est-stat-box">
                  <small>Entregado el</small>
                  <strong>{{ $entregaActiva->fecha_entrega?->format('d/m/Y H:i') }}</strong>
                </div>
                <div class="est-stat-box">
                  <small>Estado de revisión</small>
                  <strong>{{ $entregaActiva->estado_revision }}</strong>
                </div>
              </div>

              <div class="est-progress-box">
                <h3>Línea de estado de la entrega</h3>
                <div class="est-stepper">
                  <div class="est-step-node {{ $step1Class }}">
                    <div class="est-step-circle">✓</div>
                    <span class="est-step-label">Entregado</span>
                  </div>
                  <div class="est-step-node {{ $step2Class }}">
                    <div class="est-step-circle">{{ $entregaActiva->estado_revision !== 'Pendiente' ? '✓' : '○' }}</div>
                    <span class="est-step-label">{{ $entregaActiva->estado_revision !== 'Pendiente' ? 'Revisado' : 'En revisión' }}</span>
                  </div>
                  <div class="est-step-node {{ $step3Class }}">
                    <div class="est-step-circle">{{ $step3Icon }}</div>
                    <span class="est-step-label">{{ $step3Label }}</span>
                  </div>
                </div>
              </div>

              <div class="est-detail-split">
                <div style="display:flex;flex-direction:column;gap:18px;">
                  <div class="est-content-card">
                    <h3><svg class="i"><use href="#i-book"/></svg> Consigna de la tarea</h3>
                    <p style="margin:0 0 12px;font-size:14px;color:var(--ink-2);line-height:1.6;">{{ $tareaActiva->descripcion }}</p>
                  </div>

                  <div class="est-content-card">
                    <h3><svg class="i"><use href="#i-video"/></svg> Mi entrega (enlace de video enviado)</h3>
                    <div class="est-material-card" style="margin-bottom:8px;">
                      <div class="est-mat-icon video">
                        <svg class="i" style="width:20px;height:20px;"><use href="#i-video"/></svg>
                      </div>
                      <div class="est-mat-info">
                        <strong>{{ $entregaActiva->video_evidencia }}</strong>
                        <div class="est-mat-meta">
                          <span>Enviado: {{ $entregaActiva->fecha_entrega?->format('d/m/Y H:i') }}</span>
                        </div>
                      </div>
                      <div class="est-mat-actions">
                        <a href="{{ $entregaActiva->video_evidencia }}" target="_blank" rel="noopener" class="btn btn--outline btn--sm">
                          <svg class="i"><use href="#i-eye"/></svg> Abrir video
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <aside class="est-feedback-card">
                  <h3 style="margin:0 0 14px;font:700 16px/1.2 var(--font-display);color:var(--ink);">
                    Retroalimentación del docente
                  </h3>
                  @if ($entregaActiva->estado_revision === 'Pendiente')
                    <div style="background:var(--gold-soft);border:1px solid var(--gold-border);border-radius:12px;padding:16px;color:var(--gold-dark);display:flex;align-items:flex-start;gap:10px;">
                      <svg class="i" style="width:22px;height:22px;flex:none;"><use href="#i-clock"/></svg>
                      <div>
                        <strong style="display:block;margin-bottom:4px;">Tu tarea aún no ha sido revisada</strong>
                        <p style="margin:0;font-size:13.5px;">El docente evaluará tu entrega en un plazo estimado de 48 horas laborales.</p>
                      </div>
                    </div>
                  @else
                    <div class="est-teacher-header">
                      <div class="est-teacher-avatar">PR</div>
                      <div>
                        <strong>{{ $nombreDocente }}</strong>
                        <small>Revisado el {{ $entregaActiva->fecha_revision?->format('d/m/Y H:i') ?? 'Recientemente' }}</small>
                      </div>
                    </div>
                    <blockquote class="est-feedback-text">
                      "{{ $entregaActiva->comentario_docente ?? 'Tarea revisada.' }}"
                    </blockquote>
                  @endif

                  @if ($entregaActiva->estado_revision === 'Observada')
                    <div style="margin-top:16px;">
                      <button
                        type="button"
                        class="btn btn--primary btn--sm"
                        style="width:100%;"
                        wire:click="cambiarVista('tareas', {{ $sesionActiva->id_sesion }})"
                      >
                        Volver a entregar tarea
                      </button>
                    </div>
                  @endif
                </aside>
              </div>
            </div>
          @endif
        @endif
      </div>
    </main>
  </div>

  <!-- ================= MODAL DE VISTA PREVIA DE MATERIAL ================= -->
  <div class="overlay {{ $recursoPreview ? 'is-open' : '' }}" id="estModalPreview" role="dialog" aria-modal="true" aria-labelledby="estPreviewTitle">
    @if ($recursoPreview)
      <div class="dialog">
        <div class="dialog__head">
          <h3 class="dialog__title" id="estPreviewTitle">{{ $recursoPreview->titulo_recurso }}</h3>
          <button type="button" class="dialog__close" wire:click="cerrarPreviewRecurso" aria-label="Cerrar vista previa">
            <svg class="i"><use href="#i-x"/></svg>
          </button>
        </div>
        <div class="dialog__body" id="estPreviewBody">
          @php
            $embedPreview = $recursoPreview->urlReproductor();
            $urlPreview = str_starts_with($recursoPreview->url_recurso, 'http') ? $recursoPreview->url_recurso : asset('storage/' . ltrim($recursoPreview->url_recurso, '/'));
          @endphp
          @if ($embedPreview)
            <div class="reproductor-sesion" style="margin-bottom:16px;">
              <iframe class="video-embebido" src="{{ $embedPreview }}" title="{{ $recursoPreview->titulo_recurso }}" loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
          @elseif (strtoupper((string) ($recursoPreview->tipoRecurso?->nombre_tipo ?? '')) === 'MP4')
            <div class="reproductor-sesion" style="margin-bottom:16px;">
              <video controls preload="metadata" aria-label="{{ $recursoPreview->titulo_recurso }}" src="{{ $urlPreview }}"></video>
            </div>
          @else
            <div style="background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:32px 20px;text-align:center;margin-bottom:16px;">
              <div style="width:54px;height:54px;border-radius:14px;background:var(--blue-soft);color:var(--blue-dark);display:grid;place-items:center;margin:0 auto 12px;">
                <svg class="i" style="width:28px;height:28px;"><use href="#i-file"/></svg>
              </div>
              <strong style="display:block;font-size:16px;color:var(--ink);margin-bottom:4px;">Recurso: {{ $recursoPreview->titulo_recurso }}</strong>
              <small style="color:var(--muted);">Material de estudio oficial Dunamys</small>
            </div>
          @endif
          <div style="font-size:14px;line-height:1.6;color:var(--ink-2);">
            <strong style="display:block;margin-bottom:4px;color:var(--ink);">Descripción de la lección:</strong>
            <p style="margin:0 0 12px;">{{ $recursoPreview->leccion?->descripcion }}</p>
            <span class="pill blue">{{ $recursoPreview->tipoRecurso?->nombre_tipo }}</span>
          </div>
        </div>
        <div class="dialog__foot">
          <button type="button" class="btn btn--outline" wire:click="cerrarPreviewRecurso">Cerrar</button>
          <a
            href="{{ $urlPreview }}"
            target="_blank"
            rel="noopener"
            class="btn btn--primary"
          >
            <svg class="i"><use href="#i-download"/></svg> Abrir / Descargar
          </a>
        </div>
      </div>
    @endif
  </div>

  <x-toast-bridge :mensaje="$mensajeExito" tipo="success" />
</div>
