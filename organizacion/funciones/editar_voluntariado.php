<?php 
include '../includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_GET['id'])) {
    header("Location: mis_voluntariados.php");
    exit();
}

$id = new ObjectId($_GET['id']);
$voluntariado = $bd->actividades->findOne(['_id' => $id]);

?>

<link rel="stylesheet" href="../css/panel.css">

<div class="main-content">
    <h2>Editar Voluntariado ✏️</h2>

    <form id="editarForm" class="formulario-panel">

        <input type="hidden" name="id" value="<?= $_GET['id'] ?>">

        <label>Título:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($voluntariado['titulo']) ?>" required>

        <label>Descripción:</label>
        <textarea name="descripcion" required><?= htmlspecialchars($voluntariado['descripcion']) ?></textarea>

        <label>Ciudad:</label>
        <input type="text" name="ciudad" value="<?= htmlspecialchars($voluntariado['ciudad'] ?? '') ?>" required>

        <label>Fecha y hora:</label>
        <input type="datetime-local" name="fecha_hora" 
               value="<?= date('Y-m-d\TH:i', strtotime($voluntariado['fecha_hora'] ?? $voluntariado['fecha'])) ?>" 
               required>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById("editarForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const data = new FormData(this);

    const response = await fetch("../funciones/update_voluntariado.php", {
        method: "POST",
        body: data
    });

    const result = await response.json();

    Swal.fire({
        icon: result.status,
        text: result.mensaje,
        confirmButtonColor: "#00724f"
    }).then(() => {
        if (result.status === "success") {
            window.location.href = "../mis_voluntariados.php";
        }
    });
});
</script>


<?php include '../includes/layout_footer.php'; ?>
