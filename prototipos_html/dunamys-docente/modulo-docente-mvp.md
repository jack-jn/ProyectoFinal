# Módulo Docente — MVP 1 (Academia de Música Dunamys)

> Documento para que una IA en la terminal (por ejemplo Claude Code) termine el **módulo docente**.
> Léelo completo antes de escribir código. Si usas Claude Code, puedes renombrarlo a `CLAUDE.md` en la raíz del proyecto.

## 0. Archivos de la carpeta

```
carpeta-del-proyecto/
├── modulo_docente11.html   ← AVANCE del módulo docente: ES EL ARCHIVO A EDITAR
└── modulo-docente-mvp.md   ← este documento (requisitos del MVP 1)
```

- Edita **`modulo_docente11.html`**. No crees un archivo nuevo para reemplazarlo, salvo que el usuario lo pida.
- La **página pública** (`index.html`) y su documento (`especificaciones-dunamys.md`) **pueden no estar en esta carpeta**. Si no están, no los inventes: sigue la regla de la sección 9.

## 1. Objetivo

El **docente** entra a un panel y edita los textos institucionales y los datos de contacto de la academia. Lo que guarda debe verse **de inmediato** en la página pública.

Alcance del MVP: **solo** RF029, RF032 y RF033. Todo lo demás del panel (cursos, sesiones, tareas, alumnos) **ya existe y no debe romperse ni modificarse**.

## 2. Diagnóstico del avance (`modulo_docente11.html`)

Ya revisado. Datos clave para no perder tiempo:

**Tecnología y estructura**
- Un solo archivo (~2800 líneas): HTML + CSS + un único `<script>` dentro de una función autoejecutable. Sin frameworks. Español (Perú).
- Barra lateral con 4 vistas: *Cursos*, *Gestión de cursos*, *Alumnos matriculados* y **"Información web institucional"** (`#viewI`, botón `#navI`). Usuario fijo "Docente" (no hay login).
- Tiene botón "Abrir menú" (`data-menu-btn`) para celular y un interruptor **"Simular fallo de conexión"** (`#swSimFail`, variable `simFail`). **Consérvalos**: sirven para demostrar el RF032 criterio 02.

**Vista "Información web institucional"** (la que importa)
- Pestañas (`#instTabs`, `data-itab`): `av` Avisos, **`da` Datos institucionales**, `ga` Galería de eventos. Se dibujan con `renderInstView()` dentro de `#instPane`.
- Panel derecho "Vista previa web pública" (`#liveWebPreview`, `renderLiveWebPreview()`), con etiqueta "En vivo".
- Los datos están en el objeto **`webData`**: `{ avisos[], quienes, telefono, apertura, cierre, galeria[] }`. Hoy es una constante **en memoria**.
- Pestaña "Datos institucionales": `#inpQuienes` (textarea), `#inpTel` (celular), `#inpApe` y `#inpCie` (type="time"), botón `#btnSaveWebDatos` ("Guardar"). Cada campo está en un contenedor con clase `field` (`#fldQuienes`, `#fldTel`, `#fldHorWeb`) y su mensaje `<p class="error">` se muestra con la clase `is-invalid`.
- Los avisos y la galería se publican/eliminan con `#btnPubAv`, `[data-del-av]`, `#btnAddGal`, `[data-del-gal]` y un diálogo de confirmación (`openConfirmDialog`).
- Ya existen helpers: `$`, `$$`, `esc()` (escapa HTML), `toast(mensaje, tipo)`.

**Qué ya cumple (conservar)**
- RF029 criterio 02: si "Quiénes somos" queda vacío muestra "Este campo no puede quedar vacío" y restaura el texto anterior.
- RF033 criterio 02: valida celular con `^\d{9}$` (solo dígitos, exactamente 9, tras quitar espacios) y muestra "Ingresa un número de celular válido".
- RF033 criterio 03: valida que `cierre > apertura` y muestra "La hora de cierre debe ser posterior a la de apertura".
- Los textos se muestran con `esc()`.

**Qué falta o está mal (trabajo a realizar)**

