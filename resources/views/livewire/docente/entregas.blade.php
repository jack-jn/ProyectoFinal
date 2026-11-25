<div>
  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />

  <div class="subs-section-head" id="wsSubsHead">
    <div>
      <h4>
        Tareas subidas por los alumnos
        <span class="pill dark" id="wsSubsCountPill">{{ $entregas->count() }} {{ $entregas->count() === 1 ? 'entrega' : 'entregas' }}</span>
      </h4>
      <p class="hint" id="wsSubsSummary">Revisa el video de evidencia, escribe un comentario y aprueba u observa la entrega.</p>
    </div>
  </div>

  <div class="subs-list" id="wsSubsList">
    @if ($entregas->isEmpty())
      <div class="empty" style="background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:36px 20px">
        <b>Aún no hay tareas entregadas para esta sesión</b>
        <p>Cuando los alumnos suban su video de práctica desde el aula virtual, aparecerán aquí para su revisión.</p>
      </div>
    @else
      @foreach ($entregas as $ent)
        @php
          $nombreAlumno = $ent->detalleMatricula?->matricula?->alumno?->persona?->nombre_completo ?? 'Alumno';
          $iniciales = collect(explode(' ', $nombreAlumno))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
          $pillClass = $ent->estado_revision === 'Aprobada' ? 'green' : ($ent->estado_revision === 'Observada' ? 'red' : 'gold');
          $errKey = 'comentario_'.$ent->id_detalle_entrega;
        @endphp
        <article class="sub-card" wire:key="ent-{{ $ent->id_detalle_entrega }}" data-ent-id="{{ $ent->id_detalle_entrega }}">
          <div class="sub-card__top">
            <div class="sub-student-profile">
              <div class="stu-avatar">{{ mb_strtoupper($iniciales ?: 'AL') }}</div>
              <div>
                <strong>{{ $nombreAlumno }}</strong>
                <span>Entregado el {{ $ent->fecha_entrega?->format('d/m/Y H:i') }} · Enlace: <a href="{{ $ent->video_evidencia }}" target="_blank" rel="noopener noreferrer" style="color:var(--red);font-weight:600;text-decoration:underline">{{ $ent->video_evidencia }}</a></span>
              </div>
            </div>
            <span class="pill {{ $pillClass }}">
              {{ $ent->estado_revision === 'Aprobada' ? '✔ Aprobada' : ($ent->estado_revision === 'Observada' ? '⚠️ Observada' : '⏳ Pendiente') }}
            </span>
          </div>

          <div class="sub-card__grid">
            <!-- Reproductor / Enlace de video de la entrega del alumno -->
            <div class="media-player">
              <a href="{{ $ent->video_evidencia }}" target="_blank" rel="noopener noreferrer" class="media-player__screen" style="text-decoration:none">
                <span class="media-player__badge">EVIDENCIA ALUMNO</span>
                <div class="media-play-btn">▶</div>
                <span class="media-player__filename">{{ $ent->video_evidencia }}</span>
              </a>
              <div class="media-player__bar">
                <span>🔗 Enlace de video de práctica</span>
              </div>
            </div>

            <!-- Dos botones claros: Aprobar | Observar -->
            <div class="rating-box" role="group" aria-label="Revisión de tarea para {{ $nombreAlumno }}">
              <span class="rating-box__label">Decisión de revisión</span>
              <button
                type="button"
                class="rate-btn rate-btn--approve {{ $ent->estado_revision === 'Aprobada' ? 'is-active' : '' }}"
                wire:click="calificarEntrega({{ $ent->id_detalle_entrega }}, 'Aprobada')"
              >
                <svg class="i"><use href="#i-check"/></svg> Aprobar
              </button>
              <button
                type="button"
                class="rate-btn rate-btn--observe {{ $ent->estado_revision === 'Observada' ? 'is-active' : '' }}"
                wire:click="calificarEntrega({{ $ent->id_detalle_entrega }}, 'Observada')"
              >
                <svg class="i"><use href="#i-alert"/></svg> Observar
              </button>
            </div>

            <!-- Comentario para el alumno -->
            <div class="comment-col {{ $errors->has($errKey) ? 'is-invalid' : '' }}" id="commWrap_{{ $ent->id_detalle_entrega }}">
              <div>
                <label for="comm_{{ $ent->id_detalle_entrega }}">
                  <span>Comentario para el alumno</span>
                  <small class="muted">Opcional al aprobar · Obligatorio al observar (mín. 5 car.)</small>
                </label>
                <textarea
                  id="comm_{{ $ent->id_detalle_entrega }}"
                  wire:model="comentarios.{{ $ent->id_detalle_entrega }}"
                  placeholder="Escribe aquí tu retroalimentación para el alumno..."
                ></textarea>
                <p class="error" id="commErr_{{ $ent->id_detalle_entrega }}">
                  {{ $errors->first($errKey) ?: 'Este campo es obligatorio' }}
                </p>
              </div>
              <div class="comment-col__foot">
                <span class="comment-col__status">
                  @if ($ent->estado_revision === 'Pendiente')
                    El comentario se guarda al presionar Aprobar u Observar.
                  @elseif ($ent->comentario_docente)
                    ✔ Visible en el aula virtual de {{ $nombreAlumno }}: "{{ $ent->comentario_docente }}"
                  @else
                    ✔ Estado "{{ $ent->estado_revision }}" visible en el aula virtual de {{ $nombreAlumno }}
                  @endif
                </span>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    @endif
  </div>
</div>
