
/* =========================================
   DUNAMYS - FUNCIONALIDAD DE MATRÍCULA
========================================= */

const pages = document.querySelectorAll(".form-step");
const steps = document.querySelectorAll(".step");

let currentPage = 1;
let selectedCourses = [];
let studentIsMinor = false;

const $ = id => document.getElementById(id);

const money = value =>
    "S/ " + Number(value).toFixed(2);

// =========================================
// 1. NAVEGACIÓN ENTRE PASOS
// =========================================

function showPage(page) {
    currentPage = page;

    pages.forEach(section => {
        section.classList.toggle(
            "active",
            Number(section.dataset.page) === page
        );
    });

    steps.forEach(step => {
        const number = Number(step.dataset.step);

        step.classList.toggle("active", number === page);
        step.classList.toggle("completed", number < page);
    });

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

document.querySelectorAll("[data-next]").forEach(button => {
    button.addEventListener("click", () => {
        const next = Number(button.dataset.next);

        if (next === 2 && selectedCourses.length === 0) {
            alert("Selecciona al menos un curso.");
            return;
        }

        if (next === 3 && !validateSchedules()) return;

        if (next === 6) {
            updatePaymentTotal();
        }

        showPage(next);
    });
});

document.querySelectorAll("[data-back]").forEach(button => {
    button.addEventListener("click", () => {
        showPage(Number(button.dataset.back));
    });
});

// =========================================
// 2. SELECCIÓN MÚLTIPLE DE CURSOS
// =========================================

const courseChecks = document.querySelectorAll(".course-check");

function updateCourses() {
    selectedCourses = [];

    courseChecks.forEach(input => {
        if (input.checked) {
            selectedCourses.push({
                name: input.value,
                price: Number(input.dataset.price),
                schedule: ""
            });
        }
    });

    $("selectedCount").textContent = selectedCourses.length;

    renderSchedules();
}

courseChecks.forEach(input => {
    input.addEventListener("change", updateCourses);
});

// =========================================
// 3. HORARIOS Y COSTOS
// =========================================

const scheduleOptions = [
    "Lunes y miércoles · 9:00 a. m. - 11:00 a. m.",
    "Martes y jueves · 3:00 p. m. - 5:00 p. m.",
    "Sábados · 9:00 a. m. - 12:00 p. m."
];

function renderSchedules() {
    const container = $("scheduleList");

    container.innerHTML = "";

    selectedCourses.forEach((course, index) => {

        const item = document.createElement("div");
        item.className = "schedule-item";

        const info = document.createElement("div");

        const title = document.createElement("h3");
        title.textContent = course.name;

        const description = document.createElement("p");
        description.textContent = "Selecciona un horario";

        info.append(title, description);

        const select = document.createElement("select");
        select.required = true;
        select.dataset.index = index;

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "Seleccionar horario";
        select.appendChild(placeholder);

        scheduleOptions.forEach(schedule => {
            const option = document.createElement("option");
            option.value = schedule;
            option.textContent = schedule;
            select.appendChild(option);
        });

        select.addEventListener("change", () => {
            course.schedule = select.value;
        });

        const price = document.createElement("div");
        price.className = "schedule-price";
        price.textContent = money(course.price);

        item.append(info, select, price);
        container.appendChild(item);
    });

    updateTotals();
}

function validateSchedules() {
    if (selectedCourses.length === 0) {
        alert("Selecciona al menos un curso.");
        showPage(1);
        return false;
    }

    const missing = selectedCourses.some(
        course => !course.schedule
    );

    if (missing) {
        alert("Selecciona un horario para cada curso.");
        return false;
    }

    return true;
}

function getTotal() {
    return selectedCourses.reduce(
        (total, course) => total + course.price,
        0
    );
}

function updateTotals() {
    $("scheduleTotal").textContent = money(getTotal());
    $("finalTotal").textContent = money(getTotal());
    $("paymentTotal").textContent = money(getTotal());
}

// =========================================
// 4. VALIDACIÓN DE DATOS PERSONALES
// =========================================

function validForm(formId) {
    const form = $(formId);

    if (!form.reportValidity()) {
        return false;
    }

    return true;
}

function calculateAge(date) {
    const birth = new Date(date + "T00:00:00");
    const today = new Date();

    let age = today.getFullYear() - birth.getFullYear();

    const monthDifference = today.getMonth() - birth.getMonth();

    if (
        monthDifference < 0 ||
        (monthDifference === 0 && today.getDate() < birth.getDate())
    ) {
        age--;
    }

    return age;
}

$("birthDate").max = new Date().toISOString().split("T")[0];

$("personalContinue").addEventListener("click", () => {

    if (!validForm("personalForm")) return;

    const birthDate = $("birthDate").value;
    const age = calculateAge(birthDate);

    if (age < 0 || age > 120) {
        alert("Verifica la fecha de nacimiento.");
        return;
    }

    studentIsMinor = age < 18;

    if (studentIsMinor) {
        showPage(4);
    } else {
        showPage(5);
        generateSummary();
    }
});

// =========================================
// 5. DATOS DEL APODERADO
// =========================================

$("guardianContinue").addEventListener("click", () => {

    if (!validForm("guardianForm")) return;

    showPage(5);
    generateSummary();
});

// =========================================
// 6. GENERAR RESUMEN
// =========================================

function addSummaryRow(container, label, value) {
    const p = document.createElement("p");

    const title = document.createElement("span");
    title.textContent = label;

    const strong = document.createElement("strong");
    strong.textContent = value;

    p.append(title, strong);
    container.appendChild(p);
}

function generateSummary() {

    const personal = $("summaryPersonal");
    personal.innerHTML = "";
    personal.className = "summary-data";

    addSummaryRow(
        personal,
        "Nombres:",
        $("firstName").value
    );

    addSummaryRow(
        personal,
        "Apellidos:",
        $("lastName").value
    );

    addSummaryRow(
        personal,
        "DNI:",
        $("dni").value
    );

    addSummaryRow(
        personal,
        "Nacimiento:",
        $("birthDate").value
    );

    addSummaryRow(
        personal,
        "Correo:",
        $("email").value
    );

    addSummaryRow(
        personal,
        "Teléfono:",
        $("phone").value
    );

    const guardianContainer = $("summaryGuardianContainer");
    const guardian = $("summaryGuardian");

    if (studentIsMinor) {

        guardianContainer.classList.remove("hidden");
        guardian.innerHTML = "";
        guardian.className = "summary-data";

        addSummaryRow(
            guardian,
            "Nombres:",
            $("guardianName").value
        );

        addSummaryRow(
            guardian,
            "Apellidos:",
            $("guardianLastName").value
        );

        addSummaryRow(
            guardian,
            "DNI:",
            $("guardianDni").value
        );

        addSummaryRow(
            guardian,
            "Teléfono:",
            $("guardianPhone").value
        );

    } else {
        guardianContainer.classList.add("hidden");
    }

    const courses = $("summaryCourses");
    courses.innerHTML = "";

    selectedCourses.forEach(course => {

        const row = document.createElement("div");
        row.className = "summary-course-row";

        const details = document.createElement("div");

        const name = document.createElement("strong");
        name.textContent = course.name;

        const schedule = document.createElement("p");
        schedule.textContent = course.schedule;
        schedule.style.color = "#777";
        schedule.style.marginTop = "6px";
        schedule.style.fontSize = "11px";

        details.append(name, schedule);

        const price = document.createElement("strong");
        price.textContent = money(course.price);

        row.append(details, price);
        courses.appendChild(row);
    });

    updateTotals();
}

// =========================================
// 7. SELECCIÓN DEL MÉTODO DE PAGO
// =========================================

document.querySelectorAll('input[name="payment"]').forEach(input => {

    input.addEventListener("change", () => {

        const message = $("paymentMessage");

        if (input.value === "Yape") {
            message.textContent =
                "Has seleccionado Yape. El pago se realizará mediante la integración de pago correspondiente.";
        }

        if (input.value === "Tarjeta") {
            message.textContent =
                "Has seleccionado tarjeta. Se utilizará una pasarela de pago segura cuando se integre el servicio.";
        }

        message.style.color = "#159447";
    });
});

function updatePaymentTotal() {
    $("paymentTotal").textContent = money(getTotal());
}

// =========================================
// 8. FINALIZAR PROCESO DE DEMOSTRACIÓN
// =========================================

$("finishPayment").addEventListener("click", () => {

    const selectedPayment =
        document.querySelector('input[name="payment"]:checked');

    if (!selectedPayment) {
        alert("Debes seleccionar un método de pago.");
        return;
    }

    const message = selectedPayment.value;

    alert(
        "¡Excelente!\n\n" +
        "Tu selección de matrícula está completa.\n\n" +
        "Estudiante: " + $("firstName").value + " " +
        $("lastName").value + "\n" +
        "Cursos: " + selectedCourses.length + "\n" +
        "Total: " + money(getTotal()) + "\n" +
        "Método: " + message + "\n\n" +
        "Esta es una demostración. El pago real todavía no está conectado."
    );
});

// =========================================
// 9. AÑO AUTOMÁTICO
// =========================================

$("year").textContent = new Date().getFullYear();
