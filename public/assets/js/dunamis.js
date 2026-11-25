
/* =====================================================
   DUNAMYS - SISTEMA DE GESTIÓN DE PAGOS
   JAVASCRIPT COMPLETO 2026
===================================================== */

'use strict';


/* =====================================================
   1. VARIABLES Y FUNCIONES GENERALES
===================================================== */

const $ = (selector, parent = document) =>
    parent.querySelector(selector);

const $$ = (selector, parent = document) =>
    [...parent.querySelectorAll(selector)];


let records = [];

let selectedVoucher = null;

let currentDetailId = null;
let currentRejectId = null;
let currentAccessId = null;
let lastSubmittedId = null;


function escapeHTML(value) {

    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);

}


function money(amount) {

    return 'S/ ' + Number(amount || 0).toFixed(2);

}


function dateOffset(days = 0) {

    const date = new Date();

    date.setDate(date.getDate() - days);

    return [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0')
    ].join('-');

}


function formatDate(date) {

    if (!date) return '—';

    const parsed = new Date(date + 'T12:00:00');

    if (Number.isNaN(parsed.getTime())) return '—';

    return parsed.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });

}


function getRecord(id) {

    return records.find(record => record.id === id);

}


function toast(message, type = 'success') {

    const container = $('#toasts');

    if (!container) return;

    const element = document.createElement('div');

    element.className = 'toast';

    if (type === 'error') {
        element.classList.add('bad');
    }

    element.textContent = message;

    container.appendChild(element);

    setTimeout(() => {
        element.remove();
    }, 3500);

}


function statusBadge(status) {

    const classes = {
        Pendiente: 'gold',
        Aprobado: 'green',
        Rechazado: 'red'
    };

    return `
        <span class="pill ${classes[status] || 'gold'}">
            ${escapeHTML(status)}
        </span>
    `;

}


/* =====================================================
   2. GENERACIÓN AUTOMÁTICA DE CREDENCIALES
===================================================== */

/*
   DEMOSTRACIÓN:

   El usuario será el correo del alumno.
   La contraseña temporal se genera automáticamente
   utilizando el generador criptográfico del navegador.

   En Laravel se implementará la creación real
   de usuarios y el envío seguro de credenciales.
*/


function generateTemporaryPassword(length = 12) {

    const characters =
        'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';

    const bytes = new Uint8Array(length);

    crypto.getRandomValues(bytes);

    return Array.from(bytes, byte =>
        characters[byte % characters.length]
    ).join('');

}


function ensureCredentials(record) {

    if (!record.credentials) {

        record.credentials = {

            username: record.email.trim().toLowerCase(),

            password: generateTemporaryPassword(12),

            generatedAt: new Date().toISOString()

        };

    }

    return record.credentials;

}


/* =====================================================
   3. DATOS DE DEMOSTRACIÓN
===================================================== */

function createDemoRecords() {

    return [

        // PAGOS PENDIENTES

        {
            id: 'MAT-2026-008',
            student: 'Valeria López Rojas',
            dni: '74125896',
            email: 'valeria.demo@example.com',
            course: 'Piano Inicial',
            amount: 180,
            date: dateOffset(0),
            method: 'Yape',
            status: 'Pendiente',
            voucherName: 'voucher_valeria.jpg',
            voucherUrl: null,
            reason: '',
            accessSent: false
        },

        {
            id: 'MAT-2026-009',
            student: 'Mateo Torres Salas',
            dni: '72934185',
            email: 'mateo.demo@example.com',
            course: 'Guitarra Básica',
            amount: 220,
            date: dateOffset(0),
            method: 'Yape',
            status: 'Pendiente',
            voucherName: 'voucher_mateo.jpg',
            voucherUrl: null,
            reason: '',
            accessSent: false
        },

        {
            id: 'MAT-2026-010',
            student: 'Camila Ruiz Flores',
            dni: '71845236',
            email: 'camila.demo@example.com',
            course: 'Canto Inicial',
            amount: 150,
            date: dateOffset(1),
            method: 'Yape',
            status: 'Pendiente',
            voucherName: 'voucher_camila.jpg',
            voucherUrl: null,
            reason: '',
            accessSent: false
        },


        // PAGOS APROBADOS

        {
            id: 'MAT-2026-005',
            student: 'Diego Herrera Mendoza',
            dni: '70456321',
            email: 'diego.demo@example.com',
            course: 'Piano Intermedio',
            amount: 250,
            date: dateOffset(2),
            method: 'Yape',
            status: 'Aprobado',
            voucherName: 'voucher_diego.jpg',
            voucherUrl: null,
            reason: '',
            accessSent: false
        },

        {
            id: 'MAT-2026-006',
            student: 'Ana Torres Castillo',
            dni: '75632148',
            email: 'ana.demo@example.com',
            course: 'Guitarra Básica',
            amount: 220,
            date: dateOffset(3),
            method: 'Yape',
            status: 'Aprobado',
            voucherName: 'voucher_ana.jpg',
            voucherUrl: null,
            reason: '',
            accessSent: true
        },


        // PAGO RECHAZADO

        {
            id: 'MAT-2026-003',
            student: 'Luis Ramírez Paredes',
            dni: '71234567',
            email: 'luis.demo@example.com',
            course: 'Canto Inicial',
            amount: 150,
            date: dateOffset(4),
            method: 'Yape',
            status: 'Rechazado',
            voucherName: 'voucher_luis.jpg',
            voucherUrl: null,
            reason: 'El monto del comprobante no coincide con el importe de la matrícula.',
            accessSent: false
        }

    ];

}


