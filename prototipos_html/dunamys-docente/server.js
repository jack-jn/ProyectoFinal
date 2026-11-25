const http = require('http');
const fs = require('fs');
const path = require('path');

const crypto = require('crypto');

const PORT = Number(process.env.PORT) || 3000;
const DOCENTE_KEY = process.env.DOCENTE_KEY || 'dunamys2026';
const SESSION_TOKEN = crypto.randomBytes(24).toString('hex');
const DATA_PATH = process.env.CONTENIDO_PATH || path.join(__dirname, 'contenido.json');

const DEFAULT_CONTENT = {
  quienes: 'Dunamys es una academia de música en Juliaca dedicada a formar talentos con metodología práctica, recitales de gala y aula virtual interactiva.',
  telefono: '952233266',
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
  galeria: [
    { id: 1, titulo: 'Recital de Teclado', tipo: 'Foto' },
    { id: 2, titulo: 'Ensayo de Gala', tipo: 'Video' }
  ],
  actualizado: '2026-11-11T21:30:00-05:00'
};

function readContent() {
  try {
    if (!fs.existsSync(DATA_PATH)) {
      fs.writeFileSync(DATA_PATH, JSON.stringify(DEFAULT_CONTENT, null, 2), 'utf8');
      return { ...DEFAULT_CONTENT };
    }
    const raw = fs.readFileSync(DATA_PATH, 'utf8');
    return JSON.parse(raw);
  } catch (err) {
    return { ...DEFAULT_CONTENT };
  }
}

function formatPeruISO(date = new Date()) {
  const peruMs = date.getTime() - (5 * 60 * 60 * 1000);
  const d = new Date(peruMs);
  const pad = n => String(n).padStart(2, '0');
  return `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())}T${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}:${pad(d.getUTCSeconds())}-05:00`;
}

function validatePayload(body) {
  const errores = [];
  if (!body || typeof body !== 'object') {
    return { ok: false, errores: [{ campo: 'general', mensaje: 'Datos inválidos' }] };
  }

  const quienes = typeof body.quienes === 'string' ? body.quienes.trim() : '';
  if (!quienes) {
    errores.push({ campo: 'quienes', mensaje: 'Este campo no puede quedar vacío' });
  } else if (quienes.length > 1500) {
    errores.push({ campo: 'quienes', mensaje: 'Este campo no puede superar los 1500 caracteres' });
  }

  const rawTel = typeof body.telefono === 'string' ? body.telefono.trim() : '';
  const cleanTel = rawTel.replace(/[\s-]+/g, '');
  if (!/^\d{9}$/.test(cleanTel)) {
    errores.push({ campo: 'telefono', mensaje: 'Ingresa un número de celular válido' });
  }

  const apertura = typeof body.apertura === 'string' ? body.apertura.trim() : '';
  const cierre = typeof body.cierre === 'string' ? body.cierre.trim() : '';
  const timeRe = /^([01]\d|2[0-3]):[0-5]\d$/;
  if (!apertura || !cierre || !timeRe.test(apertura) || !timeRe.test(cierre) || cierre <= apertura) {
    errores.push({ campo: 'horario', mensaje: 'La hora de cierre debe ser posterior a la de apertura' });
  }

  if (!Array.isArray(body.avisos)) {
    errores.push({ campo: 'avisos', mensaje: 'El aviso no puede estar vacío' });
  } else {
    for (const av of body.avisos) {
      const ti = av && typeof av.titulo === 'string' ? av.titulo.trim() : '';
      const tx = av && typeof av.texto === 'string' ? av.texto.trim() : '';
      if (!ti || !tx) {
        errores.push({ campo: 'avisos', mensaje: 'El aviso no puede estar vacío' });
        break;
      }
    }
  }

  if (!Array.isArray(body.galeria)) {
    errores.push({ campo: 'galeria', mensaje: 'Formato de galería inválido' });
  } else {
    for (const g of body.galeria) {
      const ti = g && typeof g.titulo === 'string' ? g.titulo.trim() : '';
      if (!ti) {
        errores.push({ campo: 'galeria', mensaje: 'El título de galería no puede estar vacío' });
        break;
      }
    }
  }

  if (errores.length > 0) {
    return { ok: false, errores };
  }

  const sanitized = {
    quienes,
    telefono: cleanTel,
    apertura,
    cierre,
    avisos: body.avisos.map((a, idx) => ({
      id: Number(a.id) || (Date.now() + idx),
      titulo: String(a.titulo).trim(),
      texto: String(a.texto).trim(),
      fecha: String(a.fecha || 'Hoy').trim()
    })),
    galeria: body.galeria.map((g, idx) => ({
      id: Number(g.id) || (Date.now() + idx),
      titulo: String(g.titulo).trim(),
      tipo: g.tipo === 'Video' ? 'Video' : 'Foto'
    })),
    actualizado: formatPeruISO()
  };

  return { ok: true, data: sanitized };
}

function saveContentAtomic(data) {
  const tmpPath = DATA_PATH + '.tmp.' + process.pid + '.' + Date.now();
  fs.writeFileSync(tmpPath, JSON.stringify(data, null, 2), 'utf8');
  fs.renameSync(tmpPath, DATA_PATH);
}

