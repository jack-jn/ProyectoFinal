# Especificación — Módulo Estudiante y Recuperación de contraseña (Academia de Música Dunamys)

## 1. Contexto y alcance

Proyecto grupal de 4 integrantes: sitio web de la academia de música **Dunamys** (cursos de Teclado, Guitarra, etc.). Este documento cubre **solo** la parte del módulo del estudiante:

- **RF034–RF042**: inicio de sesión del alumno y aula virtual (sesiones, materiales, tareas, estados).
- **RF049–RF053**: inicio de sesión de cualquier usuario, redirección por rol y recuperación de contraseña.

Otros integrantes hacen: Docente/Gestión web institucional, Docente/Gestión de cursos, Interesado/pago por tarjeta, Secretaria/validación de Yape, Interesado/pago por Yape, Interesado/matrícula y Visitante. **No se tocan ni se implementan aquí.**

Es un **prototipo front-end** (HTML + CSS + JS puro, sin backend). Los datos son de ejemplo y quedan listos para conectarse luego a un backend (Laravel + MySQL).

## 2. Decisiones que difieren de los requerimientos (aplicar tal cual)

| Tema | Requerimiento original | Decisión |
|---|---|---|
| Recuperación (RF052) | Contraseña temporal aleatoria por correo | **Código aleatorio de 6 caracteres enviado por SMS** al celular registrado; se ingresa en la misma pantalla donde se solicita (más seguro). |
| Videollamadas (RF042) | Enlace de videollamada semanal | **Excluido** de esta versión. No crear menú, pantalla ni datos de videollamadas. |
| Entrega de tarea (RF039) | Solo video de práctica | Acepta **video y también otros archivos** (PDF, audio, imagen, documentos, etc.). |
| Login (RF034/RF049) | Correo y contraseña | **Se mantiene correo + contraseña** (no usar "nombre de usuario"). |
| Materiales (RF037) | Video, PDF, partitura o enlace | Materiales como **archivos descargables** (Video HD, Guía PDF, Partitura), igual que el módulo docente. Sin enlaces externos como material. |
| Estados de tarea (RF041) | Pendiente / Aprobada / Observada | Se mantienen esos 3 estados, y se muestra además una **línea de progreso** Entregado → Revisado → Aprobado/Observado (derivada de ellos). |

**Campos propuestos que el módulo docente aún no tiene** (usarlos en el estudiante y marcar como pendiente de acordar): fecha de publicación de la tarea, fecha límite (plazo), fecha de la sesión, nota/calificación.

## 3. Carpeta y archivos

Ruta de trabajo: `D:\LP1\Modulo_estudiante y recuperación_contraseña` (tiene espacios y "ó": usar comillas en la terminal).

| Archivo | Estado | Qué hacer |
|---|---|---|
| `modulo_docente11.html` | Eliminado | Fue solo referencia inicial de diseño y ya no está en la carpeta. La ruta del docente se conectará al integrar módulos. |
| `index_estudiante.html` | Implementado | Portal del estudiante con Sesiones y Tareas. |
| `login.html` | Implementado | Pantalla de inicio de sesión (RF034, RF049, RF050). |
| `recuperar_contrasena.html` | Implementado | Recuperación de contraseña por código (RF051–RF053 modificados). |
| `referencias/` (opcional) | Existe | Capturas del Figma de apoyo visual. |

Cada HTML es **autocontenido** (CSS y JS embebidos). Sin frameworks ni librerías; solo Google Fonts.

## 4. Flujo general

```
login.html ──correo+contraseña correctos──► según rol (RF050)
   │                                         ├─ Alumno     → index_estudiante.html
   │                                         ├─ Docente    → aviso "portal no disponible" (se conectará al integrar módulos)
   │                                         └─ Secretaria → aviso "portal no disponible"
   └─ "Olvidé mi contraseña" ─► recuperar_contrasena.html ─► (éxito) ─► login.html
```

## 5. Pantallas

