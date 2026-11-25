<div class="field field--full" wire:key="apoyo-{{ $prefijo }}">
  <span class="label">Recurso adicional de apoyo (opcional)</span>
  <div class="seg" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr))">
    <label><input type="radio" name="{{ $prefijo }}ExtraType" value="Ninguno" wire:model.live="tipo_recurso_extra"><span>{{ $sinAdjuntos }}</span></label>
    <label><input type="radio" name="{{ $prefijo }}ExtraType" value="Partitura" wire:model.live="tipo_recurso_extra"><span>🎼 Partitura / PDF</span></label>
    <label><input type="radio" name="{{ $prefijo }}ExtraType" value="Enlace de referencia" wire:model.live="tipo_recurso_extra"><span>🔗 Enlace web</span></label>
    <label><input type="radio" name="{{ $prefijo }}ExtraType" value="Ambos" wire:model.live="tipo_recurso_extra"><span>🎼 + 🔗 Ambos</span></label>
  </div>
</div>

@if (in_array($tipo_recurso_extra, ['Partitura', 'Ambos'], true))
  <div class="field field--full {{ $errors->has('archivo_pdf') ? 'is-invalid' : '' }}" wire:key="pdf-apoyo-{{ $prefijo }}"
    x-data="{ arrastrando: false, subiendo: false, errorPdf: false }"
    x-on:livewire-upload-start="subiendo = true; errorPdf = false"
    x-on:livewire-upload-finish="subiendo = false"
    x-on:livewire-upload-cancel="subiendo = false"
    x-on:livewire-upload-error="subiendo = false; errorPdf = true">
    <label for="{{ $prefijo }}Pdf">Partitura o guía en PDF adjunta (máx. 10 MB) @unless ($tienePdfGuardado)*@endunless</label>
    <input id="{{ $prefijo }}Pdf" x-ref="pdf" type="file" class="sr-only apoyo-pdf__input" tabindex="-1" wire:model="archivo_pdf" accept=".pdf,application/pdf"
      x-on:change.capture="
        const archivo = $event.target.files[0];
        if (archivo && (!archivo.name.toLowerCase().endsWith('.pdf') || archivo.size > 10 * 1024 * 1024)) {
          $event.stopImmediatePropagation();
          $event.target.value = '';
          errorPdf = true;
        }
      ">

    <button type="button" class="apoyo-pdf__zona" x-bind:class="{ 'is-dragging': arrastrando }"
      x-bind:disabled="subiendo" x-on:click="$refs.pdf.click()"
      x-on:dragover.prevent="if (!subiendo) arrastrando = true"
      x-on:dragleave.self="arrastrando = false"
      x-on:drop.prevent.stop="
        arrastrando = false;
        if (subiendo || !$event.dataTransfer.files.length) return;
        if ($event.dataTransfer.files.length !== 1) { errorPdf = true; return; }
        $refs.pdf.files = $event.dataTransfer.files;
        $refs.pdf.dispatchEvent(new Event('change', { bubbles: true }));
      ">
      <svg class="i" aria-hidden="true"><use href="#i-file-text"/></svg>
      <strong>Arrastra y suelta tu archivo PDF aquí</strong>
      <span>o haz clic para seleccionar desde tu equipo (PDF, máx. 10 MB)</span>
    </button>

    <p class="hint" wire:loading wire:target="archivo_pdf" role="status">Cargando PDF…</p>

    @if ($archivo_pdf)
      <div class="apoyo-pdf__archivo">
        <div>
          <strong>{{ $archivo_pdf->getClientOriginalName() }}</strong>
          <span>{{ number_format($archivo_pdf->getSize() / 1048576, 2) }} MB</span>
        </div>
        <button class="btn btn--outline btn--sm" type="button" wire:click="quitarPdfTemporal"
          x-on:click="errorPdf = false; $refs.pdf.value = ''" x-bind:disabled="subiendo"
          wire:loading.attr="disabled" wire:target="quitarPdfTemporal">Quitar</button>
      </div>
    @endif

    @if ($tienePdfGuardado)
      <p class="hint"><strong>PDF guardado:</strong> {{ $nombrePdfGuardado }}. Puedes seleccionar un nuevo PDF para reemplazarlo.</p>
    @endif

    <p class="apoyo-pdf__error" x-cloak x-show="errorPdf" role="alert">Formato o tamaño de archivo no permitido</p>
    @error('archivo_pdf')
      <p class="apoyo-pdf__error" x-show="!errorPdf" role="alert">{{ $message }}</p>
    @enderror
  </div>
  <div class="field field--full {{ $errors->has('titulo_pdf') ? 'is-invalid' : '' }}">
    <label for="{{ $prefijo }}TituloPdf">Título del PDF (opcional)</label>
    <input class="control" id="{{ $prefijo }}TituloPdf" type="text" maxlength="255" wire:model="titulo_pdf">
    @error('titulo_pdf') <p class="error">{{ $message }}</p> @enderror
  </div>
@endif

@if (in_array($tipo_recurso_extra, ['Enlace de referencia', 'Ambos'], true))
  <div class="field field--full {{ $errors->has('url_apoyo') ? 'is-invalid' : '' }}">
    <label for="{{ $prefijo }}UrlApoyo">Enlace web de apoyo *</label>
    <input class="control" id="{{ $prefijo }}UrlApoyo" type="url" wire:model="url_apoyo" placeholder="https://ejemplo.com/material-de-apoyo">
    @error('url_apoyo') <p class="error">{{ $message }}</p> @enderror
  </div>
  <div class="field field--full {{ $errors->has('titulo_apoyo') ? 'is-invalid' : '' }}">
    <label for="{{ $prefijo }}TituloApoyo">Título del enlace de apoyo (opcional)</label>
    <input class="control" id="{{ $prefijo }}TituloApoyo" type="text" maxlength="255" wire:model="titulo_apoyo">
    @error('titulo_apoyo') <p class="error">{{ $message }}</p> @enderror
  </div>
@endif
