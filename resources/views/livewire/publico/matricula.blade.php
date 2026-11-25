<div>
<header class="navbar">
    <a href="{{ route('home') }}" class="logo">
        <img src="{{ asset('assets/img/logo2.jpg') }}" alt="DUNAMYS" style="height:48px;width:auto;object-fit:contain;">
    </a>

    <a href="{{ route('home') }}" class="back-home">
        <i class="bi bi-arrow-left"></i> Volver al inicio
    </a>
</header>

<main class="enrollment-main">

    <div class="enrollment-heading">
        <span class="eyebrow">DUNAMYS ACADEMIA MUSICAL</span>
        <h1>¡Comienza tu <span>experiencia musical!</span></h1>
        <p>Completa los siguientes pasos para realizar tu matrícula.</p>
    </div>

    @if ($matriculaRegistrada)
        <div class="enrollment-card" style="text-align:center;padding:48px 28px">
            <div style="width:72px;height:72px;border-radius:50%;background:#e7f6ed;color:#16865a;display:grid;place-items:center;font-size:36px;margin:0 auto 18px">✓</div>
            <h2 style="font-size:24px;font-weight:900;margin-bottom:10px">¡Solicitud de matrícula registrada!</h2>
            <p style="color:#555;font-size:14px;max-width:520px;margin:0 auto 18px;line-height:1.7">
                Tu solicitud con código <strong>{{ $codigoMatriculaGenerado }}</strong> y tu comprobante de pago por Yape fueron enviados a Secretaría para su validación.
            </p>
            <div class="total-box" style="max-width:420px;margin:0 auto 24px">
                <span>Código de matrícula</span>
                <strong>{{ $codigoMatriculaGenerado }}</strong>
            </div>
            <a href="{{ route('home') }}" class="btn-next" style="text-decoration:none">
                Volver al inicio <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    @else
        <!-- INDICADOR DE PASOS -->
        <div class="steps-container">
            @foreach ([1 => 'Cursos', 2 => 'Acceso', 3 => 'Datos', 4 => 'Apoderado', 5 => 'Resumen', 6 => 'Pago'] as $num => $etiqueta)
                <div class="step {{ $pasoActual === $num ? 'active' : ($pasoActual > $num ? 'completed' : '') }}" data-step="{{ $num }}">
                    <span>{{ $num }}</span>
                    <small>{{ $etiqueta }}</small>
                </div>
            @endforeach
        </div>

        <div class="enrollment-card">

            <!-- PASO 1: CURSOS -->
            @if ($pasoActual === 1)
                <section class="form-step active" data-page="1">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-music-note-beamed"></i></span>
                        <div>
                            <h2>Selecciona tus cursos</h2>
                            <p>Puedes elegir uno o varios cursos con cupos disponibles.</p>
                        </div>
                    </div>

                    @error('cursosSeleccionados')
                        <div class="minor-notice" style="background:#fff0f2;color:#c90020;margin-bottom:18px;margin-top:0">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="enroll-course-grid">
                        @foreach ($cursosHabilitados as $curso)
                            @php
                                $sinCupos = $curso->cupos_disponibles <= 0;
                                $seleccionado = in_array($curso->id_curso, $cursosSeleccionados, true);
                            @endphp
                            <label
                                class="enroll-course"
                                wire:key="mat-curso-{{ $curso->id_curso }}"
                                style="{{ $sinCupos ? 'opacity:.55;cursor:not-allowed' : '' }}"
                            >
                                <input
                                    type="checkbox"
                                    class="course-check"
                                    value="{{ $curso->id_curso }}"
                                    @checked($seleccionado)
                                    @disabled($sinCupos)
                                    wire:click="alternarCurso({{ $curso->id_curso }})"
                                >

                                <img src="{{ asset($curso->imagen_url ?: 'assets/img/cursos/guitarra.webp') }}" alt="{{ $curso->nombre_curso }}">

                                <div class="enroll-course-info">
                                    <h3>{{ $curso->nombre_curso }}</h3>
                                    <p>{{ $curso->nivel }} · 100% Virtual ({{ $sinCupos ? 'Sin cupos' : $curso->cupos_disponibles.' cupos libres' }})</p>
                                    <strong>S/ {{ number_format((float) $curso->costo, 2) }}</strong>
                                </div>

                                <span class="course-selected" style="{{ $seleccionado ? 'opacity:1;transform:scale(1)' : '' }}">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="selection-summary">
                        <span><i class="bi bi-check2-square"></i> Cursos seleccionados:</span>
                        <strong id="selectedCount">{{ count($cursosSeleccionados) }}</strong>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('home') }}" class="btn-cancel">Cancelar</a>
                        <button type="button" class="btn-next" wire:click="irAPaso(2)">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            <!-- PASO 2: HORARIOS -->
            @if ($pasoActual === 2)
                <section class="form-step active" data-page="2">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-calendar-week"></i></span>
                        <div>
                            <h2>Acceso y costos</h2>
                            <p>Consulta la información de cada curso seleccionado.</p>
                        </div>
                    </div>

                    <div id="scheduleList" class="schedule-list">
                        @foreach ($cursosElegidos as $curso)
                            <div class="schedule-item" wire:key="sched-{{ $curso->id_curso }}">
                                <div>
                                    <h3>{{ $curso->nombre_curso }}</h3>
                                    <p>Modalidad: 100% Virtual · Nivel: {{ $curso->nivel }}</p>
                                </div>
                                <div>
                                    <strong style="font-size:13px">Acceso 24/7 (Virtual)</strong>
                                </div>
                                <div class="schedule-price">S/ {{ number_format((float) $curso->costo, 2) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="total-box">
                        <span>Costo total de los cursos</span>
                        <strong id="scheduleTotal">S/ {{ number_format($totalPagar, 2) }}</strong>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-back" wire:click="irAPaso(1)">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </button>
                        <button type="button" class="btn-next" wire:click="irAPaso(3)">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            <!-- PASO 3: DATOS PERSONALES -->
            @if ($pasoActual === 3)
                <section class="form-step active" data-page="3">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-person-vcard"></i></span>
                        <div>
                            <h2>Datos personales</h2>
                            <p>Ingresa tus datos para registrar tu matrícula.</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="firstName">Nombres *</label>
                            <input type="text" id="firstName" wire:model="nombres" placeholder="Ingresa tus nombres" required>
                            @error('nombres') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="lastNameP">Apellido paterno *</label>
                            <input type="text" id="lastNameP" wire:model="apellido_paterno" placeholder="Apellido paterno" required>
                            @error('apellido_paterno') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="lastNameM">Apellido materno *</label>
                            <input type="text" id="lastNameM" wire:model="apellido_materno" placeholder="Apellido materno" required>
                            @error('apellido_materno') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="dni">DNI *</label>
                            <input type="text" id="dni" wire:model="dni" maxlength="8" placeholder="8 dígitos" required>
                            @error('dni') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="birthDate">Fecha de nacimiento *</label>
                            <input type="date" id="birthDate" wire:model="fecha_nacimiento" required>
                            @error('fecha_nacimiento') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="email">Correo electrónico *</label>
                            <input type="email" id="email" wire:model="correo" placeholder="correo@ejemplo.com" required>
                            @error('correo') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="phone">Teléfono / Celular (9 dígitos) *</label>
                            <input type="tel" id="phone" wire:model="celular" maxlength="9" placeholder="9 dígitos" required>
                            @error('celular') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="minor-notice" id="minorNotice">
                        <i class="bi bi-info-circle"></i>
                        Si eres menor de 18 años, necesitaremos los datos de tu padre o apoderado en el siguiente paso.
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-back" wire:click="irAPaso(2)">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </button>
                        <button type="button" class="btn-next" id="personalContinue" wire:click="continuarDesdeDatosPersonales">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            <!-- PASO 4: APODERADO -->
            @if ($pasoActual === 4)
                <section class="form-step active" data-page="4">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-people"></i></span>
                        <div>
                            <h2>Datos del padre o apoderado</h2>
                            <p>Completa los datos de la persona responsable.</p>
                        </div>
                    </div>

                    <div class="minor-notice" style="margin-bottom:20px;margin-top:0">
                        <i class="bi bi-shield-check"></i>
                        Esta información es obligatoria porque el estudiante es menor de 18 años.
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="guardianName">Nombres del apoderado *</label>
                            <input type="text" id="guardianName" wire:model="apoderado_nombres" placeholder="Nombres del apoderado" required>
                            @error('apoderado_nombres') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="guardianLastNameP">Apellido paterno *</label>
                            <input type="text" id="guardianLastNameP" wire:model="apoderado_apellido_paterno" placeholder="Apellido paterno" required>
                            @error('apoderado_apellido_paterno') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="guardianLastNameM">Apellido materno *</label>
                            <input type="text" id="guardianLastNameM" wire:model="apoderado_apellido_materno" placeholder="Apellido materno" required>
                            @error('apoderado_apellido_materno') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="guardianDni">DNI del apoderado *</label>
                            <input type="text" id="guardianDni" wire:model="apoderado_dni" maxlength="8" placeholder="8 dígitos" required>
                            @error('apoderado_dni') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="guardianPhone">Celular del apoderado (9 dígitos) *</label>
                            <input type="tel" id="guardianPhone" wire:model="apoderado_celular" maxlength="9" placeholder="9 dígitos" required>
                            @error('apoderado_celular') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="guardianParentesco">Parentesco *</label>
                            <input type="text" id="guardianParentesco" wire:model="apoderado_parentesco" placeholder="Padre, Madre, Tutor..." required>
                            @error('apoderado_parentesco') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-back" wire:click="irAPaso(3)">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </button>
                        <button type="button" class="btn-next" id="guardianContinue" wire:click="continuarDesdeApoderado">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            <!-- PASO 5: RESUMEN -->
            @if ($pasoActual === 5)
                <section class="form-step active" data-page="5">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-receipt"></i></span>
                        <div>
                            <h2>Resumen de matrícula</h2>
                            <p>Revisa todos los datos antes de continuar al pago.</p>
                        </div>
                    </div>

                    <div class="summary-person">
                        <h3><i class="bi bi-person"></i> Datos del estudiante</h3>
                        <div class="summary-data">
                            <p><span>Nombres y apellidos:</span><strong>{{ $nombres }} {{ $apellido_paterno }} {{ $apellido_materno }}</strong></p>
                            <p><span>DNI:</span><strong>{{ $dni }}</strong></p>
                            <p><span>Nacimiento:</span><strong>{{ $fecha_nacimiento }}</strong></p>
                            <p><span>Correo:</span><strong>{{ $correo }}</strong></p>
                            <p><span>Teléfono:</span><strong>{{ $celular }}</strong></p>
                        </div>
                    </div>

                    @if ($esMenorDeEdad)
                        <div class="summary-person">
                            <h3><i class="bi bi-people"></i> Datos del apoderado</h3>
                            <div class="summary-data">
                                <p><span>Apoderado:</span><strong>{{ $apoderado_nombres }} {{ $apoderado_apellido_paterno }} {{ $apoderado_apellido_materno }}</strong></p>
                                <p><span>DNI:</span><strong>{{ $apoderado_dni }}</strong></p>
                                <p><span>Parentesco:</span><strong>{{ $apoderado_parentesco }}</strong></p>
                                <p><span>Celular:</span><strong>{{ $apoderado_celular }}</strong></p>
                            </div>
                        </div>
                    @endif

                    <div class="summary-courses">
                        <h3><i class="bi bi-music-note-list"></i> Cursos seleccionados</h3>
                        @foreach ($cursosElegidos as $c)
                            <div class="summary-course-row" wire:key="sum-c-{{ $c->id_curso }}">
                                <div>
                                    <strong>{{ $c->nombre_curso }}</strong>
                                    <p style="color:#777;margin-top:4px;font-size:11px">{{ $c->nivel }} · 100% Virtual</p>
                                </div>
                                <strong>S/ {{ number_format((float) $c->costo, 2) }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="total-box final-total">
                        <span>Total a pagar</span>
                        <strong id="finalTotal">S/ {{ number_format($totalPagar, 2) }}</strong>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-back" wire:click="irAPaso({{ $esMenorDeEdad ? 4 : 3 }})">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </button>
                        <button type="button" class="btn-next" wire:click="irAPaso(6)">
                            Continuar al pago <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            <!-- PASO 6: PAGO POR YAPE -->
            @if ($pasoActual === 6)
                <section class="form-step active" data-page="6">
                    <div class="form-title">
                        <span class="form-icon"><i class="bi bi-credit-card"></i></span>
                        <div>
                            <h2>Método de pago (Yape)</h2>
                            <p>Realiza el pago por Yape y sube la captura de tu comprobante.</p>
                        </div>
                    </div>

                    <div class="payment-options" style="grid-template-columns:1fr">
                        <label class="payment-option">
                            <input type="radio" name="payment" value="Yape" checked>
                            <div class="payment-content">
                                <div class="yape-logo">yape</div>
                                <h3>Yape Oficial Dunamys: 952 233 266</h3>
                                <p>Envía el importe exacto y adjunta la captura del comprobante abajo.</p>
                            </div>
                            <i class="bi bi-check-circle-fill payment-check" style="opacity:1"></i>
                        </label>
                    </div>

                    <div class="payment-total">
                        <span>Importe de matrícula</span>
                        <strong id="paymentTotal">S/ {{ number_format($totalPagar, 2) }}</strong>
                    </div>

                    <div class="form-grid" style="margin-top:22px">
                        <div class="field">
                            <label for="numOperacion">Número de operación Yape (opcional)</label>
                            <input type="text" id="numOperacion" wire:model="numero_operacion" placeholder="Ej. 84920145">
                            @error('numero_operacion') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div class="field">
                            <label for="voucherUpload">Captura del comprobante Yape (JPG, PNG, WEBP máx. 2 MB) *</label>
                            <input type="file" id="voucherUpload" wire:model="comprobante" accept=".jpg,.jpeg,.png,.webp,image/*">
                            @error('comprobante') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    @error('apoderado_general')
                        <div class="minor-notice" style="background:#fff0f2;color:#c90020;margin-top:16px">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-actions">
                        <button type="button" class="btn-back" wire:click="irAPaso(5)">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </button>
                        <button type="button" class="btn-next" id="finishPayment" wire:click="registrarMatricula" wire:loading.attr="disabled">
                            Confirmar matrícula y enviar pago <i class="bi bi-lock-fill"></i>
                        </button>
                    </div>
                </section>
            @endif

        </div>

        <!-- SECCIÓN PARA VOLVER A SUBIR COMPROBANTE OBSERVADO -->
        <div class="enrollment-card" style="margin-top:28px;padding:26px 32px">
            <h3 style="font-size:16px;font-weight:900;margin-bottom:8px">¿Tu comprobante fue observado por Secretaría?</h3>
            <p style="font-size:12px;color:#666;margin-bottom:14px">Ingresa tu código de matrícula (ej. MAT-2026-001) para volver a subir tu comprobante corregido.</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <input type="text" wire:model="codigoBusquedaReenvio" placeholder="Código MAT-2026-..." style="padding:11px 14px;border:1px solid #ddd;border-radius:8px;font-size:12px;flex:1 1 200px">
                <button type="button" class="btn-next" wire:click="buscarMatriculaObservada">Buscar matrícula</button>
            </div>
            @error('reenvio')
                <p style="color:#ed102d;font-size:12px;font-weight:700;margin-top:8px">{{ $message }}</p>
            @enderror
            @if ($mensajeExitoReenvio)
                <p style="color:#16865a;font-size:12px;font-weight:700;margin-top:8px">{{ $mensajeExitoReenvio }}</p>
            @endif

            @if ($pagoObservadoId)
                <div style="margin-top:16px;padding:16px;background:#fff5f6;border:1px solid #f5d5da;border-radius:10px">
                    <strong style="color:#c90020;font-size:12px;display:block;margin-bottom:6px">Motivo de observación: {{ $motivoObservacionActual }}</strong>
                    <div class="form-grid" style="margin-top:10px">
                        <div class="field">
                            <label>Número de operación</label>
                            <input type="text" wire:model="nuevo_numero_operacion">
                        </div>
                        <div class="field">
                            <label>Nuevo comprobante (JPG, PNG, WEBP máx. 2 MB) *</label>
                            <input type="file" wire:model="nuevo_comprobante" accept=".jpg,.jpeg,.png,.webp,image/*">
                            @error('nuevo_comprobante') <small style="color:#ed102d;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <button type="button" class="btn-next" style="margin-top:14px" wire:click="reenviarComprobanteObservado">
                        Volver a enviar comprobante
                    </button>
                </div>
            @endif
        </div>
    @endif

    <p class="enrollment-footer">
        © <span id="year">{{ now()->year }}</span> DUNAMYS Academia Musical.
        Todos los derechos reservados.
    </p>

</main>
</div>
