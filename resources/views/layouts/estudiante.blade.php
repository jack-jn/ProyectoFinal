<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Portal del Estudiante | Academia de Música Dunamys' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/logo-dunamys-aa834c9c.jpg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
  --red:#CE2127; --red-dark:#A9171D; --red-soft:#FCEBEC; --red-border:#F6C6C8; --red-on-dark:#FF6B70;
  --ink:#191718; --ink-2:#2B2729; --ink-3:#443F42;
  --muted:#6B6568; --line:#E7E3E4; --bg:#F4F2F3; --surface:#FAF9F9; --white:#fff;
  --green:#168854; --green-dark:#0F683F; --green-soft:#E3F6ED; --green-border:#B4E6CE;
  --gold:#D9900D; --gold-dark:#855400; --gold-soft:#FEF3DC; --gold-border:#F7D794;
  --blue:#2563EB; --blue-dark:#1D4ED8; --blue-soft:#EFF6FF; --blue-border:#BFDBFE;
  --shadow-sm:0 2px 8px rgba(25,23,24,.04);
  --shadow-md:0 10px 28px rgba(25,23,24,.08);
  --shadow-lg:0 24px 60px rgba(25,23,24,.24);
  --font-display:'Sora','Figtree',system-ui,sans-serif;
  --font-body:'Figtree',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:var(--bg);color:var(--ink);font:400 15px/1.5 var(--font-body)}
