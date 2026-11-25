@props(['recursos', 'titulo'])

@php
    $videoPrincipal = null;
    $videosApoyo = [];
    $adjuntosApoyo = [];

    foreach ($recursos as $recurso) {
        $original = trim($recurso->url_recurso);
        $esExterno = in_array(strtolower((string) parse_url($original, PHP_URL_SCHEME)), ['http', 'https'], true);
        $ruta = ltrim($original, '/');
        $archivo = $esExterno ? $original : asset(str_starts_with($ruta, 'storage/') ? $ruta : 'storage/'.$ruta);
        $embed = $recurso->urlReproductor();
        $esMp4 = $recurso->tipoRecurso?->nombre_tipo === 'MP4'
            && str_ends_with(strtolower((string) parse_url($original, PHP_URL_PATH)), '.mp4');

        if ($embed || $esMp4) {
            if ($videoPrincipal === null && $recurso->titulo_recurso !== 'Enlace de apoyo') {
                $videoPrincipal = compact('recurso', 'archivo', 'embed', 'esMp4');
            } else {
                $videosApoyo[] = compact('recurso', 'archivo', 'embed', 'esMp4');
            }
        } else {
            $adjuntosApoyo[] = compact('recurso', 'archivo');
        }
    }
@endphp

@if ($videoPrincipal)
    <div class="reproductor-sesion" wire:key="reproductor-{{ $videoPrincipal['recurso']->id_recurso }}">
        @if ($videoPrincipal['embed'])
            <iframe
                class="video-embebido"
                src="{{ $videoPrincipal['embed'] }}"
                title="{{ $titulo }}"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>
        @else
            <video controls preload="metadata" aria-label="{{ $titulo }}" src="{{ $videoPrincipal['archivo'] }}"></video>
        @endif
    </div>
@endif

@if ($adjuntosApoyo || $videosApoyo)
    <div class="material-apoyo-bloque">
        <h6 class="material-apoyo-titulo">Material de apoyo</h6>

        @if ($adjuntosApoyo)
            <div class="adjuntos-sesion">
                @foreach ($adjuntosApoyo as $item)
                    @php
                        $recurso = $item['recurso'];
                        $archivo = $item['archivo'];
                    @endphp
                    <a class="btn btn--outline btn--sm" wire:key="adjunto-{{ $recurso->id_recurso }}"
                        href="{{ $archivo }}" target="_blank" rel="noopener noreferrer"
                        @if ($recurso->tipoRecurso?->nombre_tipo === 'PDF') download @endif>
                        {{ $recurso->titulo_recurso }}
                    </a>
                @endforeach
            </div>
        @endif

        @foreach ($videosApoyo as $item)
            @php
                $recurso = $item['recurso'];
            @endphp
            <div class="material-apoyo-video" wire:key="apoyo-video-{{ $recurso->id_recurso }}">
                @if ($recurso->titulo_recurso)
                    <p class="material-apoyo-subtitulo">{{ $recurso->titulo_recurso }}</p>
                @endif
                <div class="reproductor-sesion">
                    @if ($item['embed'])
                        <iframe
                            class="video-embebido"
                            src="{{ $item['embed'] }}"
                            title="{{ $recurso->titulo_recurso ?: $titulo }}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                        ></iframe>
                    @else
                        <video controls preload="metadata" aria-label="{{ $recurso->titulo_recurso ?: $titulo }}" src="{{ $item['archivo'] }}"></video>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