const MIME_TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.ico': 'image/x-icon'
};

function parseCookies(cookieHeader = '') {
  return Object.fromEntries(
    cookieHeader.split(';').map(c => c.trim()).filter(Boolean).map(c => {
      const idx = c.indexOf('=');
      return idx === -1 ? [c, ''] : [c.slice(0, idx), decodeURIComponent(c.slice(idx + 1))];
    })
  );
}

function isAuthenticated(req) {
  const headerKey = req.headers['x-docente-key'] ||
    (req.headers['authorization'] ? req.headers['authorization'].replace(/^Bearer\s+/i, '') : '');
  if (headerKey && headerKey === DOCENTE_KEY) return true;
  const cookies = parseCookies(req.headers.cookie);
  if (cookies.dunamys_docente_sid && cookies.dunamys_docente_sid === SESSION_TOKEN) return true;
  return false;
}

const server = http.createServer((req, res) => {
  const parsedUrl = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
  const pathname = parsedUrl.pathname;

  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, PUT, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-Docente-Key, Authorization');

  if (req.method === 'OPTIONS') {
    res.writeHead(204);
    res.end();
    return;
  }

  if (pathname === '/api/contenido' && req.method === 'GET') {
    const content = readContent();
    res.writeHead(200, {
      'Content-Type': 'application/json; charset=utf-8',
      'Cache-Control': 'no-store'
    });
    res.end(JSON.stringify(content));
    return;
  }

  if (pathname === '/api/contenido' && req.method === 'PUT') {
    if (!isAuthenticated(req)) {
      res.writeHead(401, {
        'Content-Type': 'application/json; charset=utf-8',
        'Cache-Control': 'no-store'
      });
      res.end(JSON.stringify({
        campo: 'sesion',
        mensaje: 'Sesión de docente no autorizada. Ingresa la clave de docente.'
      }));
      return;
    }

    let rawBody = '';
    req.on('data', chunk => {
      rawBody += chunk;
      if (rawBody.length > 1e6) req.destroy();
    });
    req.on('end', () => {
      let parsed;
      try {
        parsed = JSON.parse(rawBody);
      } catch (e) {
        res.writeHead(400, {
          'Content-Type': 'application/json; charset=utf-8',
          'Cache-Control': 'no-store'
        });
        res.end(JSON.stringify({ campo: 'general', mensaje: 'JSON inválido' }));
        return;
      }

      const result = validatePayload(parsed);
      if (!result.ok) {
        const first = result.errores[0];
        res.writeHead(400, {
          'Content-Type': 'application/json; charset=utf-8',
          'Cache-Control': 'no-store'
        });
        res.end(JSON.stringify({
          campo: first.campo,
          mensaje: first.mensaje,
          errores: result.errores
        }));
        return;
      }

      try {
        saveContentAtomic(result.data);
        res.writeHead(200, {
          'Content-Type': 'application/json; charset=utf-8',
          'Cache-Control': 'no-store'
        });
        res.end(JSON.stringify(result.data));
      } catch (err) {
        res.writeHead(500, {
          'Content-Type': 'application/json; charset=utf-8',
          'Cache-Control': 'no-store'
        });
        res.end(JSON.stringify({
          campo: 'servidor',
          mensaje: 'No se pudo guardar, intenta nuevamente'
        }));
      }
    });
    return;
  }

  // Archivos estáticos del proyecto
  if (req.method === 'GET' || req.method === 'HEAD') {
    let relPath = pathname === '/' ? '/modulo_docente11.html' : pathname;
    const safePath = path.normalize(decodeURIComponent(relPath)).replace(/^(\.\.[\/\\])+/, '');
    const filePath = path.join(__dirname, safePath);

    if (!filePath.startsWith(__dirname) || path.basename(filePath) === 'server.js') {
      res.writeHead(403);
      res.end('Acceso denegado');
      return;
    }

    fs.stat(filePath, (err, stats) => {
      if (err || !stats.isFile()) {
        res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
        res.end('Archivo no encontrado');
        return;
      }
      const ext = path.extname(filePath).toLowerCase();
      const headers = {
        'Content-Type': MIME_TYPES[ext] || 'application/octet-stream',
        'Cache-Control': 'no-store'
      };
      if (path.basename(filePath) === 'modulo_docente11.html') {
        headers['Set-Cookie'] = `dunamys_docente_sid=${SESSION_TOKEN}; HttpOnly; Path=/; SameSite=Strict`;
      }
      res.writeHead(200, headers);
      if (req.method === 'HEAD') {
        res.end();
        return;
      }
      fs.createReadStream(filePath).pipe(res);
    });
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json; charset=utf-8' });
  res.end(JSON.stringify({ mensaje: 'Ruta no encontrada' }));
});

if (require.main === module) {
  server.listen(PORT, () => {
    console.log(`Servidor Dunamys activo en http://localhost:${PORT}`);
    console.log(`Panel docente: http://localhost:${PORT}/modulo_docente11.html`);
    console.log(`Endpoint API:  http://localhost:${PORT}/api/contenido`);
  });
}

module.exports = { server, validatePayload, readContent, saveContentAtomic };
