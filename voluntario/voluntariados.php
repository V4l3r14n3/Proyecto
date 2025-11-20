<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Obtener ID del voluntario autenticado
$idVoluntario = $_SESSION['usuario']['_id'];

// Obtener voluntariados disponibles
$voluntariados = $bd->actividades->find();

// Obtener postulaciones del voluntario
$postulaciones = $bd->inscripciones->find(["id_voluntario" => $idVoluntario])->toArray();

// Convertir lista de inscripciones a IDs para validar si ya está inscrito
$inscritos = [];
foreach ($postulaciones as $p) {
    $inscritos[(string)$p["id_actividad"]] = true;
}
?>

<link rel="stylesheet" href="../css/panel.css">

<div class="main-content">

    <h2>Voluntariados Disponibles 🌱</h2>

    <table class="tabla">
        <tr>
            <th>Actividad</th>
            <th>Descripción</th>
            <th>Ciudad</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>

        <?php foreach ($voluntariados as $v): ?>
            <?php
                $id = (string)$v['_id'];
                $fechaEvento = strtotime($v['fecha_hora'] ?? $v['fecha']);
                $ahora = time();

                $puedePostular = ($fechaEvento - $ahora) >= 86400; // 24 horas = 86400 segundos
                $yaInscrito = isset($inscritos[$id]);
            ?>
            <tr>
                <td><?= htmlspecialchars($v['titulo']) ?></td>
                <td><?= htmlspecialchars($v['descripcion']) ?></td>
                <td><?= htmlspecialchars($v['ciudad'] ?? 'No definida') ?></td>
                <td><?= htmlspecialchars($v['fecha_hora'] ?? $v['fecha']) ?></td>

                <td>
                    <?php if ($yaInscrito): ?>
                        <button class="btn-accion btn-editar" disabled>✔ Ya estás inscrito</button>

                    <?php elseif (!$puedePostular): ?>
                        <button class="btn-accion btn-eliminar" disabled>🚫 Fuera de tiempo</button>

                    <?php else: ?>
                        <button class="btn-accion btn-editar"
                                onclick="postular('<?= $id ?>')">Postularme</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function postular(id) {

    Swal.fire({
        title: "¿Deseas postularte?",
        text: "Solo podrás hacerlo una vez.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#00724f",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, postularme",
        cancelButtonText: "Cancelar"
    }).then(async (result) => {

        if (result.isConfirmed) {

            const response = await fetch("postular_voluntariado.php?id=" + id);
            const data = await response.json();

            Swal.fire({
                icon: data.status,
                text: data.mensaje,
            }).then(() => {
                if (data.status === "success") location.reload();
            });
        }
    });
}
</script>

<?php include 'includes/layout_footer.php'; ?>
