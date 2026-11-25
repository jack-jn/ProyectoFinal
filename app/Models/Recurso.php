<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recurso extends Model
{
    protected $table = 'recursos';

    protected $primaryKey = 'id_recurso';

    protected $fillable = [
        'id_tipo_recurso',
        'id_leccion',
        'id_tarea',
        'titulo_recurso',
        'url_recurso',
        'duracion',
        'tamano_mb',
    ];

    protected function casts(): array
    {
        return [
            'duracion' => 'integer',
            'tamano_mb' => 'decimal:2',
        ];
    }

    public function urlReproductor(): ?string
    {
        if ($this->tipoRecurso?->nombre_tipo !== 'URL') {
            return null;
        }

        $url = parse_url(trim($this->url_recurso));
        if (! is_array($url) || ! in_array(strtolower($url['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($url['host'] ?? '');
        $path = trim($url['path'] ?? '', '/');
        parse_str($url['query'] ?? '', $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $id = match (true) {
                in_array($host, ['youtu.be', 'www.youtu.be'], true) => $path,
                $path === 'watch' => $query['v'] ?? null,
                preg_match('~^(?:embed|shorts|live)/([^/]+)$~D', $path, $partes) === 1 => $partes[1],
                default => null,
            };

            return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)
                ? "https://www.youtube.com/embed/{$id}" : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)
            && preg_match('~^(?:video/)?(\d+)(?:/[A-Za-z0-9]+)?$~D', $path, $partes)) {
            return "https://player.vimeo.com/video/{$partes[1]}";
        }

        return null;
    }

    public function tipoRecurso(): BelongsTo
    {
        return $this->belongsTo(TipoRecurso::class, 'id_tipo_recurso', 'id_tipo_recurso');
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'id_leccion', 'id_leccion');
    }

    public function tareaAsignada(): BelongsTo
    {
        return $this->belongsTo(TareaAsignada::class, 'id_tarea', 'id_tarea');
    }
}