/* =====================================================
   4. NAVEGACIÓN
===================================================== */

const breadcrumbNames = {

    resumen: 'Resumen de pagos',
    pendientes: 'Pagos pendientes',
    historial: 'Historial de pagos',
    yape: 'Registrar pago',
    estado: 'Estado de matrícula',
    acceso: 'Pago aprobado y acceso'

};


function navigate(view) {

    const screen = $(`#screen-${view}`);

    if (!screen) return;

    $$('.screen').forEach(element => {
        element.classList.remove('active');
    });

    screen.classList.add('active');


    $$('.side-item').forEach(button => {

        button.classList.toggle(
            'active',
            button.dataset.view === view
        );

    });


    $('#breadcrumbName').textContent =
        breadcrumbNames[view] || 'DUNAMYS';


    document.body.classList.remove('menu-open');


    if (view === 'resumen') renderDashboard();

    if (view === 'pendientes') renderPendingTable();

    if (view === 'historial') renderHistoryTable();

    if (view === 'estado') renderStudentStatus();

    if (view === 'acceso') renderAccessPage();


    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

}


/* NAVEGACIÓN GLOBAL */

document.addEventListener('click', event => {

    const button = event.target.closest('[data-view]');

    if (!button) return;

    event.preventDefault();

    navigate(button.dataset.view);

});


/* =====================================================
   5. MENÚ MÓVIL
===================================================== */

$('#menuToggle')?.addEventListener('click', () => {

    document.body.classList.toggle('menu-open');

});


$('#scrim')?.addEventListener('click', () => {

    document.body.classList.remove('menu-open');

});


/* =====================================================
   6. ESTADÍSTICAS
===================================================== */

function renderStats() {

    const container = $('#stats');

    const total = records.length;

    const pending = records.filter(
        record => record.status === 'Pendiente'
    ).length;

    const approved = records.filter(
        record => record.status === 'Aprobado'
    ).length;

    const rejected = records.filter(
        record => record.status === 'Rechazado'
    ).length;

    const collected = records
        .filter(record => record.status === 'Aprobado')
        .reduce((sum, record) => sum + Number(record.amount), 0);


    const stats = [

        {
            label: 'Total de registros',
            number: total,
            help: 'Todos los movimientos',
            symbol: '▦',
            color: ''
        },

        {
            label: 'Pagos pendientes',
            number: pending,
            help: 'Esperando validación',
            symbol: '◷',
            color: 'gold'
        },

        {
            label: 'Pagos aprobados',
            number: approved,
            help: `${rejected} pagos rechazados`,
            symbol: '✓',
            color: 'green'
        },

        {
            label: 'Total recaudado',
            number: money(collected),
            help: 'Solo pagos aprobados',
            symbol: 'S/',
            color: 'red'
        }

    ];


    container.innerHTML = stats.map(stat => `

        <article class="stat">

            <span class="stat-label">
                ${stat.label}
            </span>

            <span class="stat-chip ${stat.color}">
                ${stat.symbol}
            </span>

            <strong class="stat-num">
                ${stat.number}
            </strong>

            <small class="stat-help">
                ${stat.help}
            </small>

        </article>

    `).join('');


    $('#pendingCount').textContent = pending;

    $('#pendingTotal').textContent = pending;

}


/* =====================================================
   7. TABLA RECIENTE
===================================================== */

