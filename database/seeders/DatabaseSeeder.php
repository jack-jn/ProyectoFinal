<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AvisoWeb;
use App\Models\Curso;
use App\Models\DetalleCursoHorario;
use App\Models\DetalleMatricula;
use App\Models\DiaSemana;
use App\Models\Docente;
use App\Models\GaleriaEvento;
use App\Models\InfoInstitucional;
use App\Models\Instrumento;
use App\Models\Leccion;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Recurso;
use App\Models\Secretaria;
use App\Models\Sesion;
use App\Models\TareaAsignada;
use App\Models\TipoRecurso;
use App\Models\UsuarioSistema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Los 7 instrumentos
        $instrumentosData = [
            ['nombre_instrumento' => 'Teclado', 'icono' => 'i-inst-piano'],
            ['nombre_instrumento' => 'Piano', 'icono' => 'i-inst-piano'],
            ['nombre_instrumento' => 'Guitarra', 'icono' => 'i-inst-guitar'],
            ['nombre_instrumento' => 'Violín', 'icono' => 'i-inst-violin'],
            ['nombre_instrumento' => 'Bajo Eléctrico', 'icono' => 'i-inst-guitar'],
            ['nombre_instrumento' => 'Batería', 'icono' => 'i-inst-drum'],
            ['nombre_instrumento' => 'Canto', 'icono' => 'i-inst-mic'],
        ];

        $instrumentos = [];
        foreach ($instrumentosData as $inst) {
            $modelo = Instrumento::query()->updateOrCreate(
                ['nombre_instrumento' => $inst['nombre_instrumento']],
                ['icono' => $inst['icono']]
            );
            $instrumentos[$inst['nombre_instrumento']] = $modelo;
        }

        // 2. Los 6 días de la semana (Lunes a Sábado)
        $diasNombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $dias = [];
        foreach ($diasNombres as $nombreDia) {
            $dias[$nombreDia] = DiaSemana::query()->updateOrCreate(
                ['nombre_dia' => $nombreDia]
            );
        }

        // 3. Los 3 tipos de recurso (MP4, PDF, URL)
        $tiposRecurso = [];
        foreach (['MP4', 'PDF', 'URL'] as $tipo) {
            $tiposRecurso[$tipo] = TipoRecurso::query()->updateOrCreate(
                ['nombre_tipo' => $tipo]
            );
        }

        // 4. Los 3 usuarios de prueba (Docente, Secretaria, Alumno) con clave '12345678'
        $personaDocente = Persona::query()->updateOrCreate(
            ['dni' => '45001122'],
            [
                'nombres' => 'Carlos Alberto',
                'apellido_paterno' => 'Mamani',
                'apellido_materno' => 'Quispe',
                'fecha_nacimiento' => '1985-04-15',
                'correo' => 'docente@dunamys.edu.pe',
                'celular' => '952233266',
            ]
        );

        $usuarioDocente = UsuarioSistema::query()->updateOrCreate(
            ['correo_usuario' => 'docente@dunamys.edu.pe'],
            [
                'id_persona' => $personaDocente->id_persona,
                'contrasena' => Hash::make('12345678'),
                'rol' => 'docente',
                'estado_cuenta' => 'Activo',
                'debe_cambiar_contrasena' => false,
                'permisos' => UsuarioSistema::permisosPorDefecto('docente'),
            ]
        );

        $docente = Docente::query()->updateOrCreate(
            ['id_persona' => $personaDocente->id_persona],
            ['especialidad' => 'Dirección Musical, Teclado y Piano']
        );

        $personaSecretaria = Persona::query()->updateOrCreate(
            ['dni' => '70112233'],
            [
                'nombres' => 'María Elena',
                'apellido_paterno' => 'Condori',
                'apellido_materno' => 'Flores',
                'fecha_nacimiento' => '1994-08-20',
                'correo' => 'secretaria@dunamys.edu.pe',
                'celular' => '999739090',
            ]
        );

        $usuarioSecretaria = UsuarioSistema::query()->updateOrCreate(
            ['correo_usuario' => 'secretaria@dunamys.edu.pe'],
            [
                'id_persona' => $personaSecretaria->id_persona,
                'contrasena' => Hash::make('12345678'),
                'rol' => 'secretaria',
                'estado_cuenta' => 'Activo',
                'debe_cambiar_contrasena' => false,
                'permisos' => UsuarioSistema::permisosPorDefecto('secretaria'),
            ]
        );

        $secretaria = Secretaria::query()->updateOrCreate(
            ['id_persona' => $personaSecretaria->id_persona],
            [
                'codigo_secretaria' => 'SEC-001',
                'turno_atencion' => 'Mañana y Tarde (08:00 - 18:00)',
            ]
        );

        $personaAlumno = Persona::query()->updateOrCreate(
            ['dni' => '75889900'],
            [
                'nombres' => 'Lucía',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Cahuana',
                'fecha_nacimiento' => '2004-03-10',
                'correo' => 'estudiante@dunamys.edu.pe',
                'celular' => '951445566',
            ]
        );

        $usuarioEstudiante = UsuarioSistema::query()->updateOrCreate(
            ['correo_usuario' => 'estudiante@dunamys.edu.pe'],
            [
                'id_persona' => $personaAlumno->id_persona,
                'contrasena' => Hash::make('12345678'),
                'rol' => 'alumno',
                'estado_cuenta' => 'Activo',
                'debe_cambiar_contrasena' => false,
                'permisos' => UsuarioSistema::permisosPorDefecto('alumno'),
            ]
        );

        $alumno = Alumno::query()->updateOrCreate(
            ['id_persona' => $personaAlumno->id_persona],
            [
                'id_apoderado' => null,
                'codigo_alumno' => 'ALU-2026-001',
            ]
        );

        // 5. Cursos de ejemplo con imágenes de assets/img/cursos/*.webp
        $cursosData = [
            [
                'codigo_curso' => 'C-001',
                'nombre_curso' => 'Teclado Básico',
                'instrumento' => 'Teclado',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 15,
                'imagen_url' => 'assets/img/cursos/teclado.webp',
                'estado_habilitado' => true,
                'dias' => ['Lunes', 'Miércoles', 'Viernes'],
                'hora_inicio' => '09:00:00',
                'hora_fin' => '11:00:00',
            ],
            [
                'codigo_curso' => 'C-002',
                'nombre_curso' => 'Piano Clásico y Moderno',
                'instrumento' => 'Piano',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 12,
                'imagen_url' => 'assets/img/cursos/piano.webp',
                'estado_habilitado' => false,
                'dias' => ['Martes', 'Jueves', 'Sábado'],
                'hora_inicio' => '10:00:00',
                'hora_fin' => '12:00:00',
            ],
            [
                'codigo_curso' => 'C-003',
                'nombre_curso' => 'Guitarra Acústica',
                'instrumento' => 'Guitarra',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 15,
                'imagen_url' => 'assets/img/cursos/guitarra.webp',
                'estado_habilitado' => false,
                'dias' => ['Lunes', 'Miércoles', 'Viernes'],
                'hora_inicio' => '15:00:00',
                'hora_fin' => '17:00:00',
            ],
            [
                'codigo_curso' => 'C-004',
                'nombre_curso' => 'Violín Expresivo',
                'instrumento' => 'Violín',
                'nivel' => 'Intermedio',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 10,
                'imagen_url' => 'assets/img/cursos/violin.webp',
                'estado_habilitado' => false,
                'dias' => ['Martes', 'Jueves'],
                'hora_inicio' => '15:00:00',
                'hora_fin' => '17:00:00',
            ],
            [
                'codigo_curso' => 'C-005',
                'nombre_curso' => 'Bajo Eléctrico',
                'instrumento' => 'Bajo Eléctrico',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 12,
                'imagen_url' => 'assets/img/cursos/bajo-electrico.webp',
                'estado_habilitado' => false,
                'dias' => ['Martes', 'Jueves', 'Sábado'],
                'hora_inicio' => '16:00:00',
                'hora_fin' => '18:00:00',
            ],
            [
                'codigo_curso' => 'C-006',
                'nombre_curso' => 'Batería y Percusión',
                'instrumento' => 'Batería',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 10,
                'imagen_url' => 'assets/img/cursos/bateria.webp',
                'estado_habilitado' => false,
                'dias' => ['Lunes', 'Miércoles', 'Viernes'],
                'hora_inicio' => '11:00:00',
                'hora_fin' => '13:00:00',
            ],
            [
                'codigo_curso' => 'C-007',
                'nombre_curso' => 'Canto y Técnica Vocal',
                'instrumento' => 'Canto',
                'nivel' => 'Básico',
                'modalidad' => 'Virtual',
                'costo' => 80.00,
                'cupos' => 20,
                'imagen_url' => 'assets/img/cursos/canto.webp',
                'estado_habilitado' => false,
                'dias' => ['Sábado'],
                'hora_inicio' => null,
                'hora_fin' => null,
            ],
        ];

        $cursos = [];
        foreach ($cursosData as $c) {
            $curso = Curso::query()->firstOrCreate(
                ['codigo_curso' => $c['codigo_curso']],
                [
                    'id_docente' => $docente->id_docente,
                    'id_instrumento' => $instrumentos[$c['instrumento']]->id_instrumento,
                    'nombre_curso' => $c['nombre_curso'],
                    'nivel' => $c['nivel'],
                    'modalidad' => 'Virtual',
                    'costo' => $c['costo'],
                    'cupos' => $c['cupos'],
                    'imagen_url' => $c['imagen_url'],
                    'estado_habilitado' => $c['estado_habilitado'],
                ]
            );

            foreach ($c['dias'] as $nombreDia) {
                DetalleCursoHorario::query()->firstOrCreate(
                    [
                        'id_curso' => $curso->id_curso,
                        'id_dia' => $dias[$nombreDia]->id_dia,
                    ],
                    [
                        'hora_inicio' => $c['hora_inicio'],
                        'hora_fin' => $c['hora_fin'],
                    ]
                );
            }

            $cursos[$c['codigo_curso']] = $curso;
        }

        // Matrícula activa inicial para el alumno de prueba en C-001 (Teclado Básico)
        $cursoTeclado = $cursos['C-001'];
        $matricula = Matricula::query()->updateOrCreate(
            ['codigo_matricula' => 'MAT-2026-001'],
            [
                'id_alumno' => $alumno->id_alumno,
                'fecha_matricula' => now()->subDays(5),
                'monto_total' => $cursoTeclado->costo,
                'estado_matricula' => 'Confirmada',
            ]
        );

        DetalleMatricula::query()->updateOrCreate(
            [
                'id_matricula' => $matricula->id_matricula,
                'id_curso' => $cursoTeclado->id_curso,
            ],
            [
                'fecha_inscripcion' => now()->subDays(5),
                'precio_cobrado' => $cursoTeclado->costo,
                'estado_acceso' => 'Activo',
            ]
        );

        Pago::query()->updateOrCreate(
            ['numero_operacion' => '84920145'],
            [
                'id_matricula' => $matricula->id_matricula,
                'id_secretaria' => $secretaria->id_secretaria,
                'metodo_pago' => 'Yape',
                'comprobante_url' => 'comprobantes/voucher_demo_001.jpg',
                'monto_pagado' => $cursoTeclado->costo,
                'fecha_pago' => now()->subDays(5),
                'estado_validacion' => 'Validado',
                'observacion' => null,
                'fecha_validacion' => now()->subDays(4),
            ]
        );

        // Sesiones, lecciones y tarea inicial para C-001 (Teclado Básico)
        $sesion1 = Sesion::query()->firstOrCreate(
            [
                'id_curso' => $cursoTeclado->id_curso,
                'numero_orden' => 1,
            ],
            ['titulo_sesion' => 'Postura corporal y reconocimiento del teclado']
        );

        $leccion1 = Leccion::query()->firstOrCreate(
            [
                'id_sesion' => $sesion1->id_sesion,
                'codigo_leccion' => 'LEC-01',
            ],
            [
                'numero_orden' => 1,
                'titulo_leccion' => 'Ubicación de las notas y digitación 1-5',
                'descripcion' => 'Aprende la postura ergonómica frente al teclado y cómo identificar el Do central.',
            ]
        );

        Recurso::query()->firstOrCreate(
            [
                'id_leccion' => $leccion1->id_leccion,
                'titulo_recurso' => 'Guía práctica de digitación en PDF',
            ],
            [
                'id_tipo_recurso' => $tiposRecurso['PDF']->id_tipo_recurso,
                'id_tarea' => null,
                'url_recurso' => 'recursos/guia-digitacion-teclado.pdf',
                'duracion' => null,
                'tamano_mb' => 1.25,
            ]
        );

        TareaAsignada::query()->firstOrCreate(
            ['id_sesion' => $sesion1->id_sesion],
            [
                'titulo_tarea' => 'Práctica en video: Escala de Do Mayor con ambas manos',
                'descripcion' => 'Graba un video de 1 a 2 minutos ejecutando la escala de Do Mayor ascendente y descendente cuidando la postura de muñeca.',
                'fecha_limite' => now()->addDays(7)->toDateString(),
            ]
        );

        // 6. Fila única inicial de info_institucional
        InfoInstitucional::query()->firstOrCreate(
            ['id_info' => 1],
            [
                'id_docente' => $docente->id_docente,
                'quienes_somos' => 'Dunamys es una academia de música en Juliaca dedicada a formar talentos con metodología práctica, recitales de gala y aula virtual interactiva.',
                'telefono_contacto' => '952233266',
                'correo_contacto' => 'contacto@dunamys.edu.pe',
                'direccion' => 'Plaza de Armas - Juliaca, Jr. Ica 252, Galería San Román, 3er piso.',
                'hora_apertura' => '08:00:00',
                'hora_cierre' => '18:00:00',
                'fecha_actualizado' => now(),
            ]
        );

        // 7. Avisos web iniciales
        AvisoWeb::query()->firstOrCreate(
            ['titulo_aviso' => 'Próximo inicio de ciclo'],
            [
                'id_docente' => $docente->id_docente,
                'texto_aviso' => 'Matrículas abiertas para los niveles Básico, Intermedio y Avanzado en todos nuestros instrumentos.',
                'fecha_publicacion' => now(),
            ]
        );

        // 8. Galería de eventos inicial
        $galeriaInicial = [
            ['titulo_evento' => 'Presentación musical DUNAMYS', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-01.webp'],
            ['titulo_evento' => 'Estudiantes de guitarra', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-02.webp'],
            ['titulo_evento' => 'Clase de piano', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-03.webp'],
            ['titulo_evento' => 'Recital de estudiantes', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-04.webp'],
            ['titulo_evento' => 'Actividades musicales', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-05.webp'],
            ['titulo_evento' => 'Equipo de DUNAMYS', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-06.webp'],
            ['titulo_evento' => 'Presentación de estudiantes', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-07.webp'],
            ['titulo_evento' => 'Academia musical', 'tipo_multimedia' => 'Foto', 'archivo_url' => 'assets/img/galeria/galeria-08.webp'],
        ];

        foreach ($galeriaInicial as $item) {
            GaleriaEvento::query()->firstOrCreate(
                ['titulo_evento' => $item['titulo_evento']],
                [
                    'id_docente' => $docente->id_docente,
                    'tipo_multimedia' => $item['tipo_multimedia'],
                    'archivo_url' => $item['archivo_url'],
                ]
            );
        }
        $this->call(RolesPermisosSeeder::class);

        foreach ([$usuarioDocente, $usuarioSecretaria, $usuarioEstudiante] as $usuarioBase) {
            $usuarioBase->sincronizarAcceso(UsuarioSistema::permisosPorDefecto($usuarioBase->rol));
        }
    }
}
