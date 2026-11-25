<div class="est-recovery-layout">
  <div class="est-recovery-box">
    <!-- Barra Superior -->
    <div class="est-recovery-top">
      <a href="{{ route('login') }}" class="est-brand-mini">
        <img class="est-brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
        <span class="est-brand-name">Dunamys</span>
      </a>

      <a href="{{ route('login') }}" class="est-back-link">
        <svg class="i"><use href="#i-left"/></svg>
        <span>Volver a iniciar sesión</span>
      </a>
    </div>

    <!-- Encabezado de Página -->
    <div class="est-recovery-head">
      <h1>Recuperar <span>Contraseña</span></h1>
      <p id="estStepSubtitle">
        @if ($paso === 1)
          Ingresa el correo electrónico asociado a tu cuenta para enviarte un código de verificación.
        @elseif ($paso === 2)
          Ingresa el código que te enviamos para verificar tu identidad y restablecer tu clave.
        @else
          Código verificado con éxito. Ingresa tu nueva contraseña para completar la recuperación.
        @endif
      </p>
    </div>

    <!-- Alertas Generales -->
    @if ($alertaMensaje)
      <div id="estAlertBox" class="est-alert est-alert--{{ $alertaTipo }}" role="alert" aria-live="polite">
        <svg class="i" id="estAlertIcon"><use href="#i-alert"/></svg>
        <span id="estAlertMsg">{{ $alertaMensaje }}</span>
      </div>
    @endif

    <!-- ================= PASO 1: CORREO ================= -->
    <div id="estStepEmailWrap">
      <form id="estEmailForm" wire:submit="enviarCodigo" novalidate>
        <div class="est-form-group {{ $errors->has('correo') ? 'is-invalid' : '' }}" id="estGroupEmail">
          <label for="estEmail" class="est-label">Correo registrado</label>
          <div class="est-input-row">
            <input
              type="email"
              id="estEmail"
              wire:model="correo"
              class="est-control"
              placeholder="alumno@dunamys.edu.pe"
              autocomplete="email"
              @disabled($paso > 1)
              required
            >
            <button type="submit" class="btn btn--primary" id="estSendCodeBtn" style="width:auto;" @disabled($paso > 1)>
              Enviar código
            </button>
          </div>
          <div class="est-error" id="estEmailError" role="alert">
            {{ $errors->first('correo') ?: 'Este correo no está registrado en el sistema.' }}
          </div>
        </div>
      </form>
    </div>

    <!-- ================= PASO 2: CÓDIGO (Aparece en la misma pantalla) ================= -->
    @if ($paso >= 2)
      <div id="estStepCodeWrap">
        <div class="est-section-card" x-data="{
          digits: ['', '', '', '', '', ''],
          resendSeconds: 60,
          resendTimer: null,
          init() {
            this.startResendTimer();
          },
          startResendTimer() {
            clearInterval(this.resendTimer);
            this.resendSeconds = 60;
            this.resendTimer = setInterval(() => {
              if (this.resendSeconds > 0) {
                this.resendSeconds--;
              } else {
                clearInterval(this.resendTimer);
              }
            }, 1000);
          },
          syncCode() {
            $wire.set('codigoIngresado', this.digits.join(''), false);
          },
          onInput(idx, e) {
            const val = (e.target.value || '').toUpperCase().replace(/[^ABCDEFGHJKMNPQRSTUVWXYZ23456789]/g, '');
            this.digits[idx] = val.slice(-1);
            e.target.value = this.digits[idx];
            this.syncCode();
            if (this.digits[idx] && idx < 5) {
              this.$refs['code' + (idx + 1)].focus();
            }
          },
          onKeydown(idx, e) {
            if (e.key === 'Backspace' && !this.digits[idx] && idx > 0) {
              this.digits[idx - 1] = '';
              this.$refs['code' + (idx - 1)].focus();
              this.syncCode();
            }
          },
          onPaste(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').toUpperCase().replace(/[^ABCDEFGHJKMNPQRSTUVWXYZ23456789]/g, '');
            for (let i = 0; i < 6; i++) {
              this.digits[i] = text[i] || '';
            }
            this.syncCode();
          }
        }">
          <div class="est-sms-badge">
            <svg class="i"><use href="#i-mail"/></svg>
            <span>VERIFICACIÓN DE SEGURIDAD</span>
          </div>
          <p class="est-code-desc">
            Enviamos un código de seguridad de 6 caracteres a tu correo electrónico.
          </p>

          <form id="estCodeForm" wire:submit="verificarCodigo" novalidate>
            <label class="est-label" id="estCodeLabel">Ingresa el código de 6 caracteres:</label>
            <div class="est-code-grid" role="group" aria-labelledby="estCodeLabel">
              @for ($i = 0; $i < 6; $i++)
                <input
                  type="text"
                  maxlength="1"
                  class="est-code-input"
                  :class="{ 'is-filled': digits[{{ $i }}] !== '' }"
                  id="code{{ $i }}"
                  x-ref="code{{ $i }}"
                  x-model="digits[{{ $i }}]"
                  @input="onInput({{ $i }}, $event)"
                  @keydown="onKeydown({{ $i }}, $event)"
                  @paste="onPaste($event)"
                  aria-label="Dígito {{ $i + 1 }}"
                  autocomplete="off"
                  autocapitalize="characters"
                  @disabled($paso > 2 || $intentosRestantes <= 0)
                >
              @endfor
            </div>

            @error('codigoIngresado')
              <div class="est-error" id="estCodeError" role="alert" style="display:flex;">{{ $message }}</div>
            @enderror

            <div class="est-code-actions">
              <button
                type="button"
                class="est-resend-btn"
                id="estResendBtn"
                x-bind:disabled="resendSeconds > 0 || {{ $paso > 2 ? 'true' : 'false' }}"
                @click="if (resendSeconds <= 0) { $wire.reenviarCodigo(); digits = ['', '', '', '', '', '']; startResendTimer(); }"
              >
                <span x-show="resendSeconds > 0">Reenviar código (espera <span x-text="resendSeconds"></span>s)</span>
                <span x-show="resendSeconds <= 0" x-cloak>Reenviar código</span>
              </button>
              <span class="est-attempts-info" id="estAttemptsInfo">Intentos restantes: {{ $intentosRestantes }}</span>
            </div>

            <div style="margin-top:18px;">
              <button type="submit" class="btn btn--primary btn--full" id="estVerifyCodeBtn" @disabled($paso > 2 || $intentosRestantes <= 0)>
                Verificar código
              </button>
            </div>
          </form>
        </div>
      </div>
    @endif

    <!-- ================= PASO 3: NUEVA CONTRASEÑA ================= -->
    @if ($paso === 3)
      <div id="estStepPassWrap">
        <div class="est-section-card" x-data="{ pass: @entangle('nueva_contrasena') }">
          <div class="est-sms-badge" style="color:var(--green-soft);">
            <svg class="i"><use href="#i-check"/></svg>
            <span>CÓDIGO VERIFICADO</span>
          </div>
          <p class="est-code-desc" style="margin-bottom:18px;">
            Define tu nueva contraseña de acceso para la cuenta <strong id="estConfirmedEmail">{{ $correo }}</strong>.
          </p>

          <form id="estNewPassForm" wire:submit="guardarNuevaContrasena" novalidate>
            <div class="est-form-group {{ $errors->has('nueva_contrasena') ? 'is-invalid' : '' }}" id="estGroupNewPass">
              <label for="estNewPass" class="est-label">Nueva contraseña</label>
              <div class="est-input-wrap" x-data="{ show: false }">
                <input
                  type="password"
                  :type="show ? 'text' : 'password'"
                  id="estNewPass"
                  wire:model="nueva_contrasena"
                  x-model="pass"
                  class="est-control has-toggle"
                  placeholder="Mínimo 8 caracteres (letras y números)"
                  required
                  autocomplete="new-password"
                >
                <button
                  type="button"
                  class="est-toggle-pass"
                  @click="show = !show"
                  :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                  :title="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                  <svg class="i" x-show="!show"><use href="#i-eye"/></svg>
                  <svg class="i" x-show="show" x-cloak><use href="#i-eye-off"/></svg>
                </button>
              </div>
              <div class="est-error" id="estNewPassError" role="alert">
                {{ $errors->first('nueva_contrasena') ?: 'La contraseña debe tener al menos 8 caracteres, con letra y número.' }}
              </div>
              <ul class="est-pass-rules">
                <li id="ruleLen" class="est-pass-rule" :class="{ 'valid': (pass || '').length >= 8 }">Mínimo 8 caracteres</li>
                <li id="ruleLetter" class="est-pass-rule" :class="{ 'valid': /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(pass || '') }">Al menos una letra</li>
                <li id="ruleNum" class="est-pass-rule" :class="{ 'valid': /[0-9]/.test(pass || '') }">Al menos un número</li>
              </ul>
            </div>

            <div class="est-form-group {{ $errors->has('repetir_contrasena') ? 'is-invalid' : '' }}" id="estGroupRepeatPass">
              <label for="estRepeatPass" class="est-label">Repetir contraseña</label>
              <div class="est-input-wrap" x-data="{ show: false }">
                <input
                  type="password"
                  :type="show ? 'text' : 'password'"
                  id="estRepeatPass"
                  wire:model="repetir_contrasena"
                  class="est-control has-toggle"
                  placeholder="Repite exactamente tu nueva contraseña"
                  required
                  autocomplete="new-password"
                >
                <button
                  type="button"
                  class="est-toggle-pass"
                  @click="show = !show"
                  :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                  :title="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                  <svg class="i" x-show="!show"><use href="#i-eye"/></svg>
                  <svg class="i" x-show="show" x-cloak><use href="#i-eye-off"/></svg>
                </button>
              </div>
              <div class="est-error" id="estRepeatPassError" role="alert">
                {{ $errors->first('repetir_contrasena') ?: 'Las contraseñas no coinciden.' }}
              </div>
            </div>

            <button type="submit" class="btn btn--green btn--full" id="estSavePassBtn">
              Guardar nueva contraseña e ingresar
            </button>
          </form>
        </div>
      </div>
    @endif

  </div>
</div>