function renderRecentTable() {

    const container = $('#recentTable');

    const recent = [...records]
        .sort((a, b) => b.date.localeCompare(a.date))
        .slice(0, 5);


    if (!recent.length) {

        container.innerHTML = `
            <div class="card empty">
                <strong>No hay pagos registrados</strong>
                <p>Los movimientos aparecerán aquí.</p>
            </div>
        `;

        return;

    }


    container.innerHTML = `

        <div class="card">

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>ESTUDIANTE</th>
                            <th>CURSO</th>
                            <th>MONTO</th>
                            <th>FECHA</th>
                            <th>ESTADO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>

                    <tbody>

                        ${recent.map(record => `

                            <tr>

                                <td class="td-id">
                                    ${escapeHTML(record.id)}
                                </td>

                                <td class="student-cell">
                                    <strong>${escapeHTML(record.student)}</strong>
                                    <small>DNI: ${escapeHTML(record.dni)}</small>
                                </td>

                                <td>${escapeHTML(record.course)}</td>

                                <td class="num">${money(record.amount)}</td>

                                <td>${formatDate(record.date)}</td>

                                <td>${statusBadge(record.status)}</td>

                                <td>
                                    <button
                                        class="row-action"
                                        data-action="detail"
                                        data-id="${escapeHTML(record.id)}">
                                        Ver detalle
                                    </button>
                                </td>

                            </tr>

                        `).join('')}

                    </tbody>

                </table>

            </div>

            <div class="table-footer">
                Mostrando ${recent.length} movimientos recientes.
            </div>

        </div>

    `;

}


/* =====================================================
   8. TABLA DE PENDIENTES
===================================================== */

function renderPendingTable() {

    const container = $('#pendingTable');

    const search = (
        $('#pendingSearch').value || ''
    ).toLowerCase().trim();


    const pending = records.filter(record => {

        if (record.status !== 'Pendiente') return false;

        const text = [

            record.student,
            record.dni,
            record.id,
            record.course

        ].join(' ').toLowerCase();

        return text.includes(search);

    });


    if (!pending.length) {

        container.innerHTML = `

            <div class="empty">

                <div class="empty-ico">✓</div>

                <strong>No hay pagos pendientes</strong>

                <p>
                    No existen comprobantes pendientes de revisión.
                </p>

            </div>

        `;

        return;

    }


    container.innerHTML = `

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>MATRÍCULA</th>
                        <th>ESTUDIANTE</th>
                        <th>CURSO</th>
                        <th>MONTO</th>
                        <th>FECHA</th>
                        <th>ESTADO</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>

                <tbody>

                    ${pending.map(record => `

                        <tr>

                            <td class="td-id">
                                ${escapeHTML(record.id)}
                            </td>

                            <td class="student-cell">
                                <strong>${escapeHTML(record.student)}</strong>
                                <small>DNI: ${escapeHTML(record.dni)}</small>
                            </td>

                            <td>${escapeHTML(record.course)}</td>

                            <td class="num">${money(record.amount)}</td>

                            <td>${formatDate(record.date)}</td>

                            <td>${statusBadge(record.status)}</td>

                            <td>
                                <button
                                    class="row-action"
                                    data-action="detail"
                                    data-id="${escapeHTML(record.id)}">
                                    Revisar
                                </button>
                            </td>

                        </tr>

                    `).join('')}

                </tbody>

            </table>

        </div>

        <div class="table-footer">
            ${pending.length} comprobantes pendientes de validación.
        </div>

    `;

}


$('#pendingSearch').addEventListener(
    'input',
    renderPendingTable
);


/* =====================================================
   9. HISTORIAL DE PAGOS
===================================================== */

function renderHistoryTable() {

    const container = $('#historyTable');

    const search = (
        $('#historySearch').value || ''
    ).toLowerCase().trim();

    const status = $('#historyStatus').value;


    const filtered = records.filter(record => {

        const text = [

            record.id,
            record.student,
            record.dni,
            record.course

        ].join(' ').toLowerCase();

        const matchesSearch = text.includes(search);

        const matchesStatus =
            status === 'todos' || record.status === status;

        return matchesSearch && matchesStatus;

    }).sort((a, b) => b.date.localeCompare(a.date));


    if (!filtered.length) {

        container.innerHTML = `
            <div class="empty">
                <strong>No se encontraron resultados</strong>
                <p>Prueba con otro nombre, DNI o estado.</p>
            </div>
        `;

        return;

    }


    container.innerHTML = `

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th>ESTUDIANTE</th>
                        <th>CURSO</th>
                        <th>MÉTODO</th>
                        <th>MONTO</th>
                        <th>FECHA</th>
                        <th>ESTADO</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>

                <tbody>

                    ${filtered.map(record => `

                        <tr>

                            <td class="td-id">
                                ${escapeHTML(record.id)}
                            </td>

                            <td class="student-cell">
                                <strong>${escapeHTML(record.student)}</strong>
                                <small>DNI: ${escapeHTML(record.dni)}</small>
                            </td>

                            <td>${escapeHTML(record.course)}</td>

                            <td>${escapeHTML(record.method)}</td>

                            <td class="num">${money(record.amount)}</td>

                            <td>${formatDate(record.date)}</td>

                            <td>${statusBadge(record.status)}</td>

                            <td>
                                <button
                                    class="row-action"
                                    data-action="detail"
                                    data-id="${escapeHTML(record.id)}">
                                    Ver
                                </button>
                            </td>

                        </tr>

                    `).join('')}

                </tbody>

            </table>

        </div>

        <div class="table-footer">
            ${filtered.length} registros encontrados.
        </div>

    `;

}


