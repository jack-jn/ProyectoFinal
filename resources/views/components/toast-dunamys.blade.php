@php
    $sessionToasts = [];
    foreach ([
        'success' => 'success',
        'status' => 'success',
        'mensaje' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
    ] as $flashKey => $flashType) {
        $flashVal = session($flashKey);
        if (is_string($flashVal) && trim($flashVal) !== '') {
            $sessionToasts[] = ['message' => trim($flashVal), 'type' => $flashType];
        }
    }
@endphp

<style>
  [x-cloak] { display: none !important; }
  .dunamys-toasts {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: min(380px, calc(100vw - 2rem));
    pointer-events: none;
  }
  .dunamys-toast {
    pointer-events: auto;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: start;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    box-shadow: 0 14px 36px rgba(0, 0, 0, .28);
    font: 500 13.5px/1.45 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    animation: dunamysToastIn .18s ease-out;
  }
  .dunamys-toast--success {
    background: #191718;
    color: #FFFFFF;
    border-left: 5px solid #16A34A;
  }
  .dunamys-toast--error {
    background: #CE2127;
    color: #FFFFFF;
    border-left: 5px solid #7F1D1D;
  }
  .dunamys-toast--warning {
    background: #FEF3DC;
    color: #191718;
    border: 1px solid #F7D794;
    border-left: 5px solid #D9900D;
  }
  .dunamys-toast--info {
    background: #FFFFFF;
    color: #191718;
    border: 1px solid #E5E0E2;
    border-left: 5px solid #191718;
  }
  .dunamys-toast__icon {
    font-size: 14px;
    line-height: 1.4;
    font-weight: 700;
  }
  .dunamys-toast__msg {
    margin: 0;
    color: inherit;
    word-break: break-word;
  }
  .dunamys-toast__close {
    border: 0;
    background: transparent;
    color: inherit;
    opacity: .78;
    font-size: 16px;
    line-height: 1;
    padding: 2px 6px;
    border-radius: 6px;
    cursor: pointer;
  }
  .dunamys-toast__close:hover {
    opacity: 1;
    background: rgba(128, 128, 128, .18);
  }
  @keyframes dunamysToastIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: none; }
  }
</style>

<div
  class="dunamys-toasts"
  role="region"
  aria-label="Notificaciones"
  aria-live="polite"
  x-data="{
    toasts: [],
    nextId: 1,
    timers: {},
    init() {
      const initial = @js($sessionToasts);
      initial.forEach((item) => this.push(item));

      if (window.Livewire && typeof window.Livewire.on === 'function') {
        window.Livewire.on('toast', (payload) => this.handle(payload));
        window.Livewire.on('notify', (payload) => this.handle(payload));
      }
    },
    handle(detail) {
      const src = Array.isArray(detail) ? detail[0] : detail;
      const rawMsg = typeof src === 'string'
        ? src
        : (src?.message ?? src?.mensaje ?? src?.text ?? '');
      const message = String(rawMsg ?? '').trim();
      if (!message) {
        return;
      }
      const rawType = typeof src === 'object' && src !== null
        ? (src.type ?? src.tipo ?? 'success')
        : 'success';
      const type = ['success', 'error', 'warning', 'info'].includes(rawType) ? rawType : 'success';
      this.push({ message, type });
    },
    push({ message, type = 'success' }) {
      const clean = String(message ?? '').trim();
      if (!clean) {
        return;
      }
      const id = this.nextId++;
      this.toasts.push({ id, message: clean, type });
      this.timers[id] = setTimeout(() => this.remove(id), 3500);
    },
    remove(id) {
      if (this.timers[id]) {
        clearTimeout(this.timers[id]);
        delete this.timers[id];
      }
      this.toasts = this.toasts.filter((t) => t.id !== id);
    }
  }"
  x-on:toast.window="handle($event.detail)"
  x-on:notify.window="handle($event.detail)"
  x-on:notificar.window="handle($event.detail)"
>
  <template x-for="item in toasts" :key="item.id">
    <div
      x-cloak
      class="dunamys-toast"
      :class="'dunamys-toast--' + item.type"
      role="status"
    >
      <span class="dunamys-toast__icon" x-text="item.type === 'error' ? '✕' : (item.type === 'warning' ? '⚠' : (item.type === 'info' ? 'ℹ' : '✔'))"></span>
      <p class="dunamys-toast__msg" x-text="item.message"></p>
      <button
        type="button"
        class="dunamys-toast__close"
        x-on:click="remove(item.id)"
        aria-label="Cerrar notificación"
      >×</button>
    </div>
  </template>
</div>
