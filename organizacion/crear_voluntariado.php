<?php 
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}
?>

<link rel="stylesheet" href="../css/panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content form-area">
    <h2>Crear nuevo voluntariado 📌</h2>

    <form id="formVoluntariado">
        <label>Título del voluntariado:</label>
        <input type="text" name="titulo" required>

        <label>Descripción:</label>
        <textarea name="descripcion" required></textarea>

        <label>Fecha del evento:</label>
        <input type="date" name="fecha" required>

        <button type="submit">Publicar</button>
    </form>
</div>

<script>
document.getElementById("formVoluntariado").addEventListener("submit", async function(e){
    e.preventDefault();
    
    const data = new FormData(this);

    const response = await fetch("funciones/guardar_voluntariado.php", {
        method: "POST",
        body: data
    });

    const result = await response.json();

    Swal.fire({
        icon: result.status,
        text: result.mensaje,
        confirmButtonColor: "#00724f"
    })

    if(result.status === "success") this.reset();
});
</script>
