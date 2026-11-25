/**
 * contenido-publico.js — Integración de la página pública (index.html) con el Módulo Docente (MVP 1: RF029, RF032, RF033)
 *
 * INSTRUCCIONES BREVES DE INTEGRACIÓN EN index.html:
 * 1. Copia este archivo `contenido-publico.js` en la misma carpeta que `index.html`.
 * 2. En el objeto `CONFIG` de `index.html`, agrega (o deja que este script lo defina por defecto):
 *      contenidoUrl: "/api/contenido"   // distinto de CONFIG.apiUrl (usado para matrículas y reclamos)
 * 3. Incluye este script al final del `<body>` de `index.html`:
 *      <script src="contenido-publico.js"></script>
 */
(function () {
  'use strict';

  const DEFAULT_CONTENIDO = {
    quienes: 'Dunamys es una academia de música en Juliaca dedicada a formar talentos con metodología práctica, recitales de gala y aula virtual interactiva.',
    telefono: '952233266',
    telefonoSecundario: '',
    diasAtencion: 'Lunes a viernes',
    apertura: '08:00',
    cierre: '18:00',
    avisos: [
      {
        id: 1,
        titulo: 'Próximo inicio de ciclo',
        texto: 'Matrículas abiertas para los niveles Básico, Intermedio y Avanzado.',
        fecha: '05 nov 2026'
      }
    ],
    galeria: []
  };

  window.CONFIG = window.CONFIG || {};
  if (!window.CONFIG.contenidoUrl) {
    window.CONFIG.contenidoUrl = (location.protocol === 'file:')
      ? 'http://localhost:3000/api/contenido'
      : '/api/contenido';
  }

  /**
   * Convierte una hora en formato 24h ("08:00", "18:30") a formato de 12 horas ("8:00 a. m.", "6:30 p. m.")
   */
  function formatHora12(hhmm) {
    const m = String(hhmm || '').trim().match(/^(\d{1,2}):(\d{2})$/);
    if (!m) return String(hhmm || '');
    let h = parseInt(m[1], 10);
    const min = m[2];
    const periodo = h >= 12 ? 'p. m.' : 'a. m.';
    h = h % 12;
    if (h === 0) h = 12;
    return `${h}:${min} ${periodo}`;
  }

  /**
   * Formatea el horario completo: "{días}: {apertura} – {cierre}"
   */
  function formatHorarioAtencion(dias, apertura, cierre) {
    const diasTxt = dias || window.CONFIG.diasAtencion || window.CONFIG.dias || DEFAULT_CONTENIDO.diasAtencion;
    return `${diasTxt}: ${formatHora12(apertura)} – ${formatHora12(cierre)}`;
  }

  /**
   * Formatea un celular de 9 dígitos para lectura visual ("952 233 266")
   */
  function formatTelefonoVisual(tel) {
    const clean = String(tel || '').replace(/[\s-]+/g, '');
    if (/^\d{9}$/.test(clean)) {
      return `${clean.slice(0, 3)} ${clean.slice(3, 6)} ${clean.slice(6)}`;
    }
    return clean;
  }

  /**
   * 3. Actualiza "Quiénes somos" (<p class="mf" id="mf"> en #nosotros) con textContent
   *    y preserva/re-ejecuta la separación por palabras para el efecto de resaltado sin usar innerHTML.
   */
  function renderQuienesSomos(texto) {
    const mf = document.getElementById('mf') || document.querySelector('#nosotros .mf');
    if (!mf) return;

    const hadWordSpans = mf.children.length > 0 && mf.querySelector('span') !== null;
    mf.textContent = '';

    if (typeof window.initManifestoWords === 'function') {
      mf.textContent = texto;
      window.initManifestoWords();
      return;
    }

    if (hadWordSpans) {
      const palabras = String(texto || '').trim().split(/\s+/);
      palabras.forEach((palabra, idx) => {
        const span = document.createElement('span');
        span.className = 'w';
        span.textContent = palabra;
        mf.appendChild(span);
        if (idx < palabras.length - 1) {
          mf.appendChild(document.createTextNode(' '));
        }
      });
    } else {
      mf.textContent = texto;
    }
  }

  /**
   * 4 y 6. Actualiza la sección Contacto (#i-tel, #i-hor) y el botón flotante de WhatsApp (#fab)
   */
  function renderContactoYWhatsApp(datos) {
    const cleanTel1 = String(datos.telefono || '').replace(/[\s-]+/g, '');
    const tel2 = window.CONFIG.telefonoSecundario || window.CONFIG.telefono2 ||
      (Array.isArray(window.CONFIG.telefonos) ? window.CONFIG.telefonos[1] : '') || '';
    const horarioTexto = formatHorarioAtencion(
      window.CONFIG.diasAtencion || window.CONFIG.dias,
      datos.apertura,
      datos.cierre
    );

    const elTel = document.getElementById('i-tel');
    if (elTel) {
      elTel.textContent = '';
      const link1 = document.createElement('a');
      link1.href = `tel:+51${cleanTel1}`;
      link1.textContent = formatTelefonoVisual(cleanTel1);
      link1.style.color = 'inherit';
      link1.style.textDecoration = 'none';
      elTel.appendChild(link1);

      if (tel2) {
        const cleanTel2 = String(tel2).replace(/[\s-]+/g, '');
        elTel.appendChild(document.createTextNode(' / '));
        const link2 = document.createElement('a');
        link2.href = `tel:+51${cleanTel2}`;
        link2.textContent = formatTelefonoVisual(cleanTel2);
        link2.style.color = 'inherit';
        link2.style.textDecoration = 'none';
        elTel.appendChild(link2);
      }
    }

    const elHor = document.getElementById('i-hor');
    if (elHor) {
      elHor.textContent = horarioTexto;
    }

    const fab = document.getElementById('fab');
    if (fab && cleanTel1) {
      try {
        const currentHref = fab.getAttribute('href') || '';
        if (currentHref.includes('wa.me/') || currentHref.includes('whatsapp.com')) {
          const url = new URL(currentHref, location.origin);
          if (url.hostname.includes('wa.me')) {
            url.pathname = `/51${cleanTel1}`;
            fab.setAttribute('href', url.toString());
          } else {
            url.searchParams.set('phone', `51${cleanTel1}`);
            fab.setAttribute('href', url.toString());
          }
        } else {
          fab.setAttribute('href', `https://wa.me/51${cleanTel1}`);
        }
      } catch (e) {
        fab.setAttribute('href', `https://wa.me/51${cleanTel1}`);
      }
    }
  }

  /**
   * 5. Pie de página (footer): agrega o actualiza teléfono (con enlace tel:+51…) y horario de atención.
   */
  function renderFooterContacto(datos) {
    const footer = document.querySelector('footer');
    if (!footer) return;

    const cleanTel = String(datos.telefono || '').replace(/[\s-]+/g, '');
    const horarioTexto = formatHorarioAtencion(
      window.CONFIG.diasAtencion || window.CONFIG.dias,
      datos.apertura,
      datos.cierre
    );

    let box = document.getElementById('footer-contacto-institucional');
    if (!box) {
      box = document.createElement('div');
      box.id = 'footer-contacto-institucional';
      box.style.cssText = 'display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;justify-content:center;padding:12px 0;font-size:14px;';
      footer.appendChild(box);
    }

    box.textContent = '';

    const spanTelWrap = document.createElement('span');
    spanTelWrap.appendChild(document.createTextNode('📞 Teléfono: '));
    const telLink = document.createElement('a');
    telLink.id = 'footer-tel';
    telLink.href = `tel:+51${cleanTel}`;
    telLink.textContent = formatTelefonoVisual(cleanTel);
    telLink.style.cssText = 'color:inherit;font-weight:600;text-decoration:underline;min-height:44px;display:inline-flex;align-items:center;';
    spanTelWrap.appendChild(telLink);

    const spanHor = document.createElement('span');
    spanHor.id = 'footer-hor';
    spanHor.textContent = `🕒 ${horarioTexto}`;

    box.appendChild(spanTelWrap);
    box.appendChild(spanHor);
  }

  /**
   * 7. Avisos: reemplaza la lista "Más avisos" (#av-list y window.AVISOS) usando únicamente textContent.
   */
  function renderAvisos(avisos) {
    const lista = Array.isArray(avisos) ? avisos : [];
    window.AVISOS = lista;

    const avList = document.getElementById('av-list');
    if (!avList) return;

    avList.textContent = '';

    if (!lista.length) {
      const emptyItem = document.createElement(avList.tagName === 'UL' || avList.tagName === 'OL' ? 'li' : 'div');
      emptyItem.className = 'av-item av-empty';
      emptyItem.textContent = 'No hay avisos adicionales por el momento.';
      avList.appendChild(emptyItem);
      return;
    }

    const isListTag = avList.tagName === 'UL' || avList.tagName === 'OL';
    lista.forEach(av => {
      const item = document.createElement(isListTag ? 'li' : 'article');
      item.className = 'av-item';

      const titleEl = document.createElement('strong');
      titleEl.className = 'av-title';
      titleEl.textContent = String(av.titulo || '');

      const textEl = document.createElement('p');
      textEl.className = 'av-text';
      textEl.style.margin = '4px 0 0';
      textEl.textContent = String(av.texto || '');

      item.appendChild(titleEl);
      item.appendChild(textEl);

      if (av.fecha) {
        const dateEl = document.createElement('small');
        dateEl.className = 'av-date';
        dateEl.textContent = String(av.fecha);
        item.appendChild(dateEl);
      }

      avList.appendChild(item);
    });
  }

  /**
   * Mezcla los datos del servidor con los valores por defecto de CONFIG y actualiza el DOM.
   */
  function aplicarContenidoPublico(remoto) {
    const base = {
      quienes: window.CONFIG.quienes || DEFAULT_CONTENIDO.quienes,
      telefono: window.CONFIG.telefono || (Array.isArray(window.CONFIG.telefonos) ? window.CONFIG.telefonos[0] : '') || DEFAULT_CONTENIDO.telefono,
      apertura: window.CONFIG.apertura || DEFAULT_CONTENIDO.apertura,
      cierre: window.CONFIG.cierre || DEFAULT_CONTENIDO.cierre,
      avisos: Array.isArray(window.AVISOS) ? window.AVISOS : (Array.isArray(window.CONFIG.avisos) ? window.CONFIG.avisos : DEFAULT_CONTENIDO.avisos),
      galeria: Array.isArray(window.CONFIG.galeria) ? window.CONFIG.galeria : DEFAULT_CONTENIDO.galeria
    };

    const merged = {
      quienes: (remoto && typeof remoto.quienes === 'string' && remoto.quienes.trim()) ? remoto.quienes.trim() : base.quienes,
      telefono: (remoto && typeof remoto.telefono === 'string' && remoto.telefono.trim()) ? remoto.telefono.trim() : base.telefono,
      apertura: (remoto && typeof remoto.apertura === 'string' && remoto.apertura.trim()) ? remoto.apertura.trim() : base.apertura,
      cierre: (remoto && typeof remoto.cierre === 'string' && remoto.cierre.trim()) ? remoto.cierre.trim() : base.cierre,
      avisos: (remoto && Array.isArray(remoto.avisos)) ? remoto.avisos : base.avisos,
      galeria: (remoto && Array.isArray(remoto.galeria)) ? remoto.galeria : base.galeria
    };

    Object.assign(window.CONFIG, {
      quienes: merged.quienes,
      telefono: merged.telefono,
      apertura: merged.apertura,
      cierre: merged.cierre,
      avisos: merged.avisos,
      galeria: merged.galeria
    });

    renderQuienesSomos(merged.quienes);
    renderContactoYWhatsApp(merged);
    renderFooterContacto(merged);
    renderAvisos(merged.avisos);
  }

  async function cargarContenidoPublico() {
    try {
      const res = await fetch(window.CONFIG.contenidoUrl, { cache: 'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      aplicarContenidoPublico(data);
    } catch (err) {
      // Si el servidor falla o está apagado, funciona con los valores por defecto sin errores visibles
      aplicarContenidoPublico(null);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', cargarContenidoPublico);
  } else {
    cargarContenidoPublico();
  }

  window.DunamysContenidoPublico = {
    recargar: cargarContenidoPublico,
    aplicar: aplicarContenidoPublico,
    formatHora12,
    formatHorarioAtencion
  };
})();
