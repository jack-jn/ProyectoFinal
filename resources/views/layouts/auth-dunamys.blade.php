<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Iniciar Sesión | Academia de Música Dunamys' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
  --red:#CE2127; --red-dark:#A9171D; --red-soft:#FCEBEC; --red-border:#F6C6C8; --red-on-dark:#FF6B70;
  --ink:#191718; --ink-2:#242022; --ink-3:#353032;
  --muted:#9E9699; --line:rgba(255,255,255,.12); --bg:#121011; --surface:#1D191B; --white:#fff;
  --green:#168854; --green-soft:#E3F6ED; --green-dark:#0F683F; --green-border:#B4E6CE;
  --gold:#D9900D; --gold-soft:#FEF3DC; --gold-dark:#855400; --gold-border:#F7D794;
  --shadow-sm:0 2px 8px rgba(0,0,0,.25);
  --shadow-lg:0 24px 60px rgba(0,0,0,.45);
  --font-display:'Sora','Figtree',system-ui,sans-serif;
  --font-body:'Figtree',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
}
*{box-sizing:border-box}
html,body{height:100%;margin:0;padding:0}
body{
  background:var(--bg);
  color:var(--white);
  font:400 15px/1.5 var(--font-body);
  display:flex;
  min-height:100vh;
}
button,input,select,textarea{font:inherit;color:inherit}
button{cursor:pointer}
[hidden]{display:none !important}
:focus-visible{outline:3px solid var(--red-on-dark);outline-offset:2px}

.est-login-layout{
  display:grid;
  grid-template-columns:1.1fr 1fr;
  width:100%;
  min-height:100vh;
}