body.no-scroll{overflow:hidden}
button,input,select,textarea{font:inherit;color:inherit}
button{cursor:pointer}
[hidden]{display:none !important}
:focus-visible{outline:3px solid rgba(206,33,39,.4);outline-offset:2px}
.i{width:18px;height:18px;flex:none;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* ---------- Layout Principal ---------- */
.app{display:grid;grid-template-columns:272px 1fr;min-height:100vh}
.sidebar{background:var(--ink);color:#fff;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;padding:20px 16px 18px;z-index:40}
.scrim{display:none}
.main{padding:26px 36px 48px;min-width:0;max-width:1580px;width:100%}

/* ---------- Barra Lateral ---------- */
.sidebar-top{display:flex;align-items:center;gap:10px}
.sidebar-top .brand{flex:1;min-width:0}
.brand{background:#fff;border-radius:14px;padding:10px 14px;display:flex;align-items:center;gap:10px;min-height:60px}
.brand-mark{width:38px;height:38px;flex:none}
.brand-name{font:700 20px/1 var(--font-display);color:var(--ink);letter-spacing:-.02em}
.user{display:flex;flex-direction:column;align-items:center;text-align:center;padding:22px 0 18px;border-bottom:1px solid rgba(255,255,255,.1)}
.avatar{width:64px;height:64px;border-radius:50%;border:2px solid var(--red);display:grid;place-items:center;background:var(--ink-2);color:#fff;font:700 22px/1 var(--font-display)}
.user small{margin-top:10px;font-size:12px;letter-spacing:.06em;color:#BDB6B9}
.user strong{font:700 16px/1.3 var(--font-display);color:var(--red-on-dark)}
.nav{display:flex;flex-direction:column;gap:6px;padding:18px 0;flex:1}
.nav-item{display:flex;align-items:center;gap:12px;width:100%;padding:12px 14px;border:0;border-radius:12px;background:transparent;color:#D3CDCF;text-align:left;font-weight:500;line-height:1.25;transition:.16s}
.nav-item .i{width:20px;height:20px}
.nav-item:hover{background:rgba(255,255,255,.08);color:#fff}
.nav-item.is-active{background:var(--red);color:#fff;font-weight:600;box-shadow:0 6px 18px rgba(206,33,39,.35)}
.nav-item .chev{margin-left:auto;width:16px;height:16px}
.sidebar-foot{border-top:1px solid rgba(255,255,255,.1);padding-top:14px;text-align:center;font:600 13px/1.4 var(--font-body);margin-bottom:12px}
.sidebar-foot span{display:block;color:var(--red-on-dark)}
.sidebar-logout{
  border:1px solid rgba(255,255,255,.16);
  background:rgba(255,255,255,.06);
  color:#EDEAEB;
  border-radius:10px;
  padding:9px 12px;
  font-size:13px;
  font-weight:600;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  transition:.15s;
}
.sidebar-logout:hover{
  background:rgba(206,33,39,.25);
  border-color:var(--red-on-dark);
  color:#fff;
}

/* ---------- Encabezados ---------- */
.page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.page-head h1{margin:0;font:700 26px/1.2 var(--font-display);letter-spacing:-.02em}
.page-head h1 span{color:var(--red)}
.page-head p{margin:5px 0 0;color:var(--muted);font-size:14px}
.head-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.btn.menu-btn{display:none}

.course-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 14px;
  background:#fff;
  border:1px solid var(--line);
  border-radius:999px;
  font-size:13px;
  font-weight:600;
  color:var(--ink);
  box-shadow:var(--shadow-sm);
}
.course-chip__dot{
  width:8px;
  height:8px;
  border-radius:50%;
  background:var(--red);
}

/* ---------- Botones y Controles ---------- */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:40px;padding:0 16px;border-radius:10px;border:1px solid transparent;font-weight:600;font-size:13.5px;white-space:nowrap;transition:all .16s;text-decoration:none}
.btn .i{width:16px;height:16px}
.btn:disabled{opacity:.48;cursor:not-allowed;transform:none !important}
.btn--primary{background:var(--red);color:#fff;box-shadow:0 4px 12px rgba(206,33,39,.22)}
.btn--primary:hover:not(:disabled){background:var(--red-dark);transform:translateY(-1px)}
.btn--dark{background:var(--ink);color:#fff}
.btn--dark:hover:not(:disabled){background:#000;transform:translateY(-1px)}
.btn--green{background:var(--green);color:#fff;box-shadow:0 4px 12px rgba(22,136,84,.22)}
.btn--green:hover:not(:disabled){background:var(--green-dark);transform:translateY(-1px)}
.btn--outline{background:#fff;border-color:#D4CFD1;color:var(--ink)}
.btn--outline:hover:not(:disabled){border-color:var(--ink);background:var(--surface)}
.btn--ghost{background:#EFEBEC;color:var(--ink)}
.btn--ghost:hover:not(:disabled){background:#E2DCDD}
.btn--soft-red{background:var(--red-soft);color:var(--red-dark);border-color:var(--red-border)}
.btn--soft-red:hover:not(:disabled){background:#F9D7D9}
.btn--sm{height:34px;padding:0 12px;font-size:12.5px;border-radius:8px}

.pill{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;font-size:11.5px;font-weight:600;border:1px solid #D4CFD1;background:#fff;white-space:nowrap}
.pill.green{background:var(--green-soft);border-color:var(--green-border);color:var(--green-dark)}
.pill.gold{background:var(--gold-soft);border-color:var(--gold-border);color:var(--gold-dark)}
.pill.red{background:var(--red-soft);border-color:var(--red-border);color:var(--red-dark)}
.pill.blue{background:var(--blue-soft);border-color:var(--blue-border);color:var(--blue-dark)}
.pill.dark{background:var(--ink);border-color:var(--ink);color:#fff}
.pill.neutral{background:#EFEBEC;border-color:#DDD8DA;color:var(--muted)}

/* ---------- Layout Maestro-Detalle ---------- */
.est-master-detail{
  display:grid;
  grid-template-columns:320px minmax(0,1fr);
  gap:22px;
  align-items:start;
}

.est-side-list{
  background:#fff;
  border:1px solid var(--line);
  border-radius:18px;
  padding:16px;
  box-shadow:var(--shadow-sm);
  display:flex;
  flex-direction:column;
  gap:10px;
  position:sticky;
  top:20px;
  max-height:calc(100vh - 40px);
  overflow-y:auto;
}
.est-side-list__head{
  padding:4px 6px 12px;
  border-bottom:1px solid var(--line);
  margin-bottom:4px;
}
.est-side-list__head h2{
  margin:0;
  font:700 17px/1.2 var(--font-display);
  color:var(--ink);
}
.est-side-list__head p{
  margin:3px 0 0;
  font-size:12px;
  color:var(--muted);
}

.est-item-card{
  background:var(--surface);
  border:1.5px solid var(--line);
  border-radius:13px;
  padding:12px 14px;
  text-align:left;
  width:100%;
  display:flex;
  align-items:center;
  gap:12px;
  transition:all .16s;
  cursor:pointer;
  position:relative;
}
.est-item-card:hover{
  background:#fff;
  border-color:#CFC8CB;
  transform:translateY(-1px);
  box-shadow:var(--shadow-sm);
}
.est-item-card.is-active{
  border-color:var(--red);
  background:linear-gradient(180deg,#FFF9F9 0%,#FFFFFF 100%);
  box-shadow:0 6px 16px rgba(206,33,39,.12);
}
.est-item-card.is-active::before{
  content:"";
  position:absolute;
  left:0;
  top:12px;
  bottom:12px;
  width:4px;
  border-radius:0 4px 4px 0;
  background:var(--red);
}
.est-item-card.is-locked{
  opacity:.65;
  background:#F2EFF0;
}
.est-item-card__badge{
  width:36px;
  height:36px;
  border-radius:10px;
  background:#EAE6E7;
  color:var(--ink);
  font:700 13px/1 var(--font-display);
  display:grid;
  place-items:center;
  flex:none;
}
.est-item-card.is-active .est-item-card__badge{
  background:var(--red);
  color:#fff;
}
.est-item-card__info{
  min-width:0;
  flex:1;
}
.est-item-card__top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:6px;
  margin-bottom:2px;
}
.est-item-card__num{
  font-size:11.5px;
  font-weight:700;
  color:var(--muted);
  text-transform:uppercase;
  letter-spacing:.04em;
}
.est-item-card__title{
  font:700 14px/1.3 var(--font-display);
  color:var(--ink);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  display:block;
}
.est-item-card__status{
  margin-left:auto;
  flex:none;
}

.est-detail-panel{
  background:#fff;
  border:1px solid var(--line);
  border-radius:18px;
  box-shadow:var(--shadow-sm);
  overflow:hidden;
}
.est-detail-hero{
  padding:24px 28px;
  background:linear-gradient(180deg,#FFFFFF 0%,#FAF8F9 100%);
  border-bottom:1px solid var(--line);
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:16px;
  flex-wrap:wrap;
}
.est-detail-hero__main h2{
  margin:4px 0 6px;
  font:700 22px/1.25 var(--font-display);
  color:var(--ink);
}
.est-detail-hero__meta{
  font-size:12.5px;
  color:var(--muted);
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
}
.est-detail-body{
  padding:26px 28px;
}

.est-desc-box{
  background:var(--surface);
  border:1px solid var(--line);
  border-radius:12px;
  padding:14px 18px;
  font-size:14px;
  line-height:1.6;
  color:var(--ink-2);
  margin-bottom:26px;
}

.est-section-title{
  margin:0 0 16px;
  font:700 17px/1.2 var(--font-display);
  color:var(--ink);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.est-materials-grid{
  display:grid;
  gap:14px;
}
.est-material-card{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px 18px;
  display:grid;
  grid-template-columns:auto 1fr auto;
  gap:16px;
  align-items:center;
  transition:all .16s;
  box-shadow:0 2px 6px rgba(25,23,24,.02);
}
.est-material-card:hover{
  border-color:#CFC8CB;
  box-shadow:var(--shadow-sm);
}
.est-mat-icon{
  width:44px;
  height:44px;
  border-radius:12px;
  display:grid;
  place-items:center;
  font-size:18px;
  flex:none;
}
.est-mat-icon.video{background:var(--red-soft);color:var(--red)}
.est-mat-icon.pdf{background:var(--blue-soft);color:var(--blue-dark)}
.est-mat-icon.partitura{background:var(--gold-soft);color:var(--gold-dark)}

.est-mat-info strong{
  display:block;
  font:700 15px/1.3 var(--font-display);
  color:var(--ink);
  margin-bottom:3px;
}
.est-mat-info p{
  margin:0 0 6px;
  font-size:13px;
  color:var(--ink-3);
  line-height:1.45;
}
.est-mat-meta{
  display:flex;
  align-items:center;
  gap:10px;
  font-size:12px;
  color:var(--muted);
  flex-wrap:wrap;
}
.est-mat-actions{
  display:flex;
  align-items:center;
  gap:8px;
}

.est-state-panel{
  padding:60px 24px;
  text-align:center;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
}
.est-state-icon{
  width:64px;
  height:64px;
  border-radius:18px;
  display:grid;
  place-items:center;
  margin-bottom:18px;
}
.est-state-icon.lock{background:var(--surface);border:1px solid var(--line);color:var(--muted)}
.est-state-icon.danger{background:var(--red-soft);color:var(--red)}
.est-state-panel h3{
  margin:0 0 8px;
  font:700 20px/1.3 var(--font-display);
  color:var(--ink);
}
.est-state-panel p{
  margin:0 0 20px;
  max-width:440px;
  color:var(--muted);
  font-size:14px;
  line-height:1.55;
}

.est-task-consigna{
  background:linear-gradient(135deg,#FFFDFD 0%,#FAF7F8 100%);
  border:1px solid var(--red-border);
  border-left:5px solid var(--red);
  border-radius:14px;
  padding:20px 22px;
  margin-bottom:24px;
}
.est-task-consigna__head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:12px;
  flex-wrap:wrap;
}
.est-deadline-badge{
  font-size:12.5px;
  font-weight:700;
  display:inline-flex;
  align-items:center;
  gap:6px;
  color:var(--red-dark);
}
.est-deadline-badge.ontime{color:var(--ink-2)}

.est-upload-area{
  border:2px dashed #CFC9CB;
  border-radius:14px;
  background:var(--surface);
  padding:26px 20px;
  text-align:center;
  cursor:pointer;
  transition:all .18s;
  margin-top:14px;
}
.est-upload-area:hover, .est-upload-area.is-dragover{
  border-color:var(--red);
  background:var(--red-soft);
}
.est-upload-area__icon{
  width:46px;
  height:46px;
  border-radius:12px;
  background:#fff;
  color:var(--red);
  display:grid;
  place-items:center;
  margin:0 auto 10px;
  box-shadow:var(--shadow-sm);
}
.est-upload-area strong{
  display:block;
  font-size:14.5px;
  color:var(--ink);
  margin-bottom:4px;
}
.est-upload-area small{
  display:block;
  font-size:12px;
  color:var(--muted);
}

.est-files-staged{
  margin-top:18px;
  display:grid;
  gap:8px;
}
.est-staged-file{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  background:#fff;
  border:1px solid var(--line);
  border-radius:10px;
  padding:9px 14px;
  font-size:13px;
}
.est-staged-file strong{
  font-weight:600;
  color:var(--ink);
}
.est-staged-file span{
  color:var(--muted);
  font-size:12px;
}
.est-remove-file-btn{
  border:0;
  background:none;
  color:var(--muted);
  width:26px;
  height:26px;
  border-radius:6px;
  display:grid;
  place-items:center;
  padding:0;
  transition:.15s;
}
.est-remove-file-btn:hover{
  background:var(--red-soft);
  color:var(--red);
}

.est-task-actions-row{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:12px;
  margin-top:20px;
  padding-top:16px;
  border-top:1px solid var(--line);
  flex-wrap:wrap;
}

.est-full-detail{
  display:flex;
  flex-direction:column;
  gap:22px;
}
.est-detail-backbar{
  display:flex;
  align-items:center;
  justify-content:space-between;
  background:var(--ink);
  color:#fff;
  padding:14px 20px;
  border-radius:14px;
  flex-wrap:wrap;
  gap:12px;
}
.est-detail-backbar a{
  color:#fff;
  text-decoration:none;
  font-weight:600;
  font-size:13.5px;
  display:inline-flex;
  align-items:center;
  gap:8px;
}
.est-detail-backbar a:hover{
  color:var(--red-on-dark);
}

.est-stats-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(0,1fr));
  gap:16px;
}
.est-stat-box{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px 18px;
  box-shadow:var(--shadow-sm);
}
.est-stat-box small{
  display:block;
  font-size:12px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.04em;
  color:var(--muted);
  margin-bottom:6px;
}
.est-stat-box strong{
  font:700 18px/1.2 var(--font-display);
  color:var(--ink);
}
.est-stat-box.highlight small{
  color:var(--red);
}

.est-progress-box{
  background:#fff;
  border:1px solid var(--line);
  border-radius:16px;
  padding:20px 24px;
  box-shadow:var(--shadow-sm);
}
.est-progress-box h3{
  margin:0 0 16px;
  font:700 15px/1.2 var(--font-display);
  color:var(--ink);
}
.est-stepper{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  position:relative;
  gap:10px;
}
.est-stepper::before{
  content:"";
  position:absolute;
  top:18px;
  left:15%;
  right:15%;
  height:3px;
  background:#EAE6E7;
  z-index:1;
}
.est-step-node{
  display:flex;
  flex-direction:column;
  align-items:center;
  text-align:center;
  position:relative;
  z-index:2;
}
.est-step-circle{
  width:36px;
  height:36px;
  border-radius:50%;
  background:#fff;
  border:2px solid #D4CFD1;
  color:var(--muted);
  display:grid;
  place-items:center;
  font-weight:700;
  font-size:13px;
  margin-bottom:8px;
  transition:all .2s;
}
.est-step-label{
  font-size:12.5px;
  font-weight:600;
  color:var(--muted);
}
.est-step-node.completed .est-step-circle{
  background:var(--green);
  border-color:var(--green-dark);
  color:#fff;
}
.est-step-node.completed .est-step-label{
  color:var(--green-dark);
  font-weight:700;
}
.est-step-node.observed .est-step-circle{
  background:var(--red);
  border-color:var(--red-dark);
  color:#fff;
}
.est-step-node.observed .est-step-label{
  color:var(--red-dark);
  font-weight:700;
}
.est-step-node.current .est-step-circle{
  border-color:var(--gold);
  color:var(--gold-dark);
  background:var(--gold-soft);
}

.est-detail-split{
  display:grid;
  grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);
  gap:20px;
  align-items:start;
}
.est-content-card{
  background:#fff;
  border:1px solid var(--line);
  border-radius:16px;
  padding:22px 24px;
  box-shadow:var(--shadow-sm);
}
.est-content-card h3{
  margin:0 0 14px;
  font:700 17px/1.2 var(--font-display);
  color:var(--ink);
  display:flex;
  align-items:center;
  gap:8px;
}

.est-feedback-card{
  background:linear-gradient(180deg,#FFFFFF 0%,#FBF9FA 100%);
  border:1px solid var(--line);
  border-radius:16px;
  padding:22px 24px;
  box-shadow:var(--shadow-sm);
}
.est-teacher-header{
  display:flex;
  align-items:center;
  gap:12px;
  margin-bottom:14px;
  padding-bottom:12px;
  border-bottom:1px solid var(--line);
}
.est-teacher-avatar{
  width:42px;
  height:42px;
  border-radius:12px;
  background:var(--red-soft);
  color:var(--red-dark);
  font:700 15px/1 var(--font-display);
  display:grid;
  place-items:center;
}
.est-teacher-header strong{
  display:block;
  font:700 15px/1.2 var(--font-display);
  color:var(--ink);
}
.est-teacher-header small{
  color:var(--muted);
  font-size:12px;
}
.est-feedback-text{
  font-size:14px;
  line-height:1.6;
  color:var(--ink-2);
  margin:0;
  padding:14px;
  background:#fff;
  border:1px solid var(--line);
  border-radius:12px;
}

.overlay{
  position:fixed;
  inset:0;
  display:none;
  align-items:center;
  justify-content:center;
  padding:20px;
  background:rgba(25,23,24,.65);
  backdrop-filter:blur(6px);
  z-index:60;
}
.overlay.is-open{display:flex}
.dialog{
  background:#fff;
  border-radius:20px;
  width:min(660px,100%);
  max-width:95vw;
  max-height:90vh;
  overflow-y:auto;
  box-shadow:var(--shadow-lg);
  animation:pop .18s cubic-bezier(.16,1,.3,1);
}
.sidebar-close-btn{display:none;width:38px;height:38px;border-radius:10px;border:1px solid rgba(255,255,255,.18);background:var(--ink-2);color:#fff;place-items:center;flex:none;margin-left:auto}
.sidebar-close-btn:hover{background:var(--red);border-color:var(--red)}
@keyframes pop{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:none}}
.dialog__head{display:flex;align-items:center;gap:12px;padding:20px 24px 14px;border-bottom:1px solid var(--line)}
.dialog__title{margin:0;font:700 18px/1.2 var(--font-display);color:var(--ink)}
.dialog__close{margin-left:auto;border:0;background:none;color:var(--muted);width:32px;height:32px;border-radius:8px;display:grid;place-items:center}
.dialog__close:hover{background:var(--surface);color:var(--ink)}
.dialog__body{padding:20px 24px}
.dialog__foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 20px;border-top:1px solid var(--line);background:var(--surface);border-radius:0 0 20px 20px}

.toasts{position:fixed;top:20px;right:20px;display:flex;flex-direction:column;gap:10px;z-index:90;pointer-events:none}
.toast{pointer-events:auto;display:grid;grid-template-columns:auto 1fr auto;gap:10px;align-items:start;width:min(390px,calc(100vw - 32px));background:#fff;border-radius:12px;padding:13px 14px;border-left:5px solid var(--green);box-shadow:0 12px 32px rgba(25,23,24,.22);animation:slide .2s ease-out}
.toast--error{border-color:var(--red)}
.toast--info{border-color:var(--ink)}
.toast p{margin:0;font-size:13.5px}
.toast__x{border:0;background:none;color:var(--muted);padding:2px 6px}
@keyframes slide{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:none}}

@media (max-width:1080px){
  .est-master-detail{grid-template-columns:1fr}
  .est-side-list{position:static;max-height:none}
  .est-detail-split{grid-template-columns:1fr}
  .est-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (width < 1024px){
  .app{grid-template-columns:1fr}
  .sidebar{position:fixed;left:0;top:0;bottom:0;width:280px;transform:translateX(-102%);transition:transform .22s}
  body.nav-open .sidebar{transform:none}
  body.nav-open .scrim{display:block;position:fixed;inset:0;background:rgba(25,23,24,.55);backdrop-filter:blur(2px);z-index:35}
  .btn.menu-btn{display:inline-flex}
  .sidebar-close-btn{display:inline-grid}
  .main{padding:20px 16px 36px}
}
@media (max-width:640px){
  .est-stats-grid{grid-template-columns:1fr}
  .est-material-card{grid-template-columns:1fr;align-items:flex-start}
  .est-mat-actions{width:100%;flex-wrap:wrap}
}

/* ---------- Barra de Progreso del Curso (Nuevo Prototipo) ---------- */
.est-course-progress{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:12px 18px;
  margin-bottom:22px;
  box-shadow:var(--shadow-sm);
  display:flex;
  flex-direction:column;
  justify-content:center;
  gap:8px;
}
.est-course-progress__info{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  font-size:13px;
  font-weight:600;
  color:var(--ink-2);
  line-height:1.2;
}
.est-course-progress__text{
  display:inline-flex;
  align-items:center;
  gap:6px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.est-course-progress__percent{
  font-weight:700;
  font-size:13px;
  color:var(--ink);
  font-feature-settings:"tnum";
  flex:none;
}
.est-course-progress__track{
  width:100%;
  height:7px;
  background:#EAE6E7;
  border-radius:999px;
  overflow:hidden;
}
.est-course-progress__fill{
  height:100%;
  border-radius:999px;
  background:var(--red);
  transition:width .6s cubic-bezier(0.4, 0, 0.2, 1), background-color .4s ease;
}
.est-course-progress.is-completed .est-course-progress__fill{background:var(--green)}
.est-course-progress.is-completed .est-course-progress__percent{color:var(--green-dark)}

/* ---------- Tarjetas de Lección (Nuevo Prototipo) ---------- */
.est-lesson-card{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px 18px;
  display:flex;
  flex-direction:column;
  gap:14px;
  transition:all .16s;
  box-shadow:0 2px 6px rgba(25,23,24,.02);
}
.est-lesson-card:hover{
  border-color:#CFC8CB;
  box-shadow:var(--shadow-sm);
}
.est-lesson-card__head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding-bottom:12px;
  border-bottom:1px solid var(--line);
  flex-wrap:wrap;
}
.est-lesson-card__num{
  font:700 15px/1.3 var(--font-display);
  color:var(--ink);
  letter-spacing:-.01em;
}
.est-lesson-card__meta{
  display:inline-flex;
  align-items:center;
  gap:8px;
}
.est-lesson-card__content{
  display:flex;
  flex-direction:column;
  gap:10px;
  min-width:0;
}
.est-lesson-field{
  display:flex;
  flex-direction:column;
  gap:2px;
}
.est-lesson-label{
  font-size:11px;
  font-weight:700;
  color:var(--muted);
  text-transform:uppercase;
  letter-spacing:.04em;
}
.est-lesson-title{
  font:700 15px/1.35 var(--font-display);
  color:var(--ink);
  word-break:break-word;
}
.est-lesson-desc{
  margin:0;
  font-size:13.5px;
  line-height:1.5;
  color:var(--ink-2);
  word-break:break-word;
}

/* ---------- Sección Enlace en Tareas (Nuevo Prototipo) ---------- */
.est-link-section{
  margin-top:4px;
}
.est-link-section-title{
  font-size:13.5px;
  font-weight:600;
  color:var(--ink-2);
  margin-bottom:8px;
  display:flex;
  align-items:center;
  gap:6px;
}
.est-link-input-group{
  display:flex;
  gap:8px;
  align-items:center;
  flex-wrap:wrap;
}
.est-link-input{
  flex:1 1 200px;
  height:42px;
  padding:0 14px;
  border:1.5px solid var(--line);
  border-radius:10px;
  background:#fff;
  font-size:14px;
  color:var(--ink);
  transition:border-color .15s;
}
.est-link-input:focus{
  outline:none;
  border-color:var(--red);
  box-shadow:0 0 0 3px rgba(206,33,39,.12);
}

/* ---------- Pantalla de Selección de Cursos (Nuevo Prototipo) ---------- */
.est-courses-section{margin-bottom:34px}
.est-section-label{font:700 15px/1.2 var(--font-display);color:var(--muted);margin:0 0 2px}
.est-group-title{
  font:700 18px/1.2 var(--font-display);
  color:var(--ink);
  margin:20px 0 14px;
  display:flex;
  align-items:center;
  gap:8px;
}
.est-group-count{font-size:13px;font-weight:600;color:var(--muted)}
.est-courses-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
.est-course-card{
  background:#fff;
  border:1px solid var(--line);
  border-top:4px solid var(--red);
  border-radius:16px;
  padding:20px 22px;
  display:flex;
  flex-direction:column;
  gap:12px;
  box-shadow:var(--shadow-sm);
  transition:transform .16s, box-shadow .16s, border-color .16s;
  text-decoration:none;
  color:inherit;
  text-align:left;
  font:inherit;
  cursor:pointer;
  width:100%;
  position:relative;
}
.est-course-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:#CFC8CB}
.est-course-card--concluido{border-top-color:var(--green);background:linear-gradient(180deg,#FFFFFF 0%,#FAF9F9 100%)}
.est-course-card__top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.est-course-card__code{font-size:12.5px;font-weight:700;color:var(--red);letter-spacing:.04em}
.est-course-card--concluido .est-course-card__code{color:var(--green-dark)}
.est-course-card__title{margin:0;font:700 17px/1.25 var(--font-display);color:var(--ink)}
.est-course-card__meta{font-size:12.5px;color:var(--muted);display:flex;flex-direction:column;gap:4px}
.est-course-card__meta-item{display:flex;align-items:center;gap:6px}
.est-course-card__progress{margin-top:auto;padding-top:12px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:6px}
.est-course-card__progress-info{display:flex;justify-content:space-between;align-items:center;font-size:12px;font-weight:600;color:var(--ink-2)}
.est-course-card__progress-track{width:100%;height:6px;border-radius:999px;background:#EAE6E7;overflow:hidden}
.est-course-card__progress-fill{height:100%;border-radius:999px;background:var(--red)}
.est-course-card--concluido .est-course-card__progress-fill{background:var(--green)}
.est-course-card__cta{font-weight:700;font-size:13.5px;color:var(--red);margin-top:2px}
.est-course-card--concluido .est-course-card__cta{color:var(--green-dark)}
.est-empty-courses{padding:18px 20px;border:1px dashed var(--line);border-radius:14px;background:#fff;color:var(--muted);margin-top:12px}

@media (max-width:1100px){.est-courses-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:680px){.est-courses-grid{grid-template-columns:1fr}}
@media (max-width:480px){
  .est-course-progress{padding:10px 14px;margin-bottom:16px}
  .est-course-progress__info{font-size:11.5px}
  .est-detail-hero,.est-detail-body{padding:16px 14px}
  .est-lesson-card{padding:14px}
}
</style>
    <link rel="stylesheet" href="{{ asset('assets/css/panel-dunamys.css') }}">
@stack('styles')
@livewireStyles
</head>
<body>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-book" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></symbol>
  <symbol id="i-pencil" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="i-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
  <symbol id="i-download" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></symbol>
  <symbol id="i-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="i-upload" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></symbol>
  <symbol id="i-right" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></symbol>
  <symbol id="i-left" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
  <symbol id="i-user" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
  <symbol id="i-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></symbol>
  <symbol id="i-video" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></symbol>
  <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></symbol>
  <symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></symbol>
  <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></symbol>
</svg>

{{ $slot }}

<x-toast-dunamys />
<script>document.addEventListener('keydown',function(e){if(e.key==='Escape')document.body.classList.remove('nav-open');});</script>
@stack('scripts')
@livewireScripts
</body>
</html>