| # | Problema | Requisito |
|---|---|---|
| 1 | **No hay persistencia**: `webData` vive en memoria; al recargar el panel todo vuelve a los valores de ejemplo | RF029-01, RF032-01 |
| 2 | **No está conectado a la página pública**: la "vista previa" es una simulación dentro del panel; el visitante nunca ve los cambios | RF032-01, RF033-01 |
| 3 | El fallo de guardado es solo simulado (`simFail`). Falta manejar **fallos reales** de red/servidor (`try/catch`, `fetch` rechazado, respuesta no OK) | RF032-02 |
| 4 | El mensaje de éxito es `"Cambios guardados correctamente."` (con punto final). Debe ser **exactamente** `Cambios guardados correctamente` | RF029-01 |
| 5 | Si "Quiénes somos" está vacío, la función hace `return` **antes** de validar teléfono y horario: no se muestran todos los errores a la vez | RF029/033 |
| 6 | Los errores de campo solo se limpian al volver a guardar; deben limpiarse al editar el campo | UX |
| 7 | Los errores no se anuncian a lectores de pantalla (falta `role="alert"` o `aria-live`) | accesibilidad |
| 8 | No hay estado de "guardando…" (botón deshabilitado) ni protección contra doble clic | robustez |
| 9 | "Información de contacto" solo tiene celular y horario. La página pública también muestra correo y dirección, hoy fijos | RF029 (opcional, ver sección 6) |
| 10 | No hay inicio de sesión: cualquiera con el HTML podría llamar al servidor | seguridad |

## 3. Decisión técnica que hay que tomar primero

Para que el visitante vea los cambios, los datos deben guardarse en un **lugar compartido**. `localStorage` **no sirve**: solo lo ve el navegador del docente.

- Si el proyecto ya tiene backend, úsalo.
- Si no, propón **la opción más simple** antes de programar y espera la respuesta del usuario. Opciones razonables: (a) Node.js + Express con archivo JSON o SQLite, o (b) un servicio gestionado como Supabase o Firebase.
- Sea cual sea, respeta el **contrato de datos** de la sección 4.

## 4. Contrato de datos

Se **reutilizan los nombres del propio `webData`** del panel para no romper nada. Un único documento `contenido`:

```json
{
  "quienes": "Dunamys es una academia de música en Juliaca…",
  "telefono": "952233266",
  "apertura": "08:00",
  "cierre": "18:00",
  "avisos": [
    { "id": 1, "titulo": "Próximo inicio de ciclo", "texto": "Matrículas abiertas…", "fecha": "05 nov 2026" }
  ],
  "galeria": [ { "id": 1, "titulo": "Recital de Teclado", "tipo": "Foto" } ],
  "actualizado": "2026-11-11T21:30:00-05:00"
}
```

Endpoints (o equivalentes en el servicio elegido):

| Método | Ruta | Quién | Qué hace |
|---|---|---|---|
| `GET` | `/api/contenido` | Público | Devuelve el documento. Responde con `Cache-Control: no-store`. |
| `PUT` | `/api/contenido` | Solo docente autenticado | Valida y guarda. `200` con el documento guardado; `400` con `{ "campo": "...", "mensaje": "..." }` si la validación falla; `401` sin sesión. |

Reglas del servidor:
- **Validar también en el servidor**, con las mismas reglas de la sección 7.
- Guardado **atómico**: o se guarda todo o nada. Si falla, la versión anterior queda intacta.
- Los textos son **texto plano**. Mostrarlos siempre con `textContent` o `esc()`, nunca con `innerHTML` sin escapar.
- Límite sugerido: `quienes` hasta 1500 caracteres.
- Los campos nuevos (`correo`, `direccion`) son **opcionales**; si el usuario los pide, añádelos al contrato y al formulario con el mismo patrón de validación.

## 5. Requisitos del MVP

### RF029 — Editar los textos institucionales

El sistema debe permitir al docente editar los textos institucionales de la página (quiénes somos, información de contacto).

- **Criterio 01.** Dado que el docente está en la pestaña **"Datos institucionales"**, cuando modifica el texto de **"Quiénes somos"** y presiona **"Guardar"**, entonces el sistema actualiza el texto y muestra **"Cambios guardados correctamente"**.
- **Criterio 02.** Dado que el docente está editando el texto de "Quiénes somos", cuando borra todo el contenido y presiona "Guardar", entonces el sistema **no guarda**, muestra **"Este campo no puede quedar vacío"** y **conserva el texto anterior**.

### RF032 — Reflejar los cambios de inmediato en la página pública

El sistema debe reflejar los cambios realizados en el panel de control de inmediato en la página pública.

- **Criterio 01.** Dado que el docente guardó un cambio (texto, aviso, foto o contacto), cuando un visitante **recarga** la página institucional, entonces ve la información actualizada.
- **Criterio 02.** Dado que el docente presiona "Guardar", cuando el guardado **falla por un problema de conexión**, entonces el sistema muestra **"No se pudo guardar, intenta nuevamente"** y la página pública **conserva la versión anterior**.

### RF033 — Editar contacto y horarios de atención

El sistema debe permitir al docente editar la información de contacto y horarios de atención mostrados en la web.