### 5.1 `login.html` (RF034, RF049, RF050)
- Diseño dividido (inspirado en el Figma): **izquierda** panel visual rojo/oscuro con el logo y la frase "La música transforma vidas" (imagen opcional `assets/login-hero.jpg`; si no existe, usar degradado, sin romper la página); **derecha** panel oscuro con el formulario.
- Campos: **Correo** y **Contraseña** (botón mostrar/ocultar). Botón rojo **Ingresar**. Enlace **"Olvidé mi contraseña"** a `recuperar_contrasena.html`.
- Validaciones: campo vacío → "Este campo es obligatorio"; correo con formato inválido → mensaje de formato.
- Credenciales incorrectas → no ingresa y muestra "Correo o contraseña incorrectos." (sin indicar cuál falló). (RF034 CA02)
- Protección ligera (RF049 "sin detectar amenazas"): tras 5 intentos fallidos seguidos, bloquear el formulario 30 s con aviso.
- Éxito → configurar rutas por rol (`RUTAS_PORTAL`). Alumno guarda sesión en `sessionStorage` (clave `dunamys_sesion`: `{correo, nombre, rol}`) y entra a `index_estudiante.html`. Para Docente y Secretaria, al no contar aún con su portal en esta carpeta, no se inicia sesión y se muestra el aviso de portal no disponible ("El portal de [rol] aún no está disponible en esta versión."); la ruta del portal del docente se conectará al integrar los módulos. (RF050).
- Usuarios de ejemplo (solo demo, contraseñas en el JS no son seguras):

| Correo | Contraseña | Rol | Nombre | Celular |
|---|---|---|---|---|
| `lucia.mendoza@dunamys.pe` | `Alumno2026` | Alumno | Lucía Mendoza | 987 112 233 |
| `docente@dunamys.pe` | `Docente2026` | Docente | Docente | 987 000 111 |
| `secretaria@dunamys.pe` | `Secretaria2026` | Secretaria | Secretaria | 987 000 222 |

### 5.2 `recuperar_contrasena.html` (RF051–RF053 modificados)
Todo en **una sola pantalla** (sin asistente de varias páginas):
1. Campo **Correo** + botón **Enviar código**. Correo no registrado → error "Este correo no está registrado en el sistema." (RF053).
2. Si existe: generar código de **6 caracteres aleatorios** (alfabeto `ABCDEFGHJKMNPQRSTUVWXYZ23456789`, sin caracteres ambiguos, usando `crypto.getRandomValues`). Aparece **en la misma pantalla** el bloque para ingresar el código (6 casillas con avance automático y pegado), con texto "Enviamos un código al celular ••• ••• 233".
3. Como no hay SMS real: mostrar un aviso visible **"Modo demostración: el código es XXXXXX"** (fácil de quitar luego).
4. Reglas del código: vence a los **10 minutos**; máximo **5 intentos**; **reenviar** habilitado tras 60 s (genera código nuevo e invalida el anterior); un solo uso.
5. Con código correcto aparecen **Nueva contraseña** y **Repetir contraseña** (mínimo 8 caracteres, con letra y número; deben coincidir). Al guardar → mensaje de éxito y redirección a `login.html` (la nueva contraseña queda válida para esa sesión de demo, p. ej. en `localStorage` bajo `dunamys_claves`).
6. Enlace "Volver a iniciar sesión".

### 5.3 `index_estudiante.html` — Portal del estudiante (RF035–RF041)
**Guardia de acceso:** al cargar, si no hay `dunamys_sesion` o el rol no es `Alumno`, redirigir a `login.html`.

**Layout** (copiar el del docente): barra lateral de 272 px con el logo, "USUARIO: Alumno" + nombre, menú **Sesiones** y **Tareas**, pie "La música transforma vidas" y botón **Cerrar sesión** (borra la sesión y vuelve a `login.html`). En la cabecera: título con una palabra en rojo, subtítulo, chip del curso ("C-001 · Teclado Básico") y avatar con la inicial.

**Navegación** por hash: `#/sesiones`, `#/sesion/ID`, `#/tareas`, `#/tarea/ID`, `#/tarea/ID/detalle`. Responsive: en pantallas pequeñas la barra lateral pasa a menú deslizable (como el docente).

