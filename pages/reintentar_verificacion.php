<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/rutas.php";
include '../includes/conexion.php';
include '../includes/header.php'; 

if (!isset($_SESSION['usuario_temporal'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario_temporal'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<title>Reenviar Documentos</title>
<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="form-page">
    <section class="formulario">
        
        <h2>⛔ Organización Rechazada</h2>

        <p><strong>Motivo del rechazo:</strong> <?= htmlspecialchars($usuario['motivo_rechazo']) ?></p>

        <p>Por favor sube nuevamente el enlace de verificación:</p>

        <form id="reenvioForm">
            <label>Nuevo enlace de verificación:</label>
            <input type="url" name="verificacion_url" value="<?= htmlspecialchars($usuario['verificacion_url']) ?>" required>

            <input type="hidden" name="id" value="<?= $usuario['_id'] ?>">

            <button type="submit">📤 Enviar nuevamente</button>
        </form>
    </section>
</div>

<script>
document.getElementById("reenvioForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const respuesta = await fetch("../includes/reenviar_verificacion.php", {
        method: "POST",
        body: formData
    });

    const data = await respuesta.json();

    Swal.fire(data.titulo, data.mensaje, data.tipo).then(() => {
        if (data.tipo === "success") {
            window.location.href = "login.php";
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
