
/* =========================================
   DUNAMYS - LIBRO DE RECLAMACIONES
========================================= */

const claimsForm = document.getElementById("claimsForm");

const successModal = document.getElementById("claimsSuccess");

const successDescription =
    document.getElementById("successDescription");

// =========================================
// FECHA ACTUAL
// =========================================

const today = new Date().toLocaleDateString("en-CA");

document.getElementById("incidentDate").max = today;

// =========================================
// CONTADORES DE CARACTERES
// =========================================

function characterCounter(inputId, counterId) {

    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);

    input.addEventListener("input", () => {
        counter.textContent = input.value.length;
    });
}

characterCounter("claimDescription", "descriptionCount");
characterCounter("claimRequest", "requestCount");

// =========================================
// ENVÍO DEL FORMULARIO
// =========================================

claimsForm.addEventListener("submit", event => {

    event.preventDefault();

    if (!claimsForm.reportValidity()) {
        return;
    }

    const type = document.querySelector(
        'input[name="claimType"]:checked'
    );

    if (!type) {
        alert("Selecciona si deseas registrar un reclamo o una queja.");
        return;
    }

    const incidentDate = document.getElementById("incidentDate").value;

    if (incidentDate > today) {
        alert("La fecha del incidente no puede ser futura.");
        return;
    }

    if (
        !document.getElementById("truthDeclaration").checked ||
        !document.getElementById("privacyConsent").checked
    ) {
        alert("Debes aceptar las declaraciones para continuar.");
        return;
    }

    const name = document.getElementById("consumerName").value.trim();
    const lastName = document.getElementById("consumerLastName").value.trim();

    successDescription.textContent =
        "La información de " + name + " " + lastName +
        " ha sido validada correctamente en esta demostración. " +
        "Tipo de solicitud: " + type.value + ".";

    successModal.classList.add("open");
    document.body.style.overflow = "hidden";
});

// =========================================
// CERRAR CONFIRMACIÓN
// =========================================

document.getElementById("closeSuccess").addEventListener("click", () => {

    successModal.classList.remove("open");
    document.body.style.overflow = "";

    claimsForm.reset();

    document.getElementById("descriptionCount").textContent = "0";
    document.getElementById("requestCount").textContent = "0";
});

// =========================================
// MENÚ RESPONSIVE
// =========================================

const claimsMenuToggle =
    document.getElementById("claimsMenuToggle");

const claimsNav =
    document.getElementById("claimsNav");

claimsMenuToggle.addEventListener("click", () => {

    claimsNav.classList.toggle("open");

    const icon = claimsMenuToggle.querySelector("i");

    icon.classList.toggle("bi-list");
    icon.classList.toggle("bi-x-lg");
});

claimsNav.querySelectorAll("a").forEach(link => {

    link.addEventListener("click", () => {
        claimsNav.classList.remove("open");
    });

});

// =========================================
// AÑO AUTOMÁTICO
// =========================================

document.getElementById("claimsYear").textContent =
    new Date().getFullYear();