#### Sesiones (RF035, RF036, RF037, RF038)
- Vista maestro-detalle: **lista izquierda** de sesiones en orden ascendente (Sesión 1, 2, 3…) con título y estado (**Completada**, **En proceso**, **Bloqueada** con candado); **detalle derecho** de la sesión elegida.
- Solo se listan las sesiones del **curso en que el alumno está matriculado** (RF035 CA01).
- Si se intenta abrir (por hash) una sesión de **otro curso** → panel "No tienes autorización para acceder a esta sesión." (RF035 CA02). Los datos de ejemplo deben incluir otro curso para poder probarlo.
- Sesión **bloqueada** (la anterior sin entregar su tarea) → no muestra contenido; mensaje "Debes completar la sesión anterior para acceder a esta." (RF038).
- Detalle: título, **fecha**, **descripción**, y **Materiales de la sesión** (lecciones): icono por tipo (Video HD / Guía PDF / Partitura), título, descripción, nombre y tamaño del archivo, duración si es video. Botones **Ver** (modal de vista previa simulada) y **Descargar** (descarga real de un archivo de ejemplo generado con `Blob`, para que funcione sin servidor). (RF037)
- Botón **Ir a la tarea** de esa sesión.

#### Tareas (RF039, RF040, RF041)
- Vista maestro-detalle: lista "Tarea Sesión N" con icono/estado: **Por entregar**, **Pendiente**, **Aprobada**, **Observada**, **Bloqueada**.
- Detalle de la tarea: título, descripción/instrucciones, **fecha de publicación**, **fecha límite** (con aviso "vence en N días" o "Plazo vencido"), archivos del docente (descargables) y enlace de apoyo si existe.
- **Entregar** (si no hay entrega o está Observada):
  - Zona de arrastrar/elegir archivos ("Elige archivos o suéltalos aquí"), botón **Adjuntar archivo**; se pueden adjuntar varios.
  - Lista de archivos elegidos con ✕ para quitar cada uno y botón **Cancelar** que descarta todo lo adjuntado **antes de enviar**.
  - Botón **Entregar tarea**. Sin archivo → error "Adjunta al menos un archivo para entregar la tarea." y no envía (RF039 CA02).
  - Validación de formato/tamaño con el mensaje del docente: "Formato o tamaño de archivo no permitido". Extensiones permitidas (constante editable): `mp4, webm, mov, mp3, wav, pdf, png, jpg, jpeg, doc, docx, ppt, pptx, zip, musicxml, mxl`; máximo 200 MB por archivo.
  - Al enviar: registrar la entrega, estado **Pendiente**, toast de confirmación (RF039 CA01), marcar la sesión **Completada** (RF040) y **desbloquear la siguiente** sesión/tarea.
  - Plazo vencido: permitir enviar pero marcar "Entregada con retraso" (decisión por confirmar con el grupo).
  - Si el estado es **Observada**: botón **Volver a entregar** (reemplaza la entrega y vuelve a Pendiente) (propuesta).
- Con entrega enviada: botón **Tarea entregada ✓** (deshabilitado) y **Ver detalles**.

#### Detalle de la entrega (`#/tarea/ID/detalle`)
Pantalla con botón **Volver a tareas** y título "Tarea Sesión N". Contiene:
- Tarjetas: **Fecha de entrega** (plazo), **Entregado el**, **Calificación** ("18/20" o "Sin calificar").
- **Estado de la entrega** como línea de progreso:

| Estado guardado | Entregado | Revisado | Aprobado / Observado |
|---|---|---|---|
| Pendiente | ✔ | ○ | ○ |
| Aprobada | ✔ | ✔ | ✔ Aprobado |
| Observada | ✔ | ✔ | ⚠ Observado |

- **Instrucciones** y archivos del docente (descargables).
- **Mi entrega**: archivos enviados (nombre y tamaño; descargables).
- Panel **Retroalimentación del docente**: comentario y fecha de revisión; si Pendiente → "Tu tarea aún no ha sido revisada." (RF041 CA01–CA03).

## 6. Estilos (tomar de `modulo_docente11.html`)