$('#historySearch').addEventListener(
    'input',
    renderHistoryTable
);

$('#historyStatus').addEventListener(
    'change',
    renderHistoryTable
);


/* =====================================================
   10. ACTUALIZAR TODAS LAS VISTAS
===================================================== */

function renderDashboard() {

    renderStats();

    renderRecentTable();

}


function renderAll() {

    renderDashboard();

    renderPendingTable();

    renderHistoryTable();

    renderStudentStatus();

    renderAccessPage();

}


/* =====================================================
   11. MODALES
===================================================== */

function openModal(overlay) {

    if (!overlay) return;

    overlay.hidden = false;

    document.body.classList.add('locked');

}


function closeModal(overlay) {

    if (!overlay) return;

    overlay.hidden = true;

    if ($$('.overlay:not([hidden])').length === 0) {

        document.body.classList.remove('locked');

    }

}


function closeAllModals() {

    $$('.overlay').forEach(overlay => {

        overlay.hidden = true;

    });

    document.body.classList.remove('locked');

}


/* =====================================================
   12. DETALLE DEL COMPROBANTE
===================================================== */

function openDetail(id) {

    const record = getRecord(id);

    if (!record) return;

    currentDetailId = id;


    let voucher = '';

    if (record.voucherUrl) {

        voucher = `
            <img
                src="${escapeHTML(record.voucherUrl)}"
                alt="Comprobante de pago">
        `;

    } else {

        voucher = `
            <strong>Comprobante de demostración</strong>
            <small>${escapeHTML(record.voucherName || 'Sin archivo adjunto')}</small>
            <small>No se ha cargado una imagen real para este registro.</small>
        `;

    }


    $('#detailBody').innerHTML = `

        <div class="detail-grid">

            <div class="detail-field">
                <small>Código de matrícula</small>
                <strong>${escapeHTML(record.id)}</strong>
            </div>

            <div class="detail-field">
                <small>Estado actual</small>
                ${statusBadge(record.status)}
            </div>

            <div class="detail-field">
                <small>Estudiante</small>
                <strong>${escapeHTML(record.student)}</strong>
            </div>

            <div class="detail-field">
                <small>DNI</small>
                <strong>${escapeHTML(record.dni)}</strong>
            </div>

            <div class="detail-field">
                <small>Correo electrónico</small>
                <strong>${escapeHTML(record.email)}</strong>
            </div>

            <div class="detail-field">
                <small>Curso</small>
                <strong>${escapeHTML(record.course)}</strong>
            </div>

            <div class="detail-field">
                <small>Monto</small>
                <strong>${money(record.amount)}</strong>
            </div>

            <div class="detail-field">
                <small>Fecha</small>
                <strong>${formatDate(record.date)}</strong>
            </div>

        </div>

        <h3 style="font-size:12px;margin-bottom:12px">
            Comprobante adjunto
        </h3>

        <div class="voucher-display">
            ${voucher}
        </div>

        ${record.reason ? `
            <div class="warning-box">
                <strong>Motivo de rechazo</strong>
                ${escapeHTML(record.reason)}
            </div>
        ` : ''}

    `;


    const detailFoot = $('#detailFoot');


    if (record.status === 'Pendiente') {

        detailFoot.innerHTML = `

            <button
                class="btn outline"
                data-action="reject"
                data-id="${escapeHTML(record.id)}">
                Rechazar pago
            </button>

            <button
                class="btn green"
                data-action="approve"
                data-id="${escapeHTML(record.id)}">
                ✓ Aprobar pago
            </button>

        `;

    } else if (record.status === 'Aprobado') {

        detailFoot.innerHTML = `

            <button class="btn outline close-overlay">
                Cerrar
            </button>

            <button class="btn red" data-view="acceso">
                Ver acceso del alumno →
            </button>

        `;

    } else {

        detailFoot.innerHTML = `
            <button class="btn outline close-overlay">
                Cerrar
            </button>
        `;

    }


    openModal($('#detailOverlay'));

}


