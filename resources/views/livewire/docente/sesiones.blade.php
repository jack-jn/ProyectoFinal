<div>
  <header class="page-head">
    <div>
      <h1>Gestión de <span>cursos</span></h1>
      <p>Organiza las sesiones secuenciales, publica lecciones y califica las tareas subidas.</p>
    </div>
    <div class="head-actions">
      @if ($modoWorkspace)
        <button type="button" class="btn btn--outline" id="btnToggleCatalogMode" wire:click="volverAlCatalogo">
          <svg class="i"><use href="#i-left"/></svg><span id="lblToggleCatalog">Volver al catálogo de aulas</span>
        </button>
      @endif
    </div>
  </header>

  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />

  @if (! $modoWorkspace)
    <!-- Sub-vista 2A: Catálogo de Cursos agrupado por Instrumento -->
    <section class="catalog-box" id="catalogView">
      <div class="catalog-head">
        <div>
          <h2>Elige un curso para gestionar su aula virtual</h2>
          <p>Organiza sesiones secuenciales, sube recursos (MP4, PDF/Partitura o URL) y revisa las entregas de tareas.</p>
        </div>
        <span class="pill red" id="catalogTotalBadge">{{ $cursos->count() }} cursos</span>
      </div>

      <div id="catalogGroups">
        @foreach ($instrumentos as $inst)
          @php
            $cursosInst = $cursos->where('id_instrumento', $inst->id_instrumento);
          @endphp
          @if ($cursosInst->isNotEmpty())
            <div class="inst-group" wire:key="cat-inst-{{ $inst->id_instrumento }}">
              <div class="inst-group__header">
                <span class="inst-badge-icon" aria-hidden="true">🎵</span>
                <h3>{{ $inst->nombre_instrumento }}</h3>
                <span class="inst-group__count">{{ $cursosInst->count() }} {{ $cursosInst->count() === 1 ? 'curso' : 'cursos' }}</span>
              </div>
              <div class="inst-cards">
                @foreach ($cursosInst as $c)
                  @php
                    $totalLecciones = $c->sesiones->sum(fn ($s) => $s->lecciones->count());
                    $mClass = 'blue';
                  @endphp
                  <button
                    type="button"
                    class="course-tile {{ $c->id_curso === $cursoSeleccionadoId ? 'is-selected' : '' }}"
                    wire:key="tile-curso-{{ $c->id_curso }}"
                    wire:click="seleccionarCurso({{ $c->id_curso }})"
                  >
                    <div>
                      <div class="course-tile__top">
                        <span class="course-tile__code">{{ $c->codigo_curso }} · {{ $c->nivel }} · {{ $c->matriculados_activos_count }}/{{ $c->cupos }} inscritos</span>
                        <span class="pill {{ $mClass }}">Virtual</span>
                      </div>
                      <strong>{{ $c->nombre_curso }}</strong>
                    </div>
                    <div class="course-tile__footer">
                      <span style="display:inline-flex;align-items:center;gap:5px">
                        <svg class="i" style="width:14px;height:14px"><use href="#i-book"/></svg>
                        {{ $c->sesiones->count() }} sesiones · {{ $totalLecciones }} lecciones
                      </span>
                      <span class="course-tile__cta">Seleccionar →</span>
                    </div>
                  </button>
                @endforeach
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </section>
  @else
    <!-- Sub-vista 2B: 3 Paneles (Cursos por Instrumento | Sesiones del Curso | Contenido Derecha) -->
    <section id="workspaceView">
      <div class="workspace-layout">
        <!-- Columna 1: Cursos agrupados por instrumento -->
        <aside class="courses-side-panel" aria-label="Cursos por instrumento">
          <div id="wsSideCoursesList">
            @foreach ($instrumentos as $inst)
              @php
                $cursosSide = $cursos->where('id_instrumento', $inst->id_instrumento);
              @endphp
              @if ($cursosSide->isNotEmpty())
                <div class="side-inst-group" wire:key="side-inst-{{ $inst->id_instrumento }}">
                  <h3 class="side-inst-title">{{ $inst->nombre_instrumento }}</h3>
                  <div class="side-course-list">
                    @foreach ($cursosSide as $c)
                      <button
                        type="button"
                        class="side-course-btn {{ $c->id_curso === $cursoActivo?->id_curso ? 'is-active' : '' }}"
                        wire:key="side-curso-{{ $c->id_curso }}"
                        wire:click="seleccionarCurso({{ $c->id_curso }})"
                      >
                        <strong>{{ $c->nombre_curso }}</strong>
                        <small>{{ $c->sesiones->count() }} sesiones · Virtual</small>
                      </button>
                    @endforeach
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        </aside>

        <!-- Columna 2: Plan de Sesiones Secuenciales del Curso Seleccionado -->
        <aside class="sessions-panel">
          <button type="button" class="back-btn" id="btnBackCatalog" wire:click="volverAlCatalogo">
            <svg class="i"><use href="#i-left"/></svg> Elige otro curso
          </button>
          <span class="ws-course-badge" id="wsCourseInstrument">
            {{ mb_strtoupper($cursoActivo?->instrumento?->nombre_instrumento ?? '') }} · VIRTUAL
          </span>
          <h2 id="wsCourseTitle">{{ $cursoActivo?->nombre_curso }}</h2>
          <p class="sessions-panel__hint">
            <span>Usa las flechas para reordenar las sesiones conservando el progreso.</span>
          </p>

          <ul class="session-cards" id="wsSessionList">
            @forelse ($sesionesCurso as $idx => $ses)
              @php
                $esActiva = $sesionActiva && $sesionActiva->id_sesion === $ses->id_sesion;
                $cantEntregas = $ses->tareaAsignada ? $ses->tareaAsignada->entregas->count() : 0;
              @endphp
              <li
                class="session-card {{ $esActiva ? 'is-active' : '' }}"
                wire:key="ses-card-{{ $ses->id_sesion }}"
                wire:click="seleccionarSesion({{ $ses->id_sesion }})"
              >
                <span class="session-step-badge">{{ str_pad((string) $ses->numero_orden, 2, '0', STR_PAD_LEFT) }}</span>
                <div class="session-card__info">
                  <span class="session-card__num">Sesión {{ $ses->numero_orden }}</span>
                  <span class="session-card__title">{{ $ses->titulo_sesion }}</span>
                  <div class="session-card__meta">
                    <span>{{ $ses->lecciones->count() }} lecc.</span>
                    <span>·</span>
                    <span>{{ $cantEntregas }} entreg.</span>
                  </div>
                </div>
                <div class="session-card__ctrl" onclick="event.stopPropagation()">
                  <button
                    type="button"
                    wire:click="abrirModalEditarSesion({{ $ses->id_sesion }})"
                    aria-label="Editar nombre de la sesión {{ $ses->titulo_sesion }}"
                    title="Editar nombre de la sesión"
                  ><svg class="i"><use href="#i-pencil"/></svg></button>
                  <button
                    type="button"
                    wire:click="moverSesion({{ $ses->id_sesion }}, -1)"
                    aria-label="Subir sesión {{ $ses->titulo_sesion }}"
                    title="Subir sesión"
                    @disabled($idx === 0)
                  ><svg class="i"><use href="#i-up"/></svg></button>
                  <button
                    type="button"
                    wire:click="moverSesion({{ $ses->id_sesion }}, 1)"
                    aria-label="Bajar sesión {{ $ses->titulo_sesion }}"
                    title="Bajar sesión"
                    @disabled($idx === $sesionesCurso->count() - 1)
                  ><svg class="i"><use href="#i-down"/></svg></button>
                  <button
                    type="button"
                    wire:click="eliminarSesion({{ $ses->id_sesion }})"
                    aria-label="Eliminar sesión {{ $ses->titulo_sesion }}"
                    title="Eliminar sesión"
                    style="color:var(--red)"
                  ><svg class="i"><use href="#i-trash"/></svg></button>
                </div>
              </li>
            @empty
              <li style="list-style:none;padding:22px 14px;text-align:center;border:1.5px dashed var(--line);border-radius:14px;background:var(--surface)">
                <strong style="display:block;font-size:14px;margin-bottom:4px">Este curso aún no tiene sesiones</strong>
                <span class="hint">Agrega la primera sesión de clase para publicar lecciones y tareas.</span>
              </li>
            @endforelse
          </ul>

          <button type="button" class="btn btn--soft-red btn--sm" id="btnAddSession" wire:click="abrirModalCrearSesion" style="margin-top:16px;width:100%">
            <svg class="i"><use href="#i-plus"/></svg> Nueva sesión de clase
          </button>
        </aside>

        <!-- Columna 3: Carpeta con Pestañas Superiores ("Lecciones" | "Tareas") y Contenido -->
        <div class="ws-folder-wrap">
          <div class="ws-folder-tabs" role="tablist">
            <button
              type="button"
              class="seg-tab {{ $subPestana === 'sesiones' ? 'is-active' : '' }}"
              id="tabBtnSesiones"
              wire:click="cambiarSubPestana('sesiones')"
            >
              Lecciones <span class="badge-cnt" id="cntLecciones">{{ $sesionActiva ? $sesionActiva->lecciones->count() : 0 }}</span>
            </button>
            <button
              type="button"
              class="seg-tab {{ $subPestana === 'tareas' ? 'is-active' : '' }}"
              id="tabBtnTareas"
              wire:click="cambiarSubPestana('tareas')"
            >
              Tareas <span class="badge-cnt" id="cntTareas">{{ $sesionActiva?->tareaAsignada ? $sesionActiva->tareaAsignada->entregas->count() : 0 }}</span>
            </button>
          </div>

          <div class="ws-main-card">
            <div class="ws-top-bar">
              <div class="ws-top-bar__title">
                <h3 id="wsSessionHeading">
                  @if ($sesionActiva)
                    Sesión {{ $sesionActiva->numero_orden }}: {{ $sesionActiva->titulo_sesion }}
                  @else
                    Sin sesiones registradas
                  @endif
                </h3>
              </div>
              @if ($sesionActiva && $subPestana === 'sesiones')
                <div id="wsTopActionArea">
                  <button type="button" class="btn btn--primary" id="btnCreateLesson" wire:click="abrirModalCrearLeccion">
                    <svg class="i"><use href="#i-plus"/></svg> Crear lección
                  </button>
                </div>
              @endif
            </div>

            <div class="ws-body">
              @if (! $sesionActiva)
                <div class="empty" style="background:var(--surface);border:2px dashed #D5CFD2;border-radius:16px">
                  <div class="empty__icon"><svg class="i" style="width:26px;height:26px"><use href="#i-book"/></svg></div>
                  <b>Este curso aún no tiene sesiones configuradas</b>
                  <p>Crea la primera sesión en la columna izquierda para empezar a publicar lecciones y tareas.</p>
                  <button type="button" class="btn btn--primary" wire:click="abrirModalCrearSesion">
                    <svg class="i"><use href="#i-plus"/></svg> Crear primera sesión
                  </button>
                </div>
              @elseif ($subPestana === 'sesiones')
                <!-- PESTAÑA 1: LECCIONES Y MATERIAL DE ESTUDIO -->
                <div id="paneWsSesiones">
                  <div class="lessons-list" id="wsLessonsList">
                    @forelse ($sesionActiva->lecciones as $lec)
                      <article class="lesson-card" wire:key="lec-card-{{ $lec->id_leccion }}">
                        <div class="lesson-card__top">
                          <div class="lesson-card__badges">
                            <span class="lesson-code-pill">{{ $lec->codigo_leccion }}</span>
                          </div>
                          <div class="actions">
                            <button type="button" class="btn btn--outline btn--sm" wire:click="abrirModalEditarLeccion({{ $lec->id_leccion }})">
                              <svg class="i"><use href="#i-pencil"/></svg> Editar lección
                            </button>
                            <button type="button" class="icon-btn danger" wire:click="eliminarLeccion({{ $lec->id_leccion }})" aria-label="Eliminar lección {{ $lec->titulo_leccion }}">
                              <svg class="i"><use href="#i-trash"/></svg>
                            </button>
                          </div>
                        </div>
                        <div class="lesson-info">
                          <h5>{{ $lec->titulo_leccion }}</h5>
                          <p>{{ $lec->descripcion }}</p>
                          <x-recursos-sesion :recursos="$lec->recursos" :titulo="$lec->titulo_leccion" />
                        </div>
                      </article>
                    @empty
                      <div class="empty" style="background:var(--surface);border:2px dashed #D5CFD2;border-radius:16px">
                        <div class="empty__icon"><svg class="i" style="width:26px;height:26px"><use href="#i-video"/></svg></div>
                        <b>Aún no hay lecciones publicadas en esta sesión</b>
                        <p>Publica el primer video demostrativo, guía en PDF, partitura o enlace de referencia para tus alumnos.</p>
                        <button type="button" class="btn btn--primary" wire:click="abrirModalCrearLeccion">
                          <svg class="i"><use href="#i-plus"/></svg> Publicar primera lección
                        </button>
                      </div>
                    @endforelse
                  </div>
                </div>
              @else
                <!-- PESTAÑA 2: TAREAS Y ENTREGAS DE ALUMNOS -->
                <div id="paneWsTareas">
                  <div id="wsTaskAssignBox">
                    @if (! $sesionActiva->tareaAsignada)
                      <div class="empty" style="background:linear-gradient(180deg,#FFF8F8 0%,#FFFFFF 100%);border:2px dashed var(--red-border);border-radius:16px;margin-bottom:26px">
                        <div class="empty__icon"><svg class="i" style="width:26px;height:26px"><use href="#i-file-text"/></svg></div>
                        <b>Aún no has creado tareas para esta sesión</b>
                        <p>Asigna una práctica o ejercicio para que tus alumnos suban su evidencia.</p>
                        <button type="button" class="btn btn--primary" id="btnOpenCreateTask" wire:click="abrirModalTarea">
                          <svg class="i"><use href="#i-plus"/></svg> Crear tarea
                        </button>
                      </div>
                    @else
                      @php
                        $tarea = $sesionActiva->tareaAsignada;
                      @endphp
                      <div class="task-featured-card">
                        <div class="task-featured-head">
                          <div class="task-featured-title">
                            <small>📌 CONSIGNA DE TAREA ACTIVA {{ $tarea->fecha_limite ? '· Límite: '.$tarea->fecha_limite->format('d/m/Y') : '' }}</small>
                            <h4>{{ $tarea->titulo_tarea }}</h4>
                          </div>
                          <div class="actions">
                            <button type="button" class="btn btn--outline btn--sm" id="btnOpenEditTask" wire:click="abrirModalTarea">
                              <svg class="i"><use href="#i-pencil"/></svg> Editar tarea
                            </button>
                            <button type="button" class="icon-btn danger" id="btnDeleteTask" wire:click="eliminarTarea" aria-label="Eliminar tarea asignada">
                              <svg class="i"><use href="#i-trash"/></svg>
                            </button>
                          </div>
                        </div>
                        <div class="task-featured-grid no-media">
                          <div>
                            <div class="task-instructions-box">
                              <strong>Descripción e indicaciones para el alumno</strong>
                              {{ $tarea->descripcion }}
                            </div>
                            <x-recursos-sesion :recursos="$tarea->recursos" :titulo="$tarea->titulo_tarea" />
                          </div>
                        </div>
                      </div>
                    @endif
                  </div>

                  <livewire:docente.entregas :id-sesion="$sesionActiva->id_sesion" :key="'entregas-'.$sesionActiva->id_sesion" />
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif

  <!-- ================= MODAL CREAR / EDITAR SESIÓN ================= -->
  <div class="overlay {{ $modalSesionAbierto ? 'is-open' : '' }}" id="modalNewSession" aria-hidden="{{ $modalSesionAbierto ? 'false' : 'true' }}">
    <div class="dialog dialog--sm" role="dialog" aria-modal="true" aria-labelledby="sessionModalTitle">
      <div class="dialog__head">
        <h2 class="dialog__title" id="sessionModalTitle">{{ $editandoSesionId ? 'Editar nombre de la sesión' : 'Agregar nueva sesión' }}</h2>
        <button type="button" class="icon-btn" wire:click="$set('modalSesionAbierto', false)" aria-label="Cerrar ventana"><svg class="i"><use href="#i-x"/></svg></button>
      </div>
      <form id="formNewSession" wire:submit="guardarSesion" novalidate>
        <div class="dialog__body">
          <div class="field {{ $errors->has('titulo_sesion') ? 'is-invalid' : '' }}" id="fldNewSesTitle">
            <label for="newSesTitle">Título de la sesión *</label>
            <input class="control" id="newSesTitle" type="text" wire:model="titulo_sesion" placeholder="Ej. Postura corporal y escala de Do Mayor">
            <p class="error">{{ $errors->first('titulo_sesion') ?: 'Este campo es obligatorio' }}</p>
          </div>
        </div>
        <div class="dialog__foot">
          <button type="button" class="btn btn--outline" wire:click="$set('modalSesionAbierto', false)">Cancelar</button>
          <button type="submit" class="btn btn--primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL CREAR / EDITAR LECCIÓN ================= -->
  <div class="overlay {{ $modalLeccionAbierto ? 'is-open' : '' }}" id="modalLesson" aria-hidden="{{ $modalLeccionAbierto ? 'false' : 'true' }}">
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="lessonModalTitle">
      <div class="dialog__head">
        <h2 class="dialog__title" id="lessonModalTitle">{{ $editandoLeccionId ? 'Editar lección' : 'Crear lección' }}</h2>
        <span class="code-chip">{{ $sesionActiva?->titulo_sesion ?? 'Sesión' }}</span>
        <button type="button" class="icon-btn" wire:click="$set('modalLeccionAbierto', false)" aria-label="Cerrar ventana"><svg class="i"><use href="#i-x"/></svg></button>
      </div>
      <form id="formLesson" wire:submit="guardarLeccion" novalidate>
        <div class="dialog__body">
          <div class="form-grid">
            <div class="field field--full {{ $errors->has('titulo_leccion') ? 'is-invalid' : '' }}" id="fldLesTitle">
              <label for="lesTitle">Título de la lección *</label>
              <input class="control" id="lesTitle" type="text" wire:model="titulo_leccion" placeholder="Ej. Notas naturales y postura de manos">
              <p class="error">{{ $errors->first('titulo_leccion') ?: 'Este campo es obligatorio' }}</p>
            </div>
            <div class="field field--full {{ $errors->has('descripcion_leccion') ? 'is-invalid' : '' }}" id="fldLesDesc">
              <label for="lesDesc">Descripción *</label>
              <textarea class="control" id="lesDesc" rows="3" wire:model="descripcion_leccion" placeholder="Describe qué aprenderá el alumno en esta lección..."></textarea>
              <p class="error">{{ $errors->first('descripcion_leccion') ?: 'Este campo es obligatorio' }}</p>
            </div>

            <div class="field field--full {{ $errors->has('url_video_leccion') ? 'is-invalid' : '' }}">
              <label for="lesVideo">Enlace del video de la lección (YouTube o Vimeo)@unless ($tieneMp4Leccion) *@endunless</label>
              <input class="control" id="lesVideo" type="url" wire:model="url_video_leccion"
                placeholder="https://www.youtube.com/watch?v=... o https://vimeo.com/..." @required(! $tieneMp4Leccion)>
              <p class="hint">Pega el enlace de YouTube o Vimeo; se reproducirá directamente en la lección.</p>
              @if ($tieneMp4Leccion)
                <p class="hint">Puedes conservar el MP4 existente dejando el enlace vacío.</p>
              @endif
              @error('url_video_leccion') <p class="error" style="display:flex">{{ $message }}</p> @enderror
            </div>

            @if ($modalLeccionAbierto)
              @include('livewire.docente.recursos-apoyo', ['prefijo' => 'les', 'sinAdjuntos' => 'Solo video'])
            @endif
          </div>
        </div>
        <div class="dialog__foot">
          <button type="button" class="btn btn--outline" wire:click="$set('modalLeccionAbierto', false)">Cancelar</button>
          <button type="submit" class="btn btn--primary" wire:loading.attr="disabled">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL CREAR / EDITAR TAREA ================= -->
  <div class="overlay {{ $modalTareaAbierto ? 'is-open' : '' }}" id="modalTask" aria-hidden="{{ $modalTareaAbierto ? 'false' : 'true' }}">
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
      <div class="dialog__head">
        <h2 class="dialog__title" id="taskModalTitle">{{ $sesionActiva?->tareaAsignada ? 'Editar tarea de la sesión' : 'Asignar tarea de la sesión' }}</h2>
        <span class="code-chip">Aula Virtual</span>
        <button type="button" class="icon-btn" wire:click="$set('modalTareaAbierto', false)" aria-label="Cerrar ventana"><svg class="i"><use href="#i-x"/></svg></button>
      </div>
      <form id="formTask" wire:submit="guardarTarea" novalidate>
        <div class="dialog__body">
          <div class="form-grid">
            <div class="field field--full {{ $errors->has('titulo_tarea') ? 'is-invalid' : '' }}" id="fldTaskTitle">
              <label for="tskTitle">Título de la tarea *</label>
              <input class="control" id="tskTitle" type="text" wire:model="titulo_tarea" placeholder="Ej. Escalas mayores y menores">
              <p class="error">{{ $errors->first('titulo_tarea') ?: 'Este campo es obligatorio' }}</p>
            </div>
            <div class="field field--full {{ $errors->has('descripcion_tarea') ? 'is-invalid' : '' }}" id="fldTaskDesc">
              <label for="tskDesc">Descripción / indicaciones para el alumno *</label>
              <textarea class="control" id="tskDesc" rows="3" wire:model="descripcion_tarea" placeholder="Indica los ejercicios, tempo sugerido y cómo debe grabarse el alumno..."></textarea>
              <p class="error">{{ $errors->first('descripcion_tarea') ?: 'Este campo es obligatorio' }}</p>
            </div>
            <div class="field {{ $errors->has('fecha_limite_tarea') ? 'is-invalid' : '' }}">
              <label for="tskDeadline">Fecha límite de entrega</label>
              <input class="control" id="tskDeadline" type="date" wire:model="fecha_limite_tarea">
              @error('fecha_limite_tarea') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="field field--full {{ $errors->has('url_video_tarea') ? 'is-invalid' : '' }}">
              <label for="tskVideo">Enlace del video guía de la tarea (YouTube o Vimeo)</label>
              <input class="control" id="tskVideo" type="url" wire:model="url_video_tarea"
                placeholder="https://www.youtube.com/watch?v=... o https://vimeo.com/...">
              <p class="hint">Opcional. Si agregas un enlace de YouTube o Vimeo, se reproducirá directamente en la tarea.</p>
              @error('url_video_tarea') <p class="error" style="display:flex">{{ $message }}</p> @enderror
            </div>

            @if ($modalTareaAbierto)
              @include('livewire.docente.recursos-apoyo', ['prefijo' => 'tsk', 'sinAdjuntos' => 'Solo video / Sin adjuntos'])
            @endif
          </div>
        </div>
        <div class="dialog__foot">
          <button type="button" class="btn btn--outline" wire:click="$set('modalTareaAbierto', false)">Cancelar</button>
          <button type="submit" class="btn btn--primary" wire:loading.attr="disabled">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