Leer el `<style>` del docente y **reutilizar tal cual** variables y componentes, copiando solo lo necesario:
- Fuentes: **Figtree** (texto) y **Sora** (títulos), por Google Fonts.
- Variables `:root`: `--red:#CE2127`, `--red-dark`, `--red-soft`, `--ink:#191718`, `--bg:#F4F2F3`, `--line`, `--green/gold/blue` (y sus `-soft`, `-border`, `-dark`), sombras.
- Componentes: `.app`, `.sidebar`, `.brand` (con su SVG de nota musical), `.user`, `.avatar`, `.nav-item` (+ `.is-active`, `.chev`), `.page-head`, `.btn` (`--primary`, `--outline`, `--ghost`, `--soft-red`, `--sm`), `.control`, `.card`, `.pill` (`green`, `gold`, `red`), `.dialog`, `.toasts`, `:focus-visible`, `[hidden]`, sprite de íconos SVG (`#i-book`, `#i-check`, `#i-alert`, …).
- **Ajuste pedido por el usuario:** letras menos grandes que el docente: títulos de página ≈ **26 px** (el docente usa 31 px), texto base 14–15 px, títulos de tarjeta ≈ 17–18 px.
- Estados: Aprobada → `pill green`; Observada → `pill red`; Pendiente → `pill gold`; Por entregar → píldora neutra; Bloqueada → candado + opacidad reducida.
- Inspiración del Figma: login partido con panel oscuro a la derecha; lista de sesiones/tareas a la izquierda y detalle a la derecha; iconos ✔ / ⚠ / reloj / candado en la lista de tareas; modal/zona de subida con "Elija desde archivos o suelte aquí" y botones **Cancelar** (rojo) / **Entregar tarea** (verde); pantalla "Ver detalles" con tarjetas de fecha y calificación, línea de estado y retroalimentación a la derecha.

## 7. Datos de ejemplo (modelo para el JS)

Basarse en `courses[0]` de `modulo_docente11.html` (curso **C-001 Teclado Básico**, alumna **Lucía Mendoza**) para sesiones, lecciones y tareas; completar con sesiones extra de demostración hasta tener **6 sesiones** y cubrir todos los estados:

| Sesión | Estado esperado en la demo |
|---|---|
| 1 | Completada · tarea **Aprobada** (con nota y comentario) |
| 2 | Completada · tarea **Aprobada** |
| 3 | Completada · tarea **Observada** (con comentario del docente) |
| 4 | Completada · tarea **Pendiente** (entregada, sin revisar) |
| 5 | **En proceso** · tarea por entregar (aquí se prueba subir y cancelar) |
| 6 | **Bloqueada** |

Incluir además un segundo curso (p. ej. **C-004 Guitarra Básico**) en el que Lucía **no** está matriculada, solo para probar RF035 CA02.

Estructura sugerida (campos nuevos marcados con `*`):

```js
const MATRICULAS = [{ correo: 'lucia.mendoza@dunamys.pe', cursoId: 1 }];
const COURSES = [{
  id: 1, codigo: 'C-001', nombre: 'Teclado Básico',
  sesiones: [{
    id: 101, numero: 1, titulo: '…', fecha: '2026-10-05' /* * */, descripcion: '…',
    lecciones: [{ id, tipo: 'Video HD|Guía PDF|Partitura', titulo, desc, ref: 'archivo.mp4 · 18.4 MB', duracion: '02:10' }],
    tarea: { titulo, desc, enlace, archivo: 'demo.mp4 · 16.5 MB',
             publicada: '2026-10-05' /* * */, limite: '2026-10-12' /* * */ }
  }]
}];
// Entrega del alumno (se guarda en localStorage: dunamys_entregas_<correo>)
{ sesionId, fecha, archivos: [{ nombre, tamano, tipo }], estado: 'Pendiente|Aprobada|Observada',
  nota: 18 /* * */, comentario: '…', revisadaEl: '…', conRetraso: false }
```