/* =====================================================
   13. APROBAR PAGO
===================================================== */

function approvePayment(id) {

    const record = getRecord(id);

    if (!record || record.status !== 'Pendiente') return;


    const confirmed = window.confirm(

        `¿Confirmas la aprobación del pago de ${record.student} por ${money(record.amount)}?`

    );

    if (!confirmed) return;


    // CAMBIAR ESTADO

    record.status = 'Aprobado';

    record.approvedAt = dateOffset(0);

    record.reason = '';

    record.accessSent = false;


    // GENERAR AUTOMÁTICAMENTE LAS CREDENCIALES

    ensureCredentials(record);


    // SELECCIONAR ALUMNO APROBADO

    currentAccessId = record.id;


    // ACTUALIZAR Y ABRIR ACCESO

    closeAllModals();

    renderAll();

    navigate('acceso');


    toast(
        `Pago aprobado. Credenciales generadas para ${record.student}.`
    );

}


/* =====================================================
   14. RECHAZAR PAGO
===================================================== */

function startReject(id) {

    const record = getRecord(id);

    if (!record || record.status !== 'Pendiente') return;

    currentRejectId = id;

    $('#rejectReason').value = '';

    $('#rejectError').textContent = '';

    closeAllModals();

    openModal($('#rejectOverlay'));

}


$('#rejectForm').addEventListener('submit', event => {

    event.preventDefault();

    const reason = $('#rejectReason').value.trim();

    if (reason.length < 5) {

        $('#rejectError').textContent =
            'Escribe un motivo de al menos 5 caracteres.';

        return;

    }


    const record = getRecord(currentRejectId);

    if (!record || record.status !== 'Pendiente') {

        closeAllModals();

        return;

    }


    record.status = 'Rechazado';

    record.reason = reason;

    record.rejectedAt = dateOffset(0);


    closeAllModals();

    renderAll();

    toast(
        `Comprobante rechazado: ${record.student}`,
        'error'
    );

});


/* =====================================================
   15. ACCIONES DE LAS TABLAS
===================================================== */

document.addEventListener('click', event => {

    const button = event.target.closest('[data-action]');

    if (!button) return;

    const action = button.dataset.action;

    const id = button.dataset.id;


    if (action === 'detail') {

        openDetail(id);

    }

    if (action === 'approve') {

        approvePayment(id);

    }

    if (action === 'reject') {

        startReject(id);

    }

});


/* =====================================================
   16. CERRAR MODALES
===================================================== */

document.addEventListener('click', event => {

    if (event.target.closest('.close-overlay')) {

        const overlay = event.target.closest('.overlay');

        closeModal(overlay);

        return;

    }


    if (event.target.classList.contains('overlay')) {

        closeModal(event.target);

    }

});


document.addEventListener('keydown', event => {

    if (event.key === 'Escape') {

        closeAllModals();

        document.body.classList.remove('menu-open');

    }

});


/* =====================================================
   17. COPIAR NÚMERO DE YAPE
===================================================== */

async function copyText(value) {

    try {

        if (navigator.clipboard && window.isSecureContext) {

            await navigator.clipboard.writeText(value);

        } else {

            const temporary = document.createElement('textarea');

            temporary.value = value;

            temporary.style.position = 'fixed';

            temporary.style.opacity = '0';

            document.body.appendChild(temporary);

            temporary.select();

            const copied = document.execCommand('copy');

            temporary.remove();

            if (!copied) throw new Error('No se pudo copiar');

        }

        return true;

    } catch {

        return false;

    }

}


$('#copyNumber').addEventListener('click', async () => {

    const phone = $('#yapePhone').textContent.replace(/\s/g, '');

    const copied = await copyText(phone);

    if (copied) {

        toast('Número de Yape copiado.');

    } else {

        toast('No se pudo copiar el número.', 'error');

    }

});


/* =====================================================
   18. COPIAR CREDENCIALES DEL CORREO
===================================================== */

document.addEventListener('click', async event => {

    const button = event.target.closest('[data-copy-target]');

    if (!button) return;


    const target = $(`#${button.dataset.copyTarget}`);

    if (!target) return;


    const value = target.textContent.trim();

    if (!value || value === '—') {

        toast('No hay información para copiar.', 'error');

        return;

    }


    const copied = await copyText(value);

    if (copied) {

        toast('Información copiada correctamente.');

    } else {

        toast('No se pudo copiar la información.', 'error');

    }

});


/* =====================================================
   19. SELECCIÓN DEL COMPROBANTE
===================================================== */

const voucherInput = $('#voucherFile');

