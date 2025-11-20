<?php
include 'includes/layout.php';
?>

<h2>Crear nuevo voluntariado 📌</h2>

<form id="formVoluntariado" class="formulario-panel">
    <label>Título del voluntariado:</label>
    <input type="text" name="titulo" required>

    <label>Descripción:</label>
    <textarea name="descripcion" required></textarea>

    <label>Fecha y hora del evento:</label>
    <input type="datetime-local" name="fecha" id="fecha" required>

    <label>Ciudad:</label>
    <input type="text" name="ciudad" required>

    <label>Ubicación (URL Google Maps):</label>
    <input type="url" name="ubicacion" placeholder="https://maps.google.com/..." required>

    <button type="submit">Publicar</button>
</form>

<?php include 'includes/layout_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById("formVoluntariado").addEventListener("submit", async function(e) {
        e.preventDefault();

        const fechaInput = document.getElementById("fecha").value;
        const fechaEvento = new Date(fechaInput);

        const hoy = new Date();
        const mañana = new Date();
        mañana.setDate(hoy.getDate() + 1);

        // Validación en el navegador
        if (fechaEvento <= mañana) {
            Swal.fire({
                icon: "error",
                title: "Fecha inválida",
                text: "La fecha debe ser mínimo dentro de 2 días.",
                confirmButtonColor: "#00724f"
            });
            return;
        }

        const data = new FormData(this);

        try {
            const response = await fetch("funciones/guardar_voluntariado.php", {
                method: "POST",
                body: data
            });

            const result = await response.json();

            // Mostrar mensaje desde PHP (éxito o error)
            Swal.fire({
                icon: result.status === "success" ? "success" : "error",
                title: result.status === "success" ? "¡Listo!" : "Error",
                text: result.mensaje,
                confirmButtonColor: "#00724f"
            });

            // Si se guardó correctamente limpiamos formulario
            if (result.status === "success") {
                this.reset();
            }

        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error de servidor",
                text: "No se pudo procesar la solicitud. Intenta más tarde.",
                confirmButtonColor: "#00724f"
            });
        }
    });
</script>