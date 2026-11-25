@props(['mensaje' => null, 'tipo' => 'success'])

@if (is_string($mensaje) && trim($mensaje) !== '')
  <span
    style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0"
    role="status"
    aria-live="polite"
    wire:key="toast-bridge-{{ md5($mensaje.'|'.$tipo) }}"
    x-data
    x-init="window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(trim($mensaje)), type: @js($tipo) } }))"
  >{{ $mensaje }}</span>
@endif