const filePreview = $('#filePreview');

const fileImage = $('#fileImage');

const fileName = $('#fileName');

const fileMeta = $('#fileMeta');

const fileError = $('#fileError');

const dropZone = $('#dropZone');


function clearVoucher() {

    if (selectedVoucher?.url) {

        URL.revokeObjectURL(selectedVoucher.url);

    }

    selectedVoucher = null;

    voucherInput.value = '';

    filePreview.hidden = true;

    fileImage.removeAttribute('src');

    fileError.textContent = '';

}


function validateVoucher(file) {

    if (!file) return false;

    const allowedTypes = [
        'image/jpeg',
        'image/png'
    ];


    if (!allowedTypes.includes(file.type)) {

        fileError.textContent =
            'Solo se permiten imágenes JPG o PNG.';

        return false;

    }


    if (file.size > 5 * 1024 * 1024) {

        fileError.textContent =
            'El archivo supera el límite de 5 MB.';

        return false;

    }


    return true;

}


function selectVoucher(file) {

    if (!file) return;

    clearVoucher();

    if (!validateVoucher(file)) return;


    const url = URL.createObjectURL(file);

    selectedVoucher = {
        file,
        url
    };


    fileImage.src = url;

    fileName.textContent = file.name;

    fileMeta.textContent =
        `${(file.size / 1024).toFixed(1)} KB`;


    filePreview.hidden = false;

    fileError.textContent = '';

}


voucherInput.addEventListener('change', event => {

    selectVoucher(event.target.files[0]);

});


$('#removeVoucher').addEventListener('click', () => {

    clearVoucher();

    toast('Comprobante eliminado.');

});


/* =====================================================
   20. ARRASTRAR Y SOLTAR
===================================================== */

['dragenter', 'dragover'].forEach(type => {

    dropZone.addEventListener(type, event => {

        event.preventDefault();

        dropZone.classList.add('over');

    });

});


['dragleave', 'drop'].forEach(type => {

    dropZone.addEventListener(type, event => {

        event.preventDefault();

        dropZone.classList.remove('over');

    });

});


dropZone.addEventListener('drop', event => {

    const file = event.dataTransfer?.files[0];

    if (file) selectVoucher(file);

});


/* =====================================================
   21. ACTUALIZAR PRECIO DEL CURSO
===================================================== */

function updateCoursePrice() {

    const select = $('#studentCourse');

    const option = select.selectedOptions[0];

    const amount = Number(option.dataset.price || 0);

    $('#paymentAmount').textContent = money(amount);

    $('#summaryAmount').textContent = money(amount);

    $('#summaryCourse').textContent = select.value;

}


$('#studentCourse').addEventListener(
    'change',
    updateCoursePrice
);


/* =====================================================
   22. REGISTRAR NUEVO PAGO
===================================================== */

$('#yapeForm').addEventListener('submit', event => {

    event.preventDefault();


    if (!selectedVoucher) {

        fileError.textContent =
            'Debes seleccionar un comprobante de pago.';

        toast('Adjunta tu comprobante primero.', 'error');

        return;

    }


    if (!$('#transferConfirmed').checked) {

        toast(
            'Debes confirmar que realizaste la transferencia.',
            'error'
        );

        return;

    }


    const student = $('#studentName').value.trim();

    const dni = $('#studentDni').value.trim();

    const email = $('#studentEmail').value.trim().toLowerCase();

    const courseSelect = $('#studentCourse');

    const course = courseSelect.value;

    const amount = Number(
        courseSelect.selectedOptions[0].dataset.price
    );


    const recordId =
        `MAT-2026-${String(Date.now()).slice(-6)}`;


    const record = {

        id: recordId,

        student,

        dni,

        email,

        course,

        amount,

        date: dateOffset(0),

        method: 'Yape',

        status: 'Pendiente',

        voucherName: selectedVoucher.file.name,

        voucherUrl: selectedVoucher.url,

        reason: '',

        accessSent: false

    };


    // GUARDAR SOLICITUD EN LA DEMOSTRACIÓN

    records.unshift(record);

    lastSubmittedId = record.id;


    // TRANSFERIR LA URL DEL ARCHIVO AL REGISTRO

    selectedVoucher = null;


    // LIMPIAR FORMULARIO

    $('#yapeForm').reset();

    filePreview.hidden = true;

    fileImage.removeAttribute('src');

    fileError.textContent = '';

    updateCoursePrice();


    renderAll();

    navigate('estado');


    toast(
        'Comprobante enviado. Está pendiente de revisión.'
    );

});


/* =====================================================
   23. CONSULTAR ESTADO DE MATRÍCULA
===================================================== */

