
/* =========================================
   DUNAMYS - FUNCIONES DE GALERÍA
========================================= */

const filterButtons = document.querySelectorAll(".gallery-filter");
const mediaCards = document.querySelectorAll(".media-card");

// FILTRAR FOTOGRAFÍAS Y VIDEOS

filterButtons.forEach(button => {

    button.addEventListener("click", () => {

        const filter = button.dataset.filter;

        filterButtons.forEach(btn => {
            btn.classList.remove("active");
        });

        button.classList.add("active");

        mediaCards.forEach(card => {

            const type = card.dataset.type;

            card.hidden =
                filter !== "all" && filter !== type;

            if (card.hidden) {
                card.querySelectorAll("video").forEach(video => {
                    video.pause();
                });
            }
        });
    });
});

// VISOR DE FOTOGRAFÍAS

const modal = document.getElementById("galleryModal");
const modalImage = document.getElementById("modalImage");
const modalTitle = document.getElementById("modalTitle");
const modalClose = document.getElementById("modalClose");

let previousFocus = null;

document.querySelectorAll(".media-open").forEach(button => {

    button.addEventListener("click", () => {

        previousFocus = button;

        modalImage.src = button.dataset.image;
        modalTitle.textContent = button.dataset.title;

        modal.classList.add("open");
        document.body.style.overflow = "hidden";

        modalClose.focus();
    });
});

function closeModal() {

    modal.classList.remove("open");
    document.body.style.overflow = "";

    modalImage.src = "";

    if (previousFocus) {
        previousFocus.focus();
    }
}

modalClose.addEventListener("click", closeModal);

modal.addEventListener("click", event => {

    if (event.target === modal) {
        closeModal();
    }
});

document.addEventListener("keydown", event => {

    if (event.key === "Escape" && modal.classList.contains("open")) {
        closeModal();
    }
});

// AÑO AUTOMÁTICO

document.getElementById("galleryYear").textContent =
    new Date().getFullYear();

// MENÚ RESPONSIVE

const menuToggle = document.getElementById("menuToggle");
const mainNav = document.getElementById("mainNav");

menuToggle.addEventListener("click", () => {

    mainNav.classList.toggle("open");

    const icon = menuToggle.querySelector("i");

    icon.classList.toggle("bi-list");
    icon.classList.toggle("bi-x-lg");
});

mainNav.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", () => {
        mainNav.classList.remove("open");
    });
});