/* Panel Izquierdo: Hero de Marca */
.est-login-hero{
  position:relative;
  background:radial-gradient(circle at 20% 30%, #5E1014 0%, #280709 50%, #121011 100%);
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  padding:48px;
  overflow:hidden;
  border-right:1px solid var(--line);
}
.est-login-hero::before{
  content:"";
  position:absolute;
  inset:0;
  background-image:radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
  background-size:24px 24px;
  opacity:.6;
  pointer-events:none;
}
.est-login-hero__glow{
  position:absolute;
  bottom:-100px;
  left:-100px;
  width:420px;
  height:420px;
  border-radius:50%;
  background:radial-gradient(circle, rgba(206,33,39,.35) 0%, transparent 70%);
  filter:blur(40px);
  pointer-events:none;
}
.est-brand-badge{
  display:inline-flex;
  align-items:center;
  gap:12px;
  background:rgba(25,23,24,.6);
  backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,.14);
  padding:10px 18px;
  border-radius:14px;
  width:fit-content;
  position:relative;
  z-index:2;
}
.est-brand-mark{width:40px;height:40px;flex:none}
.est-brand-name{font:700 22px/1 var(--font-display);color:#fff;letter-spacing:-.02em}

.est-hero-content{
  position:relative;
  z-index:2;
  max-width:480px;
  margin:40px 0;
}
.est-hero-tag{
  display:inline-flex;
  align-items:center;
  gap:6px;
  background:rgba(206,33,39,.2);
  border:1px solid rgba(206,33,39,.4);
  color:var(--red-on-dark);
  font-size:12px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.08em;
  padding:4px 12px;
  border-radius:99px;
  margin-bottom:16px;
}
.est-hero-title{
  margin:0 0 16px;
  font:700 34px/1.18 var(--font-display);
  letter-spacing:-.025em;
  color:#fff;
}
.est-hero-title span{
  color:var(--red-on-dark);
}
.est-hero-desc{
  margin:0;
  font-size:16px;
  line-height:1.6;
  color:#D3CDCF;
}

.est-hero-footer{
  position:relative;
  z-index:2;
  font-size:13px;
  color:var(--muted);
  border-top:1px solid rgba(255,255,255,.08);
  padding-top:20px;
}
.est-hero-footer strong{
  color:#fff;
}

/* Panel Derecho: Formulario */
.est-login-panel{
  background:var(--surface);
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  padding:48px 32px;
  position:relative;
}
.est-login-box{
  width:100%;
  max-width:420px;
}
.est-login-header{
  margin-bottom:32px;
}
.est-login-header h1{
  margin:0 0 8px;
  font:700 26px/1.2 var(--font-display);
  letter-spacing:-.02em;
  color:#fff;
}
.est-login-header h1 span{
  color:var(--red-on-dark);
}
.est-login-header p{
  margin:0;
  font-size:14.5px;
  color:var(--muted);
}

/* Layout de Recuperación / Cambio de clave */
.est-recovery-layout{
  display:grid;
  grid-template-columns:1fr;
  width:100%;
  min-height:100vh;
  place-items:center;
  padding:32px 16px;
  background:radial-gradient(circle at 50% 20%, #2A090B 0%, #150E10 50%, #100C0D 100%);
}
.est-recovery-box{
  width:100%;
  max-width:540px;
  background:var(--surface);
  border:1px solid rgba(255,255,255,.12);
  border-radius:20px;
  padding:36px 40px;
  box-shadow:var(--shadow-lg);
  position:relative;
}
.est-recovery-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-bottom:24px;
  padding-bottom:18px;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.est-brand-mini{
  display:inline-flex;
  align-items:center;
  gap:10px;
  text-decoration:none;
}
.est-brand-mini .est-brand-mark{width:34px;height:34px;flex:none}
.est-brand-mini .est-brand-name{font:700 19px/1 var(--font-display);color:#fff}
.est-back-link{
  display:inline-flex;
  align-items:center;
  gap:6px;
  color:var(--muted);
  font-size:13.5px;
  font-weight:600;
  text-decoration:none;
  transition:.15s;
}
.est-back-link:hover{
  color:#fff;
}
.est-recovery-head{
  margin-bottom:26px;
}
.est-recovery-head h1{
  margin:0 0 6px;
  font:700 26px/1.2 var(--font-display);
  letter-spacing:-.02em;
  color:#fff;
}
.est-recovery-head h1 span{
  color:var(--red-on-dark);
}
.est-recovery-head p{
  margin:0;
  font-size:14.5px;
  color:var(--muted);
  line-height:1.5;
}

.est-form-group{
  margin-bottom:20px;
}
.est-label{
  display:block;
  font-weight:600;
  font-size:13.5px;
  margin-bottom:8px;
  color:#EDEAEB;
}
.est-input-wrap{
  position:relative;
  display:flex;
  align-items:center;
}
.est-input-row{
  display:flex;
  gap:10px;
}
.est-control{
  width:100%;
  height:44px;
  border:1px solid rgba(255,255,255,.16);
  border-radius:11px;
  background:var(--ink-2);
  color:#fff;
  padding:0 14px;
  transition:all .16s;
  font-size:14.5px;
}
.est-control:hover:not(:disabled){
  border-color:rgba(255,255,255,.3);
}
.est-control:focus{
  border-color:var(--red-on-dark);
  box-shadow:0 0 0 3px rgba(206,33,39,.25);
  outline:none;
}
.est-control:disabled{
  opacity:.6;
  cursor:not-allowed;
}
.est-control.has-toggle{
  padding-right:44px;
}
.est-toggle-pass{
  position:absolute;
  right:6px;
  width:34px;
  height:34px;
  background:none;
  border:0;
  border-radius:8px;
  color:var(--muted);
  display:grid;
  place-items:center;
  padding:0;
  transition:.15s;
}
.est-toggle-pass:hover{
  color:#fff;
  background:rgba(255,255,255,.08);
}

.est-error{
  display:none;
  align-items:center;
  gap:6px;
  margin:6px 0 0;
  color:var(--red-on-dark);
  font-size:12.5px;
  font-weight:600;
}
.est-error::before{
  content:"";
  width:6px;
  height:6px;
  border-radius:50%;
  background:var(--red-on-dark);
  flex:none;
}
.is-invalid .est-control{
  border-color:var(--red-on-dark);
  background:rgba(206,33,39,.08);
}
.is-invalid .est-error{
  display:flex;
}

.est-form-meta{
  display:flex;
  justify-content:flex-end;
  align-items:center;
  margin-top:-8px;
  margin-bottom:24px;
}
.est-link{
  color:var(--red-on-dark);
  font-size:13.5px;
  font-weight:600;
  text-decoration:none;
  transition:.15s;
}
.est-link:hover{
  text-decoration:underline;
  color:#fff;
}

.est-alert{
  border-radius:12px;
  padding:12px 14px;
  font-size:13.5px;
  font-weight:500;
  margin-bottom:20px;
  display:flex;
  align-items:flex-start;
  gap:10px;
  line-height:1.45;
}
.est-alert--danger{
  background:rgba(206,33,39,.15);
  border:1px solid rgba(206,33,39,.4);
  color:#FFD2D4;
}
.est-alert--warning{
  background:rgba(217,144,13,.15);
  border:1px solid rgba(217,144,13,.4);
  color:#FFE5B4;
}
.est-alert--success{
  background:rgba(22,136,84,.18);
  border:1px solid rgba(22,136,84,.45);
  color:#D1F0E0;
}
.est-alert--demo{
  background:rgba(255,255,255,.07);
  border:1px dashed rgba(255,255,255,.25);
  color:#fff;
  display:flex;
  flex-direction:column;
  gap:4px;
}
.est-alert--demo strong{
  color:var(--gold-soft);
  font-size:13px;
  text-transform:uppercase;
  letter-spacing:.05em;
}
.est-demo-code{
  font-family:monospace;
  font-size:22px;
  font-weight:700;
  letter-spacing:.25em;
  color:var(--red-on-dark);
}

.est-section-card{
  background:var(--ink-2);
  border:1px solid rgba(255,255,255,.1);
  border-radius:14px;
  padding:22px;
  margin-bottom:20px;
  animation:estFadeIn .2s ease-out;
}
@keyframes estFadeIn{
  from{opacity:0;transform:translateY(6px)}
  to{opacity:1;transform:none}
}
.est-sms-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  font-size:12px;
  font-weight:700;
  color:var(--gold-soft);
  margin-bottom:6px;
}
.est-code-desc{
  margin:0 0 16px;
  font-size:13.5px;
  color:#D3CDCF;
}
.est-code-desc strong{
  color:#fff;
}
.est-code-grid{
  display:grid;
  grid-template-columns:repeat(6,minmax(0,1fr));
  gap:8px;
  width:100%;
  margin-bottom:14px;
}
.est-code-input{
  width:100%;
  min-width:0;
  height:48px;
  padding:0;
  text-align:center;
  font-size:20px;
  font-weight:600;
  text-transform:uppercase;
  border:1.5px solid rgba(255,255,255,.2);
  border-radius:10px;
  background:var(--surface);
  color:#fff;
  transition:all .15s;
}
.est-code-input:focus{
  border-color:var(--red-on-dark);
  background:rgba(206,33,39,.1);
  box-shadow:0 0 0 3px rgba(206,33,39,.25);
  outline:none;
}
.est-code-input.is-filled{
  border-color:rgba(255,255,255,.4);
  background:rgba(255,255,255,.05);
}
.est-code-actions{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  margin-top:12px;
}
.est-resend-btn{
  background:none;
  border:0;
  color:var(--red-on-dark);
  font-size:13px;
  font-weight:600;
  padding:4px 0;
  transition:.15s;
}
.est-resend-btn:disabled{
  color:var(--muted);
  cursor:not-allowed;
  opacity:.7;
}
.est-attempts-info{
  font-size:12.5px;
  color:var(--muted);
}

.est-pass-rules{
  list-style:none;
  padding:0;
  margin:8px 0 0;
  display:grid;
  gap:4px;
  font-size:12px;
  color:var(--muted);
}
.est-pass-rule{
  display:flex;
  align-items:center;
  gap:6px;
}
.est-pass-rule.valid{
  color:var(--green-soft);
}
.est-pass-rule.valid::before{
  content:"✓";
  color:var(--green);
  font-weight:700;
}
.est-pass-rule:not(.valid)::before{
  content:"○";
  color:var(--muted);
}

.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  height:44px;
  padding:0 20px;
  border-radius:11px;
  border:1px solid transparent;
  font-weight:600;
  font-size:14.5px;
  white-space:nowrap;
  transition:all .16s;
  width:100%;
}
.btn--primary{
  background:var(--red);
  color:#fff;
  box-shadow:0 4px 14px rgba(206,33,39,.35);
}
.btn--primary:hover:not(:disabled){
  background:var(--red-dark);
  transform:translateY(-1px);
}
.btn--green{
  background:var(--green);
  color:#fff;
  box-shadow:0 4px 12px rgba(22,136,84,.22);
}
.btn--green:hover:not(:disabled){
  background:var(--green-dark);
  transform:translateY(-1px);
}
.btn--full{
  width:100%;
}
.btn:disabled{
  opacity:.5;
  cursor:not-allowed;
  transform:none !important;
}

.i{
  width:18px;
  height:18px;
  flex:none;
  fill:none;
  stroke:currentColor;
  stroke-width:2;
  stroke-linecap:round;
  stroke-linejoin:round;
}

@media (max-width:1024px){
  .est-login-layout{grid-template-columns:1fr}
  .est-login-hero{display:none}
  .est-login-panel{padding:36px 20px}
}
@media (max-width:560px){
  .est-recovery-layout{padding:20px 12px}
  .est-recovery-box{padding:24px 16px;max-width:95vw}
  .est-section-card{padding:18px 14px}
  .est-code-grid{gap:6px}
  .est-input-row{flex-direction:column}
}
</style>
@stack('styles')
@livewireStyles
</head>
<body>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="i-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><line x1="1" y1="1" x2="23" y2="23"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="i-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></symbol>
  <symbol id="i-left" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="i-mail" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></symbol>
  <symbol id="i-key" viewBox="0 0 24 24"><path d="M21 2l-2 2m-1.5 1.5L14 9l-3-3L8 9 5 6 2 9l3 3 1.5-1.5L9 13l2.5-2.5 3 3 6.5-6.5z"/></symbol>
</svg>

{{ $slot }}

<x-toast-dunamys />
@stack('scripts')
@livewireScripts
</body>
</html>
