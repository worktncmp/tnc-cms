(() => {
    const toggle = document.querySelector(".nav-toggle");
    const menu = document.getElementById("menu");
    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener("click", () => {
        const open = menu.classList.toggle("is-open");
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
})();
