// ==========================================
// DUNAMYS - INTERACCIONES (WEB PÚBLICA)
// Cada bloque verifica que sus elementos existan, porque este script se carga
// en todas las páginas públicas (inicio, galería, matrícula, reclamaciones).
// ==========================================

(function () {
    // NAVBAR AL DESPLAZARSE
    window.addEventListener("scroll", () => {
        const navbar = document.querySelector(".navbar");
        if (navbar) {
            navbar.classList.toggle("scrolled", window.scrollY > 50);
        }
    });

    // MENÚ RESPONSIVE (delegación: sobrevive a los re-renderizados de Livewire)
    const setIcon = (toggle, open) => {
        const icon = toggle ? toggle.querySelector("i") : null;
        if (!icon) {
            return;
        }
        icon.classList.toggle("fa-bars", !open);
        icon.classList.toggle("fa-xmark", open);
    };

    document.addEventListener("click", (event) => {
        const toggle = event.target.closest("#menuToggle");
        const navMenu = document.getElementById("navMenu");

        if (toggle && navMenu) {
            const open = navMenu.classList.toggle("open");
            setIcon(toggle, open);
            return;
        }

        // CERRAR MENÚ AL SELECCIONAR UNA SECCIÓN
        if (event.target.closest("#navMenu .nav-link") && navMenu) {
            navMenu.classList.remove("open");
            setIcon(document.getElementById("menuToggle"), false);
        }
    });

    // ANIMACIONES AL APARECER EN PANTALLA
    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("show");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll(".reveal").forEach((element) => observer.observe(element));

        // NAVBAR: SECCIÓN ACTIVA
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    document.querySelectorAll(".nav-link").forEach((link) => {
                        link.classList.toggle("active", link.getAttribute("href") === "#" + entry.target.id);
                    });
                }
            });
        }, { rootMargin: "-30% 0px -60% 0px" });

        document.querySelectorAll("main section[id]").forEach((section) => sectionObserver.observe(section));
    }

    // AÑO AUTOMÁTICO DEL FOOTER
    const year = document.getElementById("year");
    if (year) {
        year.textContent = new Date().getFullYear();
    }
})();
