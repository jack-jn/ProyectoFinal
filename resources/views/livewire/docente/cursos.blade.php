<div>
  <header class="page-head">
    <div>
      <h1>Cursos <span>disponibles</span></h1>
      <p>Crea, edita y habilita los cursos que se muestran en la página web pública.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn btn--primary" id="btnNewCourse" wire:click="abrirModalCrear">
        <svg class="i"><use href="#i-plus"/></svg>Agregar curso
      </button>
    </div>
  </header>

  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />

  <section class="stats" id="courseStats">
    <div class="stat">
      <span class="stat-label">Total de cursos</span>
      <span class="stat-chip neutral"><svg class="i"><use href="#i-book"/></svg></span>
      <span class="stat-num">{{ $todosLosCursos->count() }}</span>
      <span class="stat-help">Registrados en Dunamys</span>
    </div>
    <div class="stat">
      <span class="stat-label">Cursos habilitados</span>
      <span class="stat-chip green"><svg class="i"><use href="#i-eye"/></svg></span>
      <span class="stat-num">{{ $cursosHabilitados->count() }}</span>
      <span class="stat-help">Visibles en la web pública</span>
    </div>
    <div class="stat">
      <span class="stat-label">Deshabilitados</span>
      <span class="stat-chip gray"><svg class="i"><use href="#i-eye-off"/></svg></span>
      <span class="stat-num">{{ $todosLosCursos->count() - $cursosHabilitados->count() }}</span>
      <span class="stat-help">Ocultos para nuevas matrículas</span>
    </div>
    <div class="stat">
      <span class="stat-label">Alumnos matriculados</span>
      <span class="stat-chip red"><svg class="i"><use href="#i-users"/></svg></span>
      <span class="stat-num">{{ $totalMatriculados }}</span>
      <span class="stat-help">Activos en aula virtual</span>
    </div>
  </section>

  <section class="card toolbar">
    <div class="search">
      <svg class="i"><use href="#i-search"/></svg>
      <input
        class="control"
        type="search"
        id="qCourse"
        wire:model.live="busqueda"
        placeholder="Buscar por nombre, código o instrumento..."
        aria-label="Buscar cursos"
      >
    </div>
    <select class="control" id="filterInstrument" wire:model.live="filtroInstrumento" aria-label="Filtrar por instrumento">
      <option value="all">Todos los instrumentos</option>
      @foreach ($instrumentos as $inst)
        <option value="{{ $inst->nombre_instrumento }}">{{ $inst->nombre_instrumento }}</option>
      @endforeach
    </select>
    <select class="control" id="filterState" wire:model.live="filtroEstado" aria-label="Filtrar por estado">
      <option value="all">Todos los estados</option>
      <option value="on">Habilitados</option>
      <option value="off">Deshabilitados</option>
    </select>
    @if ($busqueda !== '' || $filtroInstrumento !== 'all' || $filtroEstado !== 'all')
      <button type="button" class="link-btn" id="btnClearCourseFilters" wire:click="limpiarFiltros">Limpiar filtros</button>
    @endif
  </section>

  <section class="card">
    <div class="table-wrap">
      <table class="responsive-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre del curso</th>
            <th>Instrumento</th>
            <th>Horario</th>
            <th>Modalidad</th>
            <th class="num">Costo</th>
            <th>Matriculados</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbCourses">
          @foreach ($cursosFiltrados as $curso)
            @php
              $mat = (int) $curso->matriculados_activos_count;
              $pct = $curso->cupos > 0 ? min(100, (int) round(($mat / $curso->cupos) * 100)) : 0;
              $modClass = 'blue';
            @endphp
            <tr wire:key="row-curso-{{ $curso->id_curso }}" data-course-row="{{ $curso->id_curso }}">
              <td class="col-code" data-label="Código">{{ $curso->codigo_curso }}</td>
              <td data-label="Nombre del curso">
                <span class="course-name">{{ $curso->nombre_curso }}</span>
                <small class="muted">Nivel: {{ $curso->nivel }} · {{ $curso->docente?->persona?->nombre_completo ?? 'Prof. Titular' }}</small>
              </td>
              <td data-label="Instrumento">
                <span style="display:inline-flex;align-items:center;gap:6px;font-weight:600">
                  {{ $curso->instrumento?->nombre_instrumento }}
                </span>
              </td>
              <td data-label="Horario"><span class="sched">Acceso 24/7 (Virtual)</span></td>
              <td data-label="Modalidad"><span class="pill {{ $modClass }}">Virtual</span></td>
              <td class="num" data-label="Costo">S/ {{ number_format((float) $curso->costo, 2) }}</td>
              <td data-label="Matriculados">
                <div class="cupos">
                  <span>{{ $mat }} de {{ $curso->cupos }}</span>
                  <span class="meter {{ $mat >= $curso->cupos ? 'is-full' : '' }}"><span style="width:{{ $pct }}%"></span></span>
                </div>
              </td>
              <td data-label="Estado">
                <div class="estado">
                  <button
                    type="button"
                    class="switch"
                    role="switch"
                    aria-checked="{{ $curso->estado_habilitado ? 'true' : 'false' }}"
                    wire:click="alternarEstadoCurso({{ $curso->id_curso }})"
                    aria-label="Habilitar o deshabilitar {{ $curso->nombre_curso }}"
                  ><span></span></button>
                  <div>
                    <span class="estado__txt {{ $curso->estado_habilitado ? 'on' : 'off' }}">
                      {{ $curso->estado_habilitado ? 'Habilitado' : 'Deshabilitado' }}
                    </span>
                    @if (! $curso->estado_habilitado && $mat > 0)
                      <small class="estado__nota">{{ $mat }} alumno(s) conservan su aula virtual</small>
                    @endif
                  </div>
                </div>
              </td>
              <td data-label="Acciones">
                <div class="actions">
                  <button type="button" class="btn btn--soft-red btn--sm" wire:click="irAGestionAula({{ $curso->id_curso }})">
                    Gestionar aula
                  </button>
                  <button
                    type="button"
                    class="icon-btn"
                    wire:click="abrirModalEditar({{ $curso->id_curso }})"
                    aria-label="Editar curso {{ $curso->nombre_curso }}"
                    title="Editar curso"
                  >
                    <svg class="i"><use href="#i-pencil"/></svg>
                  </button>
                  <button
                    type="button"
                    class="icon-btn danger"
                    wire:click="eliminarCurso({{ $curso->id_curso }})"
                    aria-label="Eliminar curso {{ $curso->nombre_curso }}"
                    title="Eliminar curso"
                  >
                    <svg class="i"><use href="#i-trash"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if ($cursosFiltrados->isEmpty())
      <div class="empty" id="emptyCourses">
        <h2>No encontramos cursos</h2>
        <p>Prueba con otra palabra o limpia los filtros seleccionados.</p>
      </div>
    @endif
  </section>

  <!-- Panel en vivo de cursos visibles en la página pública -->
  <section class="card" style="margin-top:22px;padding:20px 24px" aria-label="Vista previa en vivo de la página pública">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
      <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <h2 style="margin:0;font:700 18px/1.2 var(--font-display)">Vista previa de cursos en la página pública</h2>
          <span class="live-dot">En vivo</span>
        </div>
        <p class="hint" style="margin:4px 0 0">Solo se muestran los cursos habilitados para nuevas matrículas.</p>
      </div>
      <span class="pill red" id="inlinePubCount">{{ $cursosHabilitados->count() }} cursos visibles</span>
    </div>
    <div class="public-courses-grid" id="inlinePublicCourses">
      @forelse ($cursosHabilitados as $c)
        @php
          $libres = max(0, $c->cupos - (int) $c->matriculados_activos_count);
          $mClass = 'blue';
        @endphp
        <article class="pub-course-card" wire:key="pub-prev-{{ $c->id_curso }}">
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
              <div style="display:flex;gap:6px;flex-wrap:wrap">
                <span class="pill {{ $mClass }}">Virtual</span>
                <span class="pill">{{ $c->nivel }}</span>
              </div>
              <span class="pill {{ $libres > 0 ? 'green' : 'gold' }}">
                {{ $libres > 0 ? "{$libres} cupos libres" : 'Sin cupos libres' }}
              </span>
            </div>
            <h4>{{ $c->nombre_curso }}</h4>
            <div class="pub-course-card__meta" style="margin-top:6px">
              <span>Instrumento: <strong>{{ $c->instrumento?->nombre_instrumento }}</strong></span>
              <span>Acceso 24/7 (Virtual)</span>
            </div>
          </div>
          <div class="pub-course-card__foot">
            <span class="pub-course-card__price">S/ {{ number_format((float) $c->costo, 2) }} / mes</span>
            <span class="muted" style="font-size:12px">Visible en web</span>
          </div>
        </article>
      @empty
        <div class="empty" style="grid-column:1/-1;padding:24px">
          <p style="margin:0">Ningún curso está habilitado en este momento para la página pública.</p>
        </div>
      @endforelse
    </div>
  </section>

  <!-- ================= MODAL AGREGAR / EDITAR CURSO ================= -->
  <div class="overlay {{ $modalAbierto ? 'is-open' : '' }}" id="modalCourse" aria-hidden="{{ $modalAbierto ? 'false' : 'true' }}">
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="courseModalTitle">
      <div class="dialog__head">
        <img class="brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
        <h2 class="dialog__title" id="courseModalTitle">{{ $editandoCursoId ? 'Editar curso' : 'Agregar curso' }}</h2>
        <span class="code-chip" id="courseModalCode">{{ $codigo_curso }}</span>
        <button type="button" class="icon-btn" wire:click="cerrarModal" aria-label="Cerrar ventana"><svg class="i"><use href="#i-x"/></svg></button>
      </div>
      <form id="formCourse" wire:submit="guardarCurso" novalidate>
        <div class="dialog__body">
          <div class="form-grid">
            <div class="field {{ $errors->has('nombre_curso') ? 'is-invalid' : '' }}" id="fldNombre">
              <label for="fNombre">Nombre del curso *</label>
              <input class="control" id="fNombre" type="text" wire:model="nombre_curso" placeholder="Ej. Teclado – Nivel Básico">
              <p class="error">{{ $errors->first('nombre_curso') ?: 'Este campo es obligatorio' }}</p>
            </div>
            <div class="field" id="fldDocente">
              <label for="fDocente">Docente responsable (Dueño / Admin) *</label>
              <input class="control" id="fDocente" type="text" value="{{ auth()->user()?->persona?->nombre_completo ?? 'Prof. Titular (Dueño / Admin)' }}" disabled>
            </div>

            <div class="field field--full {{ $errors->has('instrumento_nombre') ? 'is-invalid' : '' }}" id="fldInstrumento">
              <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                <span class="label">Instrumento *</span>
                <button
                  type="button"
                  class="btn btn--soft-red btn--sm"
                  id="btnToggleNewInst"
                  wire:click="$toggle('mostrarNuevoInstrumento')"
                  style="min-height:32px;padding:0 12px;font-size:12.5px"
                >
                  <svg class="i"><use href="#i-plus"/></svg> Nuevo instrumento
                </button>
              </div>
              <div class="seg" id="segInstrumento" style="grid-template-columns:repeat(auto-fit,minmax(108px,1fr))">
                @foreach ($instrumentos as $inst)
                  <label wire:key="seg-inst-{{ $inst->id_instrumento }}">
                    <input type="radio" name="instrumento" value="{{ $inst->nombre_instrumento }}" wire:model.live="instrumento_nombre">
                    <span>{{ $inst->nombre_instrumento }}</span>
                  </label>
                @endforeach
              </div>

              @if ($mostrarNuevoInstrumento)
                <div id="boxNewInst" style="margin-top:8px;padding:12px;border:1px dashed var(--red-border);border-radius:10px;background:var(--red-soft)">
                  <label for="inpNewInst" style="display:block;font-size:12px;font-weight:700;color:var(--red-dark);margin-bottom:6px">Nuevo instrumento musical</label>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
                    <input class="control" id="inpNewInst" type="text" wire:model="nuevo_instrumento_nombre" placeholder="Ej. Saxofón, Flauta..." style="flex:1 1 140px;height:38px;font-size:13.5px">
                    <button type="button" class="btn btn--primary btn--sm" wire:click="agregarInstrumento" style="min-height:38px;height:38px;padding:0 12px">Agregar</button>
                    <button type="button" class="btn btn--outline btn--sm" wire:click="$set('mostrarNuevoInstrumento', false)" style="min-height:38px;height:38px;padding:0 10px">Cancelar</button>
                  </div>
                  @error('nuevo_instrumento')
                    <p class="error" style="display:flex;margin-top:6px">{{ $message }}</p>
                  @enderror
                </div>
              @endif
              <p class="error">{{ $errors->first('instrumento_nombre') ?: 'Este campo es obligatorio' }}</p>
            </div>

            <div class="field field--full" id="fldModalidad">
              <span class="label">Modalidad *</span>
              <div class="seg" id="segModalidad" style="grid-template-columns:1fr">
                <span>Acceso 24/7 (Virtual)</span>
              </div>
              <p class="error">Este campo es obligatorio</p>
            </div>

            <div class="field field--full {{ $errors->has('nivel') ? 'is-invalid' : '' }}" id="fldNivel">
              <span class="label">Nivel *</span>
              <div class="seg" id="segNivel">
                <label><input type="radio" name="nivel" value="Básico" wire:model="nivel"><span>Básico</span></label>
                <label><input type="radio" name="nivel" value="Intermedio" wire:model="nivel"><span>Intermedio</span></label>
                <label><input type="radio" name="nivel" value="Avanzado" wire:model="nivel"><span>Avanzado</span></label>
              </div>
              <p class="error">Este campo es obligatorio</p>
            </div>

            <div class="field {{ $errors->has('costo') ? 'is-invalid' : '' }}" id="fldCosto">
              <label for="fCosto">Costo mensual *</label>
              <div class="prefix"><span>S/</span><input class="control" id="fCosto" type="number" min="0" step="0.01" wire:model="costo" placeholder="80.00"></div>
              <p class="error">{{ $errors->first('costo') ?: 'Este campo es obligatorio' }}</p>
            </div>

            <div class="field {{ $errors->has('cupos') ? 'is-invalid' : '' }}" id="fldCupos">
              <label for="fCupos">Cupos (capacidad total) *</label>
              <input class="control" id="fCupos" type="number" min="1" step="1" wire:model="cupos" placeholder="15">
              @if ($matriculadosActuales > 0)
                <p class="hint" id="cuposEditHint">Actualmente hay {{ $matriculadosActuales }} alumno(s) matriculados activos.</p>
              @endif
              <p class="error" id="errCupos">{{ $errors->first('cupos') ?: 'Este campo es obligatorio' }}</p>
            </div>

            <div class="field field--full">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--surface)">
                <div>
                  <strong style="display:block;font-size:13.5px;color:var(--ink)">Publicar en la página web pública</strong>
                  <small class="muted">Solo los cursos habilitados por el docente aparecen en la portada y en el formulario de matrícula.</small>
                </div>
                <div class="estado">
                  <button
                    type="button"
                    class="switch"
                    role="switch"
                    aria-checked="{{ $estado_habilitado ? 'true' : 'false' }}"
                    wire:click="$toggle('estado_habilitado')"
                    aria-label="Habilitar o deshabilitar curso en página pública"
                  ><span></span></button>
                  <span class="estado__txt {{ $estado_habilitado ? 'on' : 'off' }}">
                    {{ $estado_habilitado ? 'Habilitado' : 'Deshabilitado' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="field field--full {{ $errors->has('imagen_curso') ? 'is-invalid' : '' }}">
              <label for="fImagenCurso">Imagen de portada del curso (opcional)</label>
              <input class="control" id="fImagenCurso" type="file" wire:model="imagen_curso" accept=".webp,.jpg,.jpeg,.png" style="padding:7px 12px">
              <p class="hint">Si no subes una imagen, se asignará automáticamente la portada del instrumento elegido (WEBP, JPG o PNG máx. 2 MB).</p>
              @error('imagen_curso')
                <p class="error" style="display:flex">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>
        <div class="dialog__foot">
          <button type="button" class="btn btn--outline" wire:click="cerrarModal">Cancelar</button>
          <button type="submit" class="btn btn--primary" wire:loading.attr="disabled">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
