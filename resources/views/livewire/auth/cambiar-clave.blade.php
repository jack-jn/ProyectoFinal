<div class="est-recovery-layout">
  <div class="est-recovery-box">
    <div class="est-recovery-top">
      <div class="est-brand-mini">
        <img class="est-brand-mark" src="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}" alt="Academia de Música Dunamys" style="object-fit:contain">
        <span class="est-brand-name">Dunamys</span>
      </div>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="est-back-link" style="background:none;border:0;">
          <svg class="i"><use href="#i-left"/></svg>
          <span>Cerrar sesión</span>
        </button>
      </form>
    </div>

    <div class="est-recovery-head">
      <h1>Actualizar <span>Contraseña</span></h1>
      <p>Por seguridad, debes cambiar tu contraseña inicial (DNI) antes de acceder al aula virtual.</p>
    </div>

    <div class="est-section-card" x-data="{ pass: @entangle('nueva_contrasena') }">
      <div class="est-sms-badge" style="color:var(--gold-soft);">
        <svg class="i"><use href="#i-lock"/></svg>
        <span>PRIMER INGRESO OBLIGATORIO</span>
      </div>
      <p class="est-code-desc" style="margin-bottom:18px;">
        Define tu nueva contraseña personal para la cuenta <strong>{{ auth()->user()?->correo_usuario }}</strong>.
      </p>

      <form wire:submit="guardarContrasena" novalidate>
        <div class="est-form-group {{ $errors->has('nueva_contrasena') ? 'is-invalid' : '' }}">
          <label for="estChangeNewPass" class="est-label">Nueva contraseña</label>
          <div class="est-input-wrap" x-data="{ show: false }">
            <input
              type="password"
              :type="show ? 'text' : 'password'"
              id="estChangeNewPass"
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
          <div class="est-error" role="alert">
            {{ $errors->first('nueva_contrasena') ?: 'Este campo es obligatorio' }}
          </div>
          <ul class="est-pass-rules">
            <li class="est-pass-rule" :class="{ 'valid': (pass || '').length >= 8 }">Mínimo 8 caracteres</li>
            <li class="est-pass-rule" :class="{ 'valid': /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(pass || '') }">Al menos una letra</li>
            <li class="est-pass-rule" :class="{ 'valid': /[0-9]/.test(pass || '') }">Al menos un número</li>
          </ul>
        </div>

        <div class="est-form-group {{ $errors->has('repetir_contrasena') ? 'is-invalid' : '' }}">
          <label for="estChangeRepeatPass" class="est-label">Repetir contraseña</label>
          <div class="est-input-wrap" x-data="{ show: false }">
            <input
              type="password"
              :type="show ? 'text' : 'password'"
              id="estChangeRepeatPass"
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
          <div class="est-error" role="alert">
            {{ $errors->first('repetir_contrasena') ?: 'Las contraseñas no coinciden.' }}
          </div>
        </div>

        <button type="submit" class="btn btn--green btn--full">
          Guardar nueva contraseña e ingresar
        </button>
      </form>
    </div>
  </div>
</div>