function renderStudentStatus() {

    const container = $('#studentStatus');

    const record =
        getRecord(lastSubmittedId) ||
        records[0];


    if (!record) {

        container.innerHTML = `
            <h2>No hay matrículas registradas</h2>
            <p>Cuando envíes un comprobante podrás consultar aquí su estado.</p>
        `;

        return;

    }


    let content = '';


    if (record.status === 'Pendiente') {

        content = `

            ${statusBadge('Pendiente')}

            <h2>Tu pago está en revisión</h2>

            <p>
                Hola ${escapeHTML(record.student)}.
                Hemos recibido tu comprobante para el curso
                ${escapeHTML(record.course)}.
                Secretaría debe verificarlo antes de confirmar tu matrícula.
            </p>

            <div class="status-notice">

                <strong>Datos de tu solicitud</strong>

                <br>
                Matrícula: ${escapeHTML(record.id)}

                <br>
                Monto: ${money(record.amount)}

                <br>
                Fecha: ${formatDate(record.date)}

            </div>

            <p>
                Cuando tu pago sea aprobado, recibirás las instrucciones
                para ingresar a tu cuenta.
            </p>

        `;

    }


    if (record.status === 'Aprobado') {

        content = `

            ${statusBadge('Aprobado')}

            <h2>¡Tu pago fue aprobado!</h2>

            <p>
                Tu matrícula en ${escapeHTML(record.course)}
                ha sido confirmada.
                Ya puedes consultar tus credenciales de acceso.
            </p>

            <div class="status-notice">

                Matrícula: ${escapeHTML(record.id)}

                <br>
                Monto aprobado: ${money(record.amount)}

                <br>
                Alumno: ${escapeHTML(record.student)}

            </div>

            <button class="btn red" data-view="acceso">
                Ver mi acceso →
            </button>

        `;

    }


    if (record.status === 'Rechazado') {

        content = `

            ${statusBadge('Rechazado')}

            <h2>Tu comprobante necesita corrección</h2>

            <p>
                Secretaría no pudo validar tu pago.
                Revisa el motivo indicado.
            </p>

            <div class="status-reason">

                <strong>Motivo del rechazo:</strong>

                <p>${escapeHTML(record.reason)}</p>

            </div>

            <button class="btn red" data-view="yape">
                Volver a registrar pago →
            </button>

        `;

    }


    container.innerHTML = content;

}


/* =====================================================
   24. PANTALLA DE ACCESO APROBADO Y CORREO
===================================================== */

function renderAccessPage() {

    const record = getRecord(currentAccessId);

    const activationButton = $('#accessActivationButton');


    if (!record || record.status !== 'Aprobado') {

        $('#accessStudent').textContent = 'Sin pagos aprobados';

        $('#accessMatricula').textContent = '—';

        $('#accessCourse').textContent = '—';

        $('#accessAmount').textContent = '—';

        $('#accessUsername').textContent = '—';

        $('#accessEmail').textContent = '—';

        $('#accessPassword').textContent = '—';

        $('#emailTo').textContent = '—';

        $('#emailStudent').textContent = 'Estudiante';

        $('#emailCourse').textContent = '—';

        $('#emailMatricula').textContent = '—';

        $('#emailUsername').textContent = '—';

        $('#emailPassword').textContent = '—';

        $('#emailPreviewState').textContent = 'Sin destinatario';

        activationButton.disabled = true;

        activationButton.textContent = 'Esperando aprobación';

        $('#accessHint').textContent =
            'Cuando apruebes un pago aparecerán aquí las credenciales.';

        return;

    }


    // ASEGURAR QUE EXISTAN CREDENCIALES

    const credentials = ensureCredentials(record);


    const firstName = record.student.split(' ')[0];


    // INFORMACIÓN DEL PANEL IZQUIERDO

    $('#accessStudent').textContent = record.student;

    $('#accessMatricula').textContent = record.id;

    $('#accessCourse').textContent = record.course;

    $('#accessAmount').textContent = money(record.amount);

    $('#accessStatus').textContent = 'Aprobado';


    // USUARIO Y CONTRASEÑA GENERADOS

    $('#accessUsername').textContent = credentials.username;

    $('#accessEmail').textContent = record.email;

    $('#accessPassword').textContent = credentials.password;


    // INFORMACIÓN DEL CORREO

    $('#emailTo').textContent = record.email;

    $('#emailStudent').textContent = firstName;

    $('#emailCourse').textContent = record.course;

    $('#emailMatricula').textContent = record.id;


    // LAS MISMAS CREDENCIALES EN EL CORREO

    $('#emailUsername').textContent = credentials.username;

    $('#emailPassword').textContent = credentials.password;


    // ESTADO DEL CORREO

    const emailState = $('#emailPreviewState');

    if (record.accessSent) {

        emailState.className = 'pill green';

        emailState.textContent = 'Envío simulado completado';

        activationButton.textContent =
            'Correo preparado ✓';

        activationButton.disabled = true;

        $('#accessHint').textContent =
            `Las instrucciones se prepararon para ${record.email}.`;

    } else {

        emailState.className = 'pill gold';

        emailState.textContent = 'Pendiente de envío';

        activationButton.textContent =
            'Simular envío del correo';

        activationButton.disabled = false;

        $('#accessHint').textContent =
            'Las credenciales están generadas y listas para el correo de bienvenida.';

    }

}