Estado de sesión (calculado): **Completada** si tiene entrega; **En proceso** si no tiene entrega y la sesión anterior está completada (la 1 siempre está disponible); **Bloqueada** en otro caso. No guardar el contenido de los archivos, solo sus metadatos.

Equivalencias previstas con la base de datos (para conectar luego): `SESION`, `LECCION/MATERIAL`, `TAREA` (fecha de publicación, fecha límite), `ENTREGA_TAREA` (estado, nota, comentario) y archivos de la entrega.

## 8. Reglas de código (para unir con los otros módulos)

- JS dentro de una función autoejecutable (IIFE): **sin variables globales**. Funciones y constantes en español o inglés de forma consistente, con comentarios cortos que indiquen el RF.
- Reutilizar los **mismos nombres de clases** del docente para componentes compartidos; las clases y `id` propios del estudiante llevan prefijo `est-` (p. ej. `est-sesion-card`, `#estViewSesiones`).
- Claves de almacenamiento con prefijo `dunamys_`.
- Escapar siempre el texto dinámico (usar la misma función `esc` del docente) antes de insertarlo en HTML.
- Accesibilidad: etiquetas `<label>`, `aria-live` en avisos, modales con foco atrapado y cierre con Esc, foco visible, navegación por teclado, contraste suficiente. Respetar `prefers-reduced-motion`.
- Todos los textos de la interfaz en **español**.

## 9. Checklist de aceptación (probar a mano en el navegador)

**Login**
- [ ] Correo y contraseña correctos de Lucía → entra a `index_estudiante.html` (RF034 CA01, RF050 CA01).
- [ ] Credenciales incorrectas → no entra y muestra el mensaje (RF034 CA02).
- [ ] `docente@dunamys.pe` → va a `modulo_docente11.html`; `secretaria@dunamys.pe` → aviso de portal no disponible (RF050).
- [ ] Campos vacíos y correo inválido muestran sus mensajes; 5 fallos seguidos bloquean 30 s.
- [ ] Abrir `index_estudiante.html` sin sesión → redirige a `login.html`.

**Recuperación**
- [ ] Correo no registrado → error (RF053).
- [ ] Correo válido → aparece el campo del código en la misma pantalla y el aviso de demostración con el código de 6 caracteres.
- [ ] Código incorrecto resta intentos; a los 5 se bloquea; el código vence a los 10 min; reenviar invalida el anterior.
- [ ] Código correcto → permite crear nueva contraseña y luego iniciar sesión con ella.

**Estudiante**
- [ ] Solo se ven las sesiones de C-001, en orden 1 a 6; abrir una sesión de C-004 por hash muestra "No tienes autorización…".
- [ ] La sesión 6 está bloqueada y muestra el mensaje; al entregar la tarea 5 se desbloquea.
- [ ] Cada material se puede ver y descargar.
- [ ] Entregar sin archivo → error; con archivo permitido → toast, estado Pendiente, sesión Completada; con archivo no permitido → mensaje de formato.
- [ ] Adjuntar y luego **Cancelar** descarta los archivos sin enviar.
- [ ] **Ver detalles** muestra fechas, nota, estado con línea de progreso, archivos del docente, mi entrega y comentarios; el estado coincide con la tabla de la sección 5.3.
- [ ] No existe ninguna opción de videollamadas.
- [ ] Se ve bien en escritorio y en móvil, sin errores en la consola.

## 10. Pendientes por acordar con el grupo

1. Que el módulo docente agregue **fecha de publicación, fecha límite y nota** a la tarea/entrega.
2. Renombrar en el docente el campo de entrega `video` a `archivo` y acordar la lista de extensiones permitidas.
3. Comportamiento cuando la tarea llega **después del plazo** (aquí: se permite con marca de retraso).
4. Si una entrega **Observada** puede volver a enviarse (aquí: sí).
5. Quién será dueño de las entidades compartidas (`PERSONA`, `USUARIO_SISTEMA`, `ALUMNO`, `CURSO`, `MATRICULA`) y cómo se une el menú lateral entre roles.
6. Dejar documentado en el Miro: recuperación por código SMS (cambia RF052), exclusión de RF042 y entrega con cualquier archivo (cambia RF039).
