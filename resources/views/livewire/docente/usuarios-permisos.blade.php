<section>
    <div class="page-head">
        <div>
            <h1>Usuarios y <span>permisos</span></h1>
            <p>Administra cuentas de Secretaría y Alumnos, controla sus accesos y restablece credenciales.</p>
        </div>
        @if ($pestanaActiva !== 'crear')
            <div class="head-actions">
                <button type="button" class="btn btn--primary" wire:click="cambiarPestana('crear')">
                    <svg class="i"><use href="#i-plus"/></svg> Nuevo usuario
                </button>
            </div>
        @endif
    </div>

    <x-toast-bridge :mensaje="$mensaje" tipo="success" />

    <!-- 3 PESTAÑAS PRINCIPALES -->
    <div class="ws-folder-tabs" role="tablist" aria-label="Secciones de usuarios y permisos" style="margin-bottom:0;flex-wrap:wrap">
        <button
            type="button"
            role="tab"
            aria-selected="{{ $pestanaActiva === 'secretaria' ? 'true' : 'false' }}"
            class="seg-tab {{ $pestanaActiva === 'secretaria' ? 'is-active' : '' }}"
            wire:click="cambiarPestana('secretaria')"
        >
            Personal / Secretaría <span class="badge-cnt">{{ $totalSecretaria }}</span>
        </button>
        <button
            type="button"
            role="tab"
            aria-selected="{{ $pestanaActiva === 'alumnos' ? 'true' : 'false' }}"
            class="seg-tab {{ $pestanaActiva === 'alumnos' ? 'is-active' : '' }}"
            wire:click="cambiarPestana('alumnos')"
        >
            Alumnos <span class="badge-cnt">{{ $totalEstudiante }}</span>
        </button>
        <button
            type="button"
            role="tab"
            aria-selected="{{ $pestanaActiva === 'crear' ? 'true' : 'false' }}"
            class="seg-tab {{ $pestanaActiva === 'crear' ? 'is-active' : '' }}"
            wire:click="cambiarPestana('crear')"
        >
            + Crear usuario
        </button>
    </div>

    @if ($pestanaActiva === 'secretaria' || $pestanaActiva === 'alumnos')
        <!-- PESTAÑA 1 Y 2: LISTADO Y GESTIÓN DE PERMISOS -->
        <div class="card" style="border-top-left-radius:0;padding:18px 20px">
            @if ($pestanaActiva === 'alumnos')
                <div class="tabs-bar" role="tablist" aria-label="Filtrar alumnos por instrumento" style="margin-bottom:14px">
                    <button
                        type="button"
                        class="tab-btn {{ $filtroInstrumento === 'all' ? 'is-active' : '' }}"
                        wire:click="seleccionarInstrumento('all')"
                    >
                        Todos
                    </button>
                    @foreach ($instrumentos as $inst)
                        <button
                            type="button"
                            class="tab-btn {{ $filtroInstrumento === $inst->nombre_instrumento ? 'is-active' : '' }}"
                            wire:key="usr-tab-inst-{{ $inst->id_instrumento }}"
                            wire:click="seleccionarInstrumento('{{ $inst->nombre_instrumento }}')"
                        >
                            {{ $inst->nombre_instrumento }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="toolbar" style="padding:0;box-shadow:none;border:0;margin-bottom:16px">
                <div class="search" style="flex:1 1 260px">
                    <svg class="i"><use href="#i-search"/></svg>
                    <input
                        class="control"
                        type="search"
                        wire:model.live.debounce.300ms="busqueda"
                        placeholder="{{ $pestanaActiva === 'alumnos' ? 'Buscar por nombre, DNI, correo, código o curso...' : 'Buscar por nombres, apellidos, correo o DNI...' }}"
                        aria-label="Buscar usuarios"
                    >
                </div>

                @if ($pestanaActiva === 'alumnos')
                    <select class="control" style="max-width:200px" wire:model.live="filtroNivel" aria-label="Filtrar por nivel">
                        <option value="all">Todos los niveles</option>
                        <option value="Básico">Básico</option>
                        <option value="Intermedio">Intermedio</option>
                        <option value="Avanzado">Avanzado</option>
                    </select>
                @endif

                <select class="control" style="max-width:190px" wire:model.live="filtroEstado" aria-label="Filtrar por estado de cuenta">
                    <option value="todos">Todos los estados</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
                @if (trim($busqueda) !== '' || $filtroEstado !== 'todos' || ($pestanaActiva === 'alumnos' && ($filtroInstrumento !== 'all' || $filtroNivel !== 'all' || $filtroCurso !== 'all')))
                    <button type="button" class="link-btn" wire:click="limpiarFiltros">Limpiar filtros</button>
                @endif
            </div>

            <div class="table-wrap">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Credenciales</th>
                            <th>Estado de cuenta</th>
                            <th>Permisos (Spatie)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cuentas as $cuenta)
                            @php
                                $tieneClaveTemporal = ! empty($cuenta->password_temporal);
                                $claveVisible = $passwordsVisibles[$cuenta->id_usuario] ?? null;
                            @endphp
                            <tr wire:key="usuario-{{ $cuenta->id_usuario }}">
                                <td data-label="Usuario">
                                    <strong>{{ $cuenta->persona?->nombre_completo ?? 'Sin nombre' }}</strong>
                                    <div style="font-size:12.5px;color:var(--muted)">{{ $cuenta->correo_usuario }}</div>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;align-items:center">
                                        @if ($cuenta->rol === 'secretaria')
                                            <span class="pill dark">Secretaría</span>
                                        @else
                                            <span class="pill">Estudiante</span>
                                        @endif
                                        @if ($cuenta->persona?->dni)
                                            <small style="font-size:11.5px;color:var(--muted)">DNI: {{ $cuenta->persona->dni }}</small>
                                        @endif
                                        @if ($cuenta->persona?->celular)
                                            <small style="font-size:11.5px;color:var(--muted)">Cel: {{ $cuenta->persona->celular }}</small>
                                        @endif
                                    </div>
                                    @if ($cuenta->rol === 'alumno')
                                        @php
                                            $cursosAlumno = ($cuenta->persona?->alumno?->matriculas ?? collect())
                                                ->flatMap->detalleMatriculas
                                                ->map(fn ($d) => $d->curso?->nombre_curso)
                                                ->filter()
                                                ->unique()
                                                ->implode(', ');
                                        @endphp
                                        <small style="display:block;margin-top:4px;font-size:11.5px;color:var(--red-dark)">
                                            {{ $cursosAlumno !== '' ? $cursosAlumno : 'Sin cursos matriculados' }}
                                        </small>
                                    @endif
                                </td>
                                <td data-label="Credenciales">
                                    @if (! $tieneClaveTemporal)
                                        <span class="pill gold">Sin clave visible</span>
                                    @elseif ($claveVisible !== null)
                                        <code style="font-size:13px;font-weight:700;color:var(--ink);background:var(--surface);padding:4px 9px;border-radius:8px;border:1px solid var(--line)">{{ $claveVisible }}</code>
                                    @else
                                        <span style="font-family:monospace;letter-spacing:2px;color:var(--muted)">••••••••••</span>
                                    @endif
                                    @if ($cuenta->debe_cambiar_contrasena)
                                        <small style="display:block;font-size:11px;color:var(--muted);margin-top:4px">Cambio pendiente en 1er ingreso</small>
                                    @endif
                                </td>
                                <td data-label="Estado">
                                    <span class="pill {{ $cuenta->estado_cuenta === 'Activo' ? 'green' : 'red' }}">
                                        {{ $cuenta->estado_cuenta }}
                                    </span>
                                </td>
                                <td data-label="Permisos">
                                    <div style="display:grid;gap:4px">
                                        @foreach ($cuenta->rol === 'alumno' ? \App\Models\UsuarioSistema::PERMISOS_ALUMNO : \App\Models\UsuarioSistema::PERMISOS_SECRETARIA as $permiso => $etiqueta)
                                            <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer" wire:key="permiso-{{ $cuenta->id_usuario }}-{{ $permiso }}">
                                                <input type="checkbox" value="{{ $permiso }}" wire:model="permisosSeleccionados.{{ $cuenta->id_usuario }}">
                                                <span>{{ $etiqueta }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('permisosSeleccionados.'.$cuenta->id_usuario.'.*')
                                        <p class="error" style="display:flex;margin-top:4px" role="alert">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td data-label="Acciones">
                                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                                        @if ($tieneClaveTemporal)
                                            <button type="button" class="btn btn--outline btn--sm" wire:click="alternarPassword({{ $cuenta->id_usuario }})" wire:loading.attr="disabled">
                                                <svg class="i"><use href="#{{ $claveVisible !== null ? 'i-eye-off' : 'i-eye' }}"/></svg>
                                                {{ $claveVisible !== null ? 'Ocultar' : 'Mostrar' }}
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn--outline btn--sm" wire:click="cambiarEstado({{ $cuenta->id_usuario }})" wire:loading.attr="disabled">
                                            {{ $cuenta->estado_cuenta === 'Activo' ? 'Desactivar' : 'Activar' }}
                                        </button>
                                        <button type="button" class="btn btn--soft-red btn--sm" wire:click="restablecerClave({{ $cuenta->id_usuario }})" wire:loading.attr="disabled">
                                            Restablecer clave
                                        </button>
                                        <button type="button" class="btn btn--primary btn--sm" wire:click="guardarPermisos({{ $cuenta->id_usuario }})" wire:loading.attr="disabled">
                                            Guardar permisos
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:28px;color:var(--muted)">
                                    No se encontraron usuarios en esta categoría con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- PESTAÑA 3: CREAR USUARIO -->
        <form class="card" style="border-top-left-radius:0;padding:22px" wire:submit="crearUsuario" novalidate>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--line)">
                <div>
                    <h2 style="margin:0 0 4px;font-size:18px">Registrar nuevo usuario</h2>
                    <p style="margin:0;font-size:13px;color:var(--muted)">Crea una cuenta de Secretaría o Estudiante y define sus permisos iniciales.</p>
                </div>
                <button type="button" class="btn btn--outline btn--sm" wire:click="cambiarPestana('secretaria')">
                    Volver al listado
                </button>
            </div>

            <!-- Selección destacada de Rol -->
            <div class="field field--full" style="margin-bottom:18px">
                <span class="label" style="margin-bottom:8px;display:block">Tipo de cuenta (Rol) *</span>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
                    <label style="display:flex;align-items:flex-start;gap:10px;padding:14px;border-radius:12px;border:2px solid {{ $rol === 'secretaria' ? 'var(--red)' : 'var(--line)' }};background:{{ $rol === 'secretaria' ? 'var(--red-soft)' : '#fff' }};cursor:pointer">
                        <input type="radio" name="rol_nuevo" value="secretaria" wire:model.live="rol" style="margin-top:3px">
                        <span>
                            <strong style="display:block;font-size:14px;color:var(--ink)">Secretaría</strong>
                            <small style="color:var(--muted);font-size:12px">Valida comprobantes Yape, consulta historial y gestiona accesos de matrícula.</small>
                        </span>
                    </label>
                    <label style="display:flex;align-items:flex-start;gap:10px;padding:14px;border-radius:12px;border:2px solid {{ $rol === 'alumno' ? 'var(--red)' : 'var(--line)' }};background:{{ $rol === 'alumno' ? 'var(--red-soft)' : '#fff' }};cursor:pointer">
                        <input type="radio" name="rol_nuevo" value="alumno" wire:model.live="rol" style="margin-top:3px">
                        <span>
                            <strong style="display:block;font-size:14px;color:var(--ink)">Estudiante</strong>
                            <small style="color:var(--muted);font-size:12px">Accede al aula virtual, visualiza lecciones/materiales y entrega tareas.</small>
                        </span>
                    </label>
                </div>
                @error('rol')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
            </div>

            <!-- Grid responsivo de datos personales y acceso -->
            <div class="form-grid" style="margin-bottom:18px">
                <div class="field {{ $errors->has('nombres') ? 'is-invalid' : '' }}">
                    <label for="nuevo-nombres">Nombres *</label>
                    <input class="control" id="nuevo-nombres" type="text" wire:model="nombres" placeholder="Ej. Ana María" required>
                    @error('nombres')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field {{ $errors->has('apellido_paterno') ? 'is-invalid' : '' }}">
                    <label for="nuevo-apellido_paterno">Apellido paterno *</label>
                    <input class="control" id="nuevo-apellido_paterno" type="text" wire:model="apellido_paterno" placeholder="Ej. Flores" required>
                    @error('apellido_paterno')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field {{ $errors->has('apellido_materno') ? 'is-invalid' : '' }}">
                    <label for="nuevo-apellido_materno">Apellido materno *</label>
                    <input class="control" id="nuevo-apellido_materno" type="text" wire:model="apellido_materno" placeholder="Ej. López" required>
                    @error('apellido_materno')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field {{ $errors->has('dni') ? 'is-invalid' : '' }}">
                    <label for="nuevo-dni">DNI *</label>
                    <input class="control" id="nuevo-dni" type="text" maxlength="8" inputmode="numeric" wire:model="dni" placeholder="8 dígitos" required>
                    @error('dni')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field {{ $errors->has('correo') ? 'is-invalid' : '' }}">
                    <label for="nuevo-correo">Correo electrónico *</label>
                    <input class="control" id="nuevo-correo" type="email" wire:model="correo" placeholder="usuario@dunamys.edu.pe" required>
                    @error('correo')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field {{ $errors->has('celular') ? 'is-invalid' : '' }}">
                    <label for="nuevo-celular">Celular (opcional)</label>
                    <input class="control" id="nuevo-celular" type="text" maxlength="9" inputmode="numeric" wire:model="celular" placeholder="9 dígitos">
                    @error('celular')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field field--full {{ $errors->has('clave') ? 'is-invalid' : '' }}">
                    <label for="nuevo-clave">Contraseña inicial *</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input
                            class="control"
                            id="nuevo-clave"
                            type="{{ $mostrarClave ? 'text' : 'password' }}"
                            autocomplete="new-password"
                            wire:model="clave"
                            placeholder="Mínimo 8 caracteres"
                            required
                            style="flex:1"
                        >
                        <button type="button" class="btn btn--outline btn--sm" wire:click="alternarMostrarClave" style="height:40px;white-space:nowrap">
                            <svg class="i"><use href="#{{ $mostrarClave ? 'i-eye-off' : 'i-eye' }}"/></svg>
                            {{ $mostrarClave ? 'Ocultar' : 'Mostrar' }}
                        </button>
                    </div>
                    @error('clave')<p class="error" style="display:flex" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Permisos iniciales opcionales -->
            <div style="padding:14px 16px;border-radius:12px;border:1px solid var(--line);background:#FAF8F9;margin-bottom:18px">
                <strong style="display:block;font-size:13.5px;margin-bottom:4px">
                    Permisos iniciales para {{ $rol === 'alumno' ? 'Estudiante' : 'Secretaría' }}
                </strong>
                <small style="display:block;color:var(--muted);margin-bottom:10px">
                    Se asignan todos por defecto; puedes ajustar qué módulos podrá ver desde su primer ingreso.
                </small>
                <div style="display:flex;flex-wrap:wrap;gap:12px 20px">
                    @foreach ($rol === 'alumno' ? \App\Models\UsuarioSistema::PERMISOS_ALUMNO : \App\Models\UsuarioSistema::PERMISOS_SECRETARIA as $permiso => $etiqueta)
                        <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer" wire:key="crear-permiso-{{ $rol }}-{{ $permiso }}">
                            <input type="checkbox" value="{{ $permiso }}" wire:model="permisosInicialesCrear">
                            <span>{{ $etiqueta }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permisosInicialesCrear.*')<p class="error" style="display:flex;margin-top:6px" role="alert">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap">
                <button type="button" class="btn btn--outline" wire:click="cambiarPestana('secretaria')">Cancelar</button>
                <button class="btn btn--primary" type="submit" wire:loading.attr="disabled">
                    <svg class="i"><use href="#i-plus"/></svg> Crear usuario
                </button>
            </div>
        </form>
    @endif

    <!-- MODAL DE CREDENCIALES DE UN SOLO USO -->
    @if ($modalCredencialesAbierto && $credencialClaveTemporal)
        <div class="overlay is-open" wire:key="modal-credenciales-un-solo-uso" wire:keydown.escape="cerrarModalCredenciales">
            <div class="dialog dialog--sm" role="dialog" aria-modal="true" aria-labelledby="tituloCredencialesUnSoloUso"
                x-data="{
                    copiado: false,
                    copiar(texto) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(texto).then(() => {
                                this.copiado = true;
                                setTimeout(() => this.copiado = false, 2000);
                            });
                        } else {
                            const area = document.createElement('textarea');
                            area.value = texto;
                            document.body.appendChild(area);
                            area.select();
                            document.execCommand('copy');
                            document.body.removeChild(area);
                            this.copiado = true;
                            setTimeout(() => this.copiado = false, 2000);
                        }
                    }
                }">
                <div class="dialog__head">
                    <h2 class="dialog__title" id="tituloCredencialesUnSoloUso">Credenciales temporales</h2>
                    <button type="button" class="icon-btn" wire:click="cerrarModalCredenciales" aria-label="Cerrar modal">
                        <svg class="i"><use href="#i-x"/></svg>
                    </button>
                </div>
                <div class="dialog__body">
                    <div style="padding:10px 12px;border-radius:10px;background:var(--gold-soft);border:1px solid var(--gold-border);color:var(--gold-dark);font-size:12.5px;margin-bottom:14px">
                        <strong>Aviso de seguridad:</strong> Esta contraseña temporal se muestra una sola vez. El usuario deberá cambiarla en su próximo inicio de sesión.
                    </div>

                    <div style="display:grid;gap:10px;font-size:13.5px">
                        <div>
                            <small style="display:block;color:var(--muted);font-weight:600">USUARIO ({{ $credencialUsuarioRol }})</small>
                            <strong>{{ $credencialUsuarioNombre }}</strong>
                        </div>
                        <div>
                            <small style="display:block;color:var(--muted);font-weight:600">CORREO DE ACCESO</small>
                            <strong>{{ $credencialUsuarioCorreo }}</strong>
                        </div>
                        <div>
                            <small style="display:block;color:var(--muted);font-weight:600;margin-bottom:4px">CONTRASEÑA TEMPORAL</small>
                            <div style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;background:#191718;color:#fff">
                                <code style="flex:1;font-size:15px;font-weight:700;letter-spacing:.06em;user-select:all">{{ $credencialClaveTemporal }}</code>
                                <button
                                    type="button"
                                    class="btn btn--sm btn--primary"
                                    x-on:click="copiar(@js($credencialClaveTemporal))"
                                >
                                    <span x-text="copiado ? '¡Copiado!' : 'Copiar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dialog__foot">
                    <button
                        type="button"
                        class="btn btn--outline"
                        x-on:click="copiar(@js("Correo: {$credencialUsuarioCorreo}\nContraseña temporal: {$credencialClaveTemporal}"))"
                    >
                        Copiar correo y clave
                    </button>
                    <button type="button" class="btn btn--primary" wire:click="cerrarModalCredenciales">Entendido, cerrar</button>
                </div>
            </div>
        </div>
    @endif
</section>