/* =====================================================
   25. SIMULAR ENVÍO DEL CORREO
===================================================== */

$('#accessActivationButton').addEventListener('click', () => {

    const record = getRecord(currentAccessId);


    if (!record || record.status !== 'Aprobado') {

        toast('Primero debes tener un pago aprobado.', 'error');

        return;

    }


    if (record.accessSent) {

        toast('El correo ya fue preparado.');

        return;

    }


    // SOLO SIMULACIÓN VISUAL.
    // EN LARAVEL SE UTILIZARÁ MAIL PARA EL ENVÍO REAL.

    record.accessSent = true;

    renderAccessPage();

    toast(
        `Correo de bienvenida preparado para ${record.email}.`
    );

});


/* =====================================================
   26. EXPORTAR HISTORIAL CSV
===================================================== */

$('#exportCsv').addEventListener('click', () => {

    if (!records.length) {

        toast('No hay registros para exportar.', 'error');

        return;

    }


    const headers = [

        'Código',
        'Estudiante',
        'DNI',
        'Correo',
        'Curso',
        'Monto',
        'Fecha',
        'Método',
        'Estado',
        'Motivo de rechazo'

    ];


    const safeCell = value => {

        let text = String(value ?? '');

        if (/^[=+\-@\t\r]/.test(text)) {

            text = "'" + text;

        }

        return `"${text.replace(/"/g, '""')}"`;

    };


    const rows = records.map(record => [

        record.id,
        record.student,
        record.dni,
        record.email,
        record.course,
        Number(record.amount).toFixed(2),
        record.date,
        record.method,
        record.status,
        record.reason

    ].map(safeCell).join(','));


    const csv = '\uFEFF' + [

        headers.map(safeCell).join(','),

        ...rows

    ].join('\r\n');


    const blob = new Blob(
        [csv],
        { type: 'text/csv;charset=utf-8;' }
    );


    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');

    link.href = url;

    link.download = 'historial_pagos_dunamys.csv';

    document.body.appendChild(link);

    link.click();

    link.remove();

    URL.revokeObjectURL(url);


    toast('Historial exportado correctamente.');

});


/* =====================================================
   27. RESTAURAR DEMOSTRACIÓN
===================================================== */

function resetDemo() {

    records.forEach(record => {

        if (record.voucherUrl?.startsWith('blob:')) {

            URL.revokeObjectURL(record.voucherUrl);

        }

    });


    if (selectedVoucher?.url) {

        URL.revokeObjectURL(selectedVoucher.url);

    }


    selectedVoucher = null;

    records = createDemoRecords();

    currentAccessId = 'MAT-2026-005';

    lastSubmittedId = 'MAT-2026-008';


    // Generar credenciales para los registros
    // que ya están aprobados en la demostración.

    records
        .filter(record => record.status === 'Aprobado')
        .forEach(ensureCredentials);


    clearVoucher();

    $('#yapeForm').reset();

    updateCoursePrice();

    renderAll();

    navigate('resumen');

    toast('Datos de demostración restaurados.');

}


/* =====================================================
   28. INICIALIZACIÓN
===================================================== */

function initializeDunamys() {

    records = createDemoRecords();


    // Credenciales para alumnos de ejemplo aprobados.

    records
        .filter(record => record.status === 'Aprobado')
        .forEach(ensureCredentials);


    // Solicitud inicial para consultar.

    lastSubmittedId = 'MAT-2026-008';


    // Abrir la demostración de un pago aprobado.

    currentAccessId = 'MAT-2026-005';


    updateCoursePrice();

    renderAll();

    navigate('resumen');


    console.log(
        'DUNAMYS: módulo de pagos inicializado correctamente.'
    );

    console.log(
        'Credenciales de demostración generadas para alumnos aprobados.'
    );

}


document.addEventListener(
    'DOMContentLoaded',
    initializeDunamys,
    { once: true }
);
