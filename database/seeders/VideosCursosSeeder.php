<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Leccion;
use App\Models\Recurso;
use App\Models\Sesion;
use App\Models\TipoRecurso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideosCursosSeeder extends Seeder
{
    private const CLASES_TECLADO = [
        1 => ['Clase 1: Cómo tocar teclado para principiantes (Desde cero)', 'cbBM9pzVf0M'],
        2 => ['Clase 2: Primeros ejercicios y digitación en el teclado', '9TAtZpoUUzo'],
        3 => ['Clase 3: Acordes básicos y práctica guiada', 'QsSEevleUGc'],
    ];

    private const VIDEOS_INICIALES = [
        'teclado' => 'cbBM9pzVf0M',
        'guitarra' => 'eJGs9_kgRyg',
        'piano' => 'wCKHqOXGxdA',
        'violín' => 'KgHapbcY1r8',
        'bajo eléctrico' => 'Y-7E1fvZqzc',
        'batería' => '6mveJ3x_X_A',
        'canto' => 'LVTUdfplfZc',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $teclado = Curso::query()->where('codigo_curso', 'C-001')->first()
                ?? Curso::query()->where('nombre_curso', 'like', '%Teclado%')->orderBy('id_curso')->firstOrFail();
            $tipoUrl = TipoRecurso::query()->firstOrCreate(['nombre_tipo' => 'URL']);

            $sesion = $this->primeraSesion($teclado);
            foreach (self::CLASES_TECLADO as $orden => [$titulo, $video]) {
                $leccion = $sesion->lecciones()->where('numero_orden', $orden)->orderBy('id_leccion')->first()
                    ?? $this->crearLeccion($sesion, $orden, $titulo);
                $leccion->update(['titulo_leccion' => $titulo]);
                $this->guardarVideo($tipoUrl, "Video — Clase {$orden}: Teclado para principiantes", $video, $leccion->id_leccion, null);
            }

            $tarea = $sesion->tareaAsignada()->updateOrCreate([], [
                'titulo_tarea' => 'Práctica de la Sesión 1: Demostración en teclado',
                'descripcion' => 'Observa el video guía de la tarea, practica el ejercicio indicado y envía el enlace de tu video demostrativo.',
            ]);
            $this->guardarVideo($tipoUrl, 'Video guía de tarea — Clase 7: Teclado para principiantes', 'wkVnb41ckIU', null, $tarea->id_tarea);

            Curso::query()->whereKeyNot($teclado->getKey())
                ->with(['instrumento', 'sesiones.lecciones.recursos.tipoRecurso'])
                ->eachById(function (Curso $curso) use ($tipoUrl): void {
                    $this->agregarVideoInicial($curso, $tipoUrl);
                });
        });
    }

    private function primeraSesion(Curso $curso): Sesion
    {
        return $curso->sesiones()->firstOrCreate(
            ['numero_orden' => 1],
            ['titulo_sesion' => 'Introducción: '.$curso->nombre_curso],
        );
    }

    private function agregarVideoInicial(Curso $curso, TipoRecurso $tipoUrl): void
    {
        $instrumento = $curso->instrumento?->nombre_instrumento;
        $video = self::VIDEOS_INICIALES[mb_strtolower((string) $instrumento)] ?? null;
        if ($video === null) {
            return;
        }

        $sesion = $curso->sesiones->firstWhere('numero_orden', 1);
        $tieneVideos = $curso->sesiones->flatMap->lecciones->flatMap->recursos
            ->contains(fn (Recurso $recurso): bool => $recurso->tipoRecurso?->nombre_tipo === 'MP4' || $recurso->urlReproductor() !== null);

        if ($sesion && $tieneVideos) {
            return;
        }

        $sesion ??= $this->primeraSesion($curso);
        $titulo = 'Introducción: '.$instrumento;
        $leccion = $this->crearLeccion($sesion, (int) $sesion->lecciones()->max('numero_orden') + 1, $titulo);
        $this->guardarVideo($tipoUrl, 'Video — '.$titulo, $video, $leccion->id_leccion, null);
    }

    private function crearLeccion(Sesion $sesion, int $orden, string $titulo): Leccion
    {
        $codigos = $sesion->lecciones()->pluck('codigo_leccion');
        $numeroCodigo = $orden;
        do {
            $codigo = sprintf('LEC-%02d', $numeroCodigo++);
        } while ($codigos->contains($codigo));

        return $sesion->lecciones()->firstOrCreate(['codigo_leccion' => $codigo], [
            'numero_orden' => $orden,
            'titulo_leccion' => $titulo,
        ]);
    }

    private function guardarVideo(TipoRecurso $tipoUrl, string $titulo, string $video, ?int $idLeccion, ?int $idTarea): void
    {
        Recurso::query()->updateOrCreate([
            'id_leccion' => $idLeccion,
            'id_tarea' => $idTarea,
            'titulo_recurso' => $titulo,
            'id_tipo_recurso' => $tipoUrl->id_tipo_recurso,
        ], [
            'url_recurso' => 'https://www.youtube.com/embed/'.$video,
        ]);
    }
}
