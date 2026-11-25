<div>
  <header class="page-head">
    <div>
      <h1>Información web <span>institucional</span></h1>
      <p>Publica avisos, edita quiénes somos, horarios de contacto y galería en tiempo real.</p>
    </div>
  </header>

  <x-toast-bridge :mensaje="$toastMensaje" :tipo="$toastTipo" />

  <div class="igrid">
    <section class="card">
      <div class="tabs-bar" id="instTabs">
        <button
          type="button"
          class="tab-btn {{ $pestanaActiva === 'av' ? 'is-active' : '' }}"
          data-itab="av"
          wire:click="cambiarPestana('av')"
          style="display:inline-flex;align-items:center;gap:7px"
        >
          <svg class="i"><use href="#i-megaphone"/></svg>Avisos
        </button>
        <button
          type="button"
          class="tab-btn {{ $pestanaActiva === 'da' ? 'is-active' : '' }}"
          data-itab="da"
          wire:click="cambiarPestana('da')"
          style="display:inline-flex;align-items:center;gap:7px"
        >
          <svg class="i"><use href="#i-building"/></svg>Datos institucionales
        </button>
        <button
          type="button"
          class="tab-btn {{ $pestanaActiva === 'ga' ? 'is-active' : '' }}"
          data-itab="ga"
          wire:click="cambiarPestana('ga')"
          style="display:inline-flex;align-items:center;gap:7px"
        >
          <svg class="i"><use href="#i-image"/></svg>Galería de eventos
        </button>
      </div>

      <div style="padding:20px" id="instPane">
        @if ($pestanaActiva === 'av')
          <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap">
            <input
              class="control"
              id="newAvTitle"
              wire:model.live="titulo_aviso"
              placeholder="Título del aviso (ej. Próximo inicio de ciclo)"
              style="flex:1 1 200px"
              aria-label="Título del aviso"
            >
            <input
              class="control"
              id="newAvText"
              wire:model.live="texto_aviso"
              placeholder="Contenido del aviso..."
              style="flex:2 1 260px"
              aria-label="Contenido del aviso"
            >
            <button type="button" class="btn btn--primary" id="btnPubAv" wire:click="publicarAviso" wire:loading.attr="disabled">
              {{ $editandoAvisoId ? 'Actualizar aviso' : 'Publicar' }}
            </button>
            @if ($editandoAvisoId)
              <button type="button" class="btn btn--ghost" id="btnCancelEditAv" wire:click="cancelarEdicionAviso">Cancelar</button>
            @endif
          </div>

          @error('aviso')
            <p class="error" id="errAv" role="alert" aria-live="polite" style="display:flex;margin-bottom:12px">{{ $message }}</p>
          @enderror

          @foreach ($avisos as $aviso)
            <div wire:key="aviso-{{ $aviso->id_aviso }}" style="border:1px solid var(--line);border-left:4px solid var(--red);border-radius:10px;padding:12px 14px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:10px">
              <div>
                <strong>{{ $aviso->titulo_aviso }}</strong>
                <p style="margin:4px 0 0;font-size:13.5px;color:var(--muted)">{{ $aviso->texto_aviso }}</p>
              </div>
              <div class="actions">
                <button
                  type="button"
                  class="icon-btn"
                  wire:click="editarAviso({{ $aviso->id_aviso }})"
                  aria-label="Editar aviso {{ $aviso->titulo_aviso }}"
                  title="Editar aviso"
                >
                  <svg class="i"><use href="#i-pencil"/></svg>
                </button>
                <button
                  type="button"
                  class="icon-btn danger"
                  wire:click="eliminarAviso({{ $aviso->id_aviso }})"
                  aria-label="Eliminar aviso {{ $aviso->titulo_aviso }}"
                  title="Eliminar aviso"
                >
                  <svg class="i"><use href="#i-trash"/></svg>
                </button>
              </div>
            </div>
          @endforeach
        @elseif ($pestanaActiva === 'da')
          <form wire:submit="guardarDatosInstitucionales" novalidate>
            <div class="field {{ $errors->has('quienes_somos') ? 'is-invalid' : '' }}" id="fldQuienes" style="margin-bottom:16px">
              <label for="inpQuienes">Quiénes somos</label>
              <textarea
                class="control"
                id="inpQuienes"
                rows="4"
                wire:model.live="quienes_somos"
                aria-invalid="{{ $errors->has('quienes_somos') ? 'true' : 'false' }}"
              ></textarea>
              <p class="error" id="errQuienes" role="alert" aria-live="polite">
                {{ $errors->first('quienes_somos') ?: 'Este campo no puede quedar vacío' }}
              </p>
            </div>

            <div class="form-grid" style="margin-bottom:16px">
              <div class="field {{ $errors->has('telefono_contacto') ? 'is-invalid' : '' }}" id="fldTel">
                <label for="inpTel">Celular de contacto</label>
                <input
                  class="control"
                  id="inpTel"
                  wire:model.live="telefono_contacto"
                  aria-invalid="{{ $errors->has('telefono_contacto') ? 'true' : 'false' }}"
                >
                <p class="error" id="errTel" role="alert" aria-live="polite">
                  {{ $errors->first('telefono_contacto') ?: 'Ingresa un número de celular válido' }}
                </p>
              </div>

              <div class="field {{ $errors->has('horario') ? 'is-invalid' : '' }}" id="fldHorWeb">
                <label>Horario de atención</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                  <input
                    class="control"
                    type="time"
                    id="inpApe"
                    wire:model.live="hora_apertura"
                    aria-label="Hora de apertura"
                    aria-invalid="{{ $errors->has('horario') ? 'true' : 'false' }}"
                    style="flex:1 1 110px"
                  >
                  <input
                    class="control"
                    type="time"
                    id="inpCie"
                    wire:model.live="hora_cierre"
                    aria-label="Hora de cierre"
                    aria-invalid="{{ $errors->has('horario') ? 'true' : 'false' }}"
                    style="flex:1 1 110px"
                  >
                </div>
                <p class="error" id="errHorWeb" role="alert" aria-live="polite">
                  {{ $errors->first('horario') ?: 'La hora de cierre debe ser posterior a la de apertura' }}
                </p>
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:16px">
              <div class="field {{ $errors->has('correo_contacto') ? 'is-invalid' : '' }}" id="fldCorreo">
                <label for="inpCorreo">Correo de contacto</label>
                <input class="control" id="inpCorreo" type="email" wire:model.live="correo_contacto">
                @error('correo_contacto')<p class="error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="field {{ $errors->has('direccion') ? 'is-invalid' : '' }}" id="fldDireccion">
                <label for="inpDireccion">Dirección institucional</label>
                <input class="control" id="inpDireccion" type="text" wire:model.live="direccion">
                @error('direccion')<p class="error" role="alert">{{ $message }}</p>@enderror
              </div>
            </div>

            <button type="submit" class="btn btn--primary" id="btnSaveWebDatos" wire:loading.attr="disabled">
              <span wire:loading.remove wire:target="guardarDatosInstitucionales">Guardar</span>
              <span wire:loading wire:target="guardarDatosInstitucionales">Guardando…</span>
            </button>
          </form>
        @else
          <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
            <input
              class="control"
              id="newGalTitle"
              wire:model="titulo_evento"
              placeholder="Título de la foto o video del evento..."
              style="flex:1 1 220px"
              aria-label="Título de la foto o video"
            >
            <select class="control" wire:model="tipo_multimedia" style="width:120px" aria-label="Tipo de multimedia">
              <option value="Foto">Foto</option>
              <option value="Video">Video</option>
            </select>
            <input type="file" wire:model="archivo_galeria" accept=".webp,.jpg,.jpeg,.png,.mp4" style="font-size:12px;max-width:210px">
            <button type="button" class="btn btn--primary" id="btnAddGal" wire:click="agregarGaleria" wire:loading.attr="disabled">
              Subir a galería
            </button>
          </div>

          @error('galeria')
            <p class="error" role="alert" aria-live="polite" style="display:flex;margin-bottom:12px">{{ $message }}</p>
          @enderror

          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">
            @foreach ($galeria as $item)
              <div wire:key="gal-{{ $item->id_galeria }}" style="border:1px solid var(--line);border-radius:10px;padding:14px;background:#FAF8F9;display:flex;justify-content:space-between;align-items:center">
                <div>
                  <strong>{{ $item->titulo_evento }}</strong>
                  <small class="muted">{{ $item->tipo_multimedia }}</small>
                </div>
                <button
                  type="button"
                  class="icon-btn danger"
                  wire:click="eliminarGaleria({{ $item->id_galeria }})"
                  aria-label="Eliminar {{ $item->titulo_evento }} de la galería"
                >
                  <svg class="i"><use href="#i-trash"/></svg>
                </button>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    <section class="card" style="padding:18px">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px">
        <h3 style="margin:0;font:700 17px/1.2 var(--font-display)">Vista previa web pública</h3>
        <span class="live-dot">En vivo</span>
      </div>
      <p class="hint" style="margin:0 0 12px">Se actualiza al instante tras cada cambio guardado.</p>

      <div id="liveWebPreview" style="border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fafafa;padding:14px;font-size:13.5px">
        <div style="background:var(--red);color:#fff;padding:8px 12px;border-radius:8px;margin-bottom:10px;font-size:12.5px;display:flex;align-items:center;gap:8px">
          <svg class="i" style="width:16px;height:16px;flex:none"><use href="#i-megaphone"/></svg>
          <strong>
            @if ($avisos->isNotEmpty())
              {{ $avisos->first()->titulo_aviso }}: {{ $avisos->first()->texto_aviso }}
            @else
              Sin avisos activos
            @endif
          </strong>
        </div>

        <div style="margin-bottom:12px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <h4 style="margin:0;font-size:13.5px;display:inline-flex;align-items:center;gap:6px">
              <svg class="i" style="width:15px;height:15px;color:var(--red)"><use href="#i-megaphone"/></svg>
              Avisos publicados ({{ $avisos->count() }})
            </h4>
          </div>
          <div style="display:grid;gap:6px;max-height:170px;overflow-y:auto">
            @forelse ($avisos as $idx => $av)
              <div wire:key="prev-av-{{ $av->id_aviso }}" style="background:#fff;border:1px solid var(--line);border-left:3px solid {{ $idx === 0 ? 'var(--red)' : 'var(--ink)' }};border-radius:8px;padding:8px 10px">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:6px">
                  <strong style="font-size:13px;color:var(--ink)">{{ $av->titulo_aviso }}</strong>
                  <span style="font-size:11px;color:var(--muted)">{{ $av->fecha_publicacion?->format('d M Y') }}</span>
                </div>
                <p style="margin:3px 0 0;font-size:12.5px;color:var(--muted)">{{ $av->texto_aviso }}</p>
              </div>
            @empty
              <div style="font-size:12.5px;color:var(--muted);padding:6px 0">No hay avisos activos publicados.</div>
            @endforelse
          </div>
        </div>

        <h4 style="margin:0 0 4px;font-size:13.5px;display:inline-flex;align-items:center;gap:6px">
          <svg class="i" style="width:15px;height:15px;color:var(--red)"><use href="#i-building"/></svg>
          Quiénes somos
        </h4>
        <p style="margin:0 0 12px;color:var(--muted);line-height:1.45">
          {{ $infoGuardada?->quienes_somos ?? $quienes_somos }}
        </p>

        <div style="border-top:1px solid var(--line);padding-top:10px;margin-bottom:10px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;font-size:12.5px">
          <span style="display:inline-flex;align-items:center;gap:5px">
            <svg class="i" style="width:14px;height:14px;color:var(--red)"><use href="#i-phone"/></svg>
            <strong>{{ $infoGuardada?->telefono_contacto ?? $telefono_contacto }}</strong>
          </span>
          <span style="display:inline-flex;align-items:center;gap:5px">
            <svg class="i" style="width:14px;height:14px;color:var(--red)"><use href="#i-clock"/></svg>
            Atención: {{ substr((string) ($infoGuardada?->hora_apertura ?? $hora_apertura), 0, 5) }} a {{ substr((string) ($infoGuardada?->hora_cierre ?? $hora_cierre), 0, 5) }}
          </span>
        </div>

        <div style="border-top:1px dashed var(--line);padding-top:8px">
          <strong style="font-size:12.5px;display:inline-flex;align-items:center;gap:5px;margin-bottom:2px">
            <svg class="i" style="width:14px;height:14px;color:var(--red)"><use href="#i-image"/></svg>
            Galería pública ({{ $galeria->count() }} ítems)
          </strong>
          @if ($galeria->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
              @foreach ($galeria as $g)
                <span wire:key="prev-gal-{{ $g->id_galeria }}" class="pill" style="font-size:11.5px;display:inline-flex;align-items:center;gap:5px">
                  <svg class="i" style="width:13px;height:13px"><use href="#i-image"/></svg>
                  {{ $g->titulo_evento }} ({{ $g->tipo_multimedia }})
                </span>
              @endforeach
            </div>
          @else
            <div style="font-size:12px;color:var(--muted)">Sin elementos en galería</div>
          @endif
        </div>
      </div>
    </section>
  </div>
</div>
