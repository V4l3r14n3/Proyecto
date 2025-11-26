</div> <!-- cierre main-content -->

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("darkToggle");
    const body = document.body;
    const label = document.querySelector(".theme-toggle .label");

    // Leer modo almacenado
    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-mode");
        toggle.checked = true;
        label.textContent = "Modo claro";
    }

    toggle.addEventListener("change", () => {
        body.classList.add("dark-mode-transition");

        if (toggle.checked) {
            body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
            label.textContent = "Modo claro";
        } else {
            body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
            label.textContent = "Modo oscuro";
        }

        setTimeout(() => body.classList.remove("dark-mode-transition"), 400);
    });
});
</script>

</body>
</html>
