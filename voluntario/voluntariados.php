<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\ObjectId;

// Verifica acceso
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// Obtener ID del voluntario correctamente
$rawId = $_SESSION['usuario']['_id'];

if (is_array($rawId) && isset($rawId['$oid'])) {
    $rawId = $rawId['$oid'];
}

$idVoluntario = new ObjectId($rawId);

// Cargar voluntariados disponibles
$voluntariados = $bd->actividades->find([]);

// Cargar postulaciones del voluntario
$postulaciones = $bd->inscripciones->find([
    'voluntario_id' => $idVoluntario
]);

// Convertimos a lista para consultar rápido
$postuladosIds = [];
foreach ($postulaciones as $p) {
    $postuladosIds[] = (string)$p['actividad_id'];
}
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">

    <h2>Voluntariados Disponibles 🌱</h2>

    <table class="tabla">
        <tr>
            <th>Título</th>
            <th>Descripción</th>
            <th>Ciudad</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>

        <?php foreach ($voluntariados as $v): ?>

            <?php 
            $idActividad = (string)$v['_id'];
            $fechaEvento = strtotime($v['fecha_hora'] ?? $v['fecha']);
            $falta24Horas = $fechaEvento - time() <= 86400; 
            ?>

            <tr>
                <td><?= htmlspecialchars($v['titulo']) ?></td>
                <td><?= htmlspecialchars($v['descripcion']) ?></td>
                <td><?= htmlspecialchars($v['ciudad'] ?? 'No definida') ?></td>
                <td><?= htmlspecialchars($v['fecha_hora'] ?? $v['fecha']) ?></td>
                <td>

                    <?php if (in_array($idActividad, $postuladosIds)): ?>

                        <span style="color:green; font-weight:bold;">✔ Ya inscrito</span>

                    <?php elseif ($falta24Horas): ?>

                        <button class="btn-eliminar" onclick="alertaNoDisponible()">No disponible</button>

                    <?php else: ?>

                        <button class="btn-editar" onclick="postular('<?= $idActividad ?>')">Postularme</button>

                    <?php endif; ?>

                </td>
            </tr>

        <?php endforeach; ?>
    </table>
</div>

<script>
function alertaNoDisponible() {
    Swal.fire({
        icon: "error",
        title: "No disponible ⛔",
        text: "Solo puedes postularte hasta 24 horas antes del evento."
    });
}

function postular(id) {
    Swal.fire({
        title: "¿Confirmas postularte?",
        text: "Una vez inscrito podrás ver el evento en tus postulaciones.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#00724f",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, postularme",
        cancelButtonText: "Cancelar"
    }).then(async (result) => {
        if (result.isConfirmed) {
            const response = await fetch("funciones/postular.php?id=" + id);
            const data = await response.json();

            Swal.fire({
                icon: data.status,
                text: data.message
            }).then(() => location.reload());
        }
    });
}
</script>

<?php include 'includes/layout_footer.php'; ?>