- **Criterio 01.** Dado que el docente está en la pestaña "Datos institucionales", cuando modifica el **número de contacto** o el **horario de atención** y presiona "Guardar", entonces el sistema actualiza la información en la **sección "Contacto"** y en el **pie de página** de la web pública.
- **Criterio 02.** Dado que el docente ingresa un número de contacto **con letras o con menos de 9 dígitos**, cuando presiona "Guardar", entonces el sistema **no guarda** y muestra **"Ingresa un número de celular válido"**.
- **Criterio 03.** Dado que el docente edita el horario de atención, cuando ingresa una **hora de cierre anterior a la de apertura**, entonces el sistema **no guarda** y muestra **"La hora de cierre debe ser posterior a la de apertura"**.

### Alcance de RF032 sobre avisos y fotos

RF032 menciona avisos y fotos como ejemplos de lo que debe reflejarse. En el MVP:
- **Avisos:** ya se crean y eliminan en el panel. Deben **persistir en el servidor** y mostrarse en la página pública (lista "Más avisos"). Esto sí entra.
- **Galería/fotos:** hoy el panel solo guarda un título y un tipo, **sin subir archivos reales**. Subir imágenes reales queda **fuera del MVP** salvo que el usuario lo pida. No rompas esa pestaña.

## 6. Mensajes exactos

Usa **exactamente** estos textos (el avance ya usa casi todos):

| Situación | Mensaje | Tipo |
|---|---|---|
| Guardado correcto | `Cambios guardados correctamente` (**sin punto final**) | éxito |
| "Quiénes somos" vacío o solo espacios | `Este campo no puede quedar vacío` | error en el campo |
| Celular con letras o con menos de 9 dígitos | `Ingresa un número de celular válido` | error en el campo |
| Hora de cierre menor o igual a la de apertura | `La hora de cierre debe ser posterior a la de apertura` | error en el campo |
| Falla de conexión o del servidor al guardar | `No se pudo guardar, intenta nuevamente` | error general (toast) |

## 7. Reglas de validación

- **Todo o nada:** si hay error en cualquier campo, **no se guarda nada**. Valida **todos** los campos y muestra **todos** los errores a la vez (corrige el `return` temprano del punto 5 de la sección 2).
- **Quiénes somos:** `trim()`; si queda vacío, error y **se conserva el texto anterior guardado** (no se sobrescribe en el servidor). Decide con criterio de UX si restauras el contenido del textarea o dejas lo que escribió el docente; lo esencial es que lo guardado no cambie.
- **Celular:** quitar espacios y guiones y comprobar `^\d{9}$`. Ya está casi hecho; solo añade el guion. (Opcional, confirmar con el usuario: exigir que empiece con 9, como los celulares peruanos.)
- **Horario:** `cierre > apertura` y ambos presentes. Si falta alguno, mostrar el mismo error del horario.
- **Fallo real de guardado:** en `catch` de `fetch` o ante respuesta no OK (`!res.ok`), mostrar `No se pudo guardar, intenta nuevamente`, **no** modificar `webData` y **no** mostrar el mensaje de éxito. Lo escrito en el formulario se conserva para reintentar.
- **Interruptor `#swSimFail`:** debe seguir produciendo ese mismo resultado sin llamar al servidor.
- **Guardando:** deshabilitar `#btnSaveWebDatos` mientras dura la petición y mostrar estado de carga.
- **Accesibilidad:** los mensajes de error con `role="alert"` o `aria-live="polite"`; no depender solo del color. Limpiar el error de un campo cuando el docente lo edita.

## 8. Cambios en el panel (`modulo_docente11.html`)

Mínimos y localizados, sin tocar cursos, alumnos ni sesiones:

1. Cargar `webData` desde `GET /api/contenido` al iniciar (con valores por defecto si falla, y un aviso discreto de "no se pudo cargar").
2. Convertir los guardados de la vista institucional (`#btnSaveWebDatos`, publicar/eliminar avisos) en llamadas `PUT`, asíncronas, con el manejo de errores de la sección 7.
3. Ajustar el mensaje de éxito y la validación (puntos 4 a 8 de la sección 2).
4. Mantener `renderLiveWebPreview()` y las etiquetas "En vivo".
5. Protección del `PUT` con una **sesión de docente simple** (por ejemplo, contraseña definida por el equipo, nunca escrita en el HTML). El login completo es otro requisito y queda fuera; pero no dejes el `PUT` abierto al público.

## 9. Página pública (`index.html`)

> **Si `index.html` no está en la carpeta:** no lo inventes ni lo recrees. Entrega esta sección como un fragmento de código listo para pegar (por ejemplo `contenido-publico.js`) con instrucciones breves, e indícalo en tu reporte final.

Para cumplir RF032 y RF033, la página pública debe **leer** el contenido del servidor:

1. Añadir `CONFIG.contenidoUrl` (distinto de `CONFIG.apiUrl`, que ya se usa para matrículas y reclamos).
2. Al cargar, `fetch(CONFIG.contenidoUrl, { cache: "no-store" })` y **mezclar** el resultado con los valores por defecto de `CONFIG`. Si falla, la página **debe seguir funcionando** con los valores por defecto.
3. **Quiénes somos:** hoy está fijo en `<p class="mf" id="mf">` de la sección `#nosotros`, y un script lo separa por palabras para el efecto de resaltado. Asignar el texto del servidor **antes** de ese efecto (o volver a ejecutarlo después).
4. **Sección Contacto (`#contacto`):** rellenar `#i-tel` y `#i-hor`. Formato del horario: `{días}: {apertura} – {cierre}` en 12 horas con "a. m." / "p. m." (por ejemplo `Lunes a viernes: 8:00 a. m. – 6:00 p. m.`). Los días siguen fijos en `CONFIG`.
5. **Pie de página (`footer`):** hoy **no** muestra teléfono ni horario. **Agregarlos**, alimentados con los mismos datos (teléfono con enlace `tel:+51…`).
6. El `telefono` del panel reemplaza al **primer** teléfono de la web (el principal). Actualizar también el enlace del botón flotante de WhatsApp (`#fab`). El segundo teléfono sigue en `CONFIG`.
7. **Avisos:** reemplazar la lista "Más avisos" (`#av-list`, constante `AVISOS`) por `avisos` del servidor (título y texto). La cuenta regresiva grande sigue usando `CONFIG.inicioCiclo` (editarla no es parte del MVP).
8. Mostrar todo con `textContent`, nunca con `innerHTML` sin escapar.

## 10. Fuera del alcance de este MVP

- Subir fotos o videos reales a la galería.
- Editar la fecha de inicio de ciclo, la foto del docente, los cursos públicos y los testimonios.
- Portales de alumno y de secretaría, e inicio de sesión completo.
- Historial de versiones y deshacer.

## 11. Pruebas manuales (todas deben pasar)

| # | Prueba | Resultado esperado |
|---|---|---|
| 1 | Editar "Quiénes somos" y guardar | `Cambios guardados correctamente`; al **recargar el panel** el texto sigue |
| 2 | Borrar todo "Quiénes somos" y guardar | `Este campo no puede quedar vacío`; el texto guardado no cambia ni en el servidor ni en la web pública |
| 3 | Cambiar el celular y guardar | Se actualiza en la sección Contacto **y** en el pie de la web pública |
| 4 | Celular `95223A266` | No guarda; `Ingresa un número de celular válido` |
| 5 | Celular `95223326` (8 dígitos) | No guarda; mismo mensaje |
| 6 | Apertura 18:00 y cierre 08:00 | No guarda; `La hora de cierre debe ser posterior a la de apertura` |
| 7 | Apertura 08:00 y cierre 08:00 | No guarda; mismo mensaje |
| 8 | Dejar "Quiénes somos" vacío **y** celular inválido | Se muestran **ambos** errores a la vez |
| 9 | Guardar con el servidor apagado | `No se pudo guardar, intenta nuevamente`; la web pública conserva la versión anterior; lo escrito sigue en el formulario |
| 10 | Activar "Simular fallo de conexión" y guardar | Mismo mensaje del punto 9; no se llama al servidor |
| 11 | Guardar y recargar la web pública en otra pestaña o dispositivo | Se ve lo nuevo, sin borrar la caché |
| 12 | Publicar un aviso y recargar la web pública | El aviso aparece en "Más avisos" |
| 13 | Apagar el servidor y abrir la web pública | Carga con los datos por defecto, sin errores visibles |
| 14 | Escribir `<script>alert(1)</script>` en "Quiénes somos" y en un aviso | Se muestra como texto, no se ejecuta, ni en el panel ni en la web pública |
| 15 | Panel y web pública a 320, 390, 768 y 1440 px | Sin scroll horizontal; botones de al menos 44 px |
| 16 | Usar Cursos, Gestión de cursos y Alumnos | Siguen funcionando igual que antes |

## 12. Forma de trabajar (para la IA)

1. Lee `modulo_docente11.html` (sobre todo `#viewI`, `renderInstView`, `renderLiveWebPreview`, el manejador de `#instPane` y `webData`) y este documento. Si existen en la carpeta, lee también `index.html` y `especificaciones-dunamys.md`.
2. Confirma el diagnóstico de la sección 2 (corrige lo que no coincida) y propón la tecnología de guardado (sección 3). **Espera la confirmación del usuario** si no hay backend definido.
3. Implementa en este orden: contrato de datos y endpoints → validaciones y mensajes → conexión del panel (cargar y guardar) → lectura en la web pública (o `contenido-publico.js`) → pie de página.
4. Haz cambios **pequeños y localizados**. No reformatees ni reescribas el archivo.
5. Ejecuta las pruebas de la sección 11 y reporta cuáles pasaron y cuáles no.
6. No inventes datos de la academia.
