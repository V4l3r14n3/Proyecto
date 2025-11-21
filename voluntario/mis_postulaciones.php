<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// Normalizar ID voluntario
$rawId = $_SESSION['usuario']['_id'];
if (is_array($rawId) && isset($rawId['$oid'])) {
    $rawId = $rawId['$oid'];
}
$idVoluntario = new ObjectId($rawId);

// Obtener postulaciones del voluntario
$postulaciones = $bd->inscripciones->find(["voluntario_id" => $idVoluntario]);

// Obtener IDs de actividades postuladas
$idsActividades = [];
foreach ($postulaciones as $p) {
    $idsActividades[] = new ObjectId($p['actividad_id']);
}

// Obtener detalles de actividades para mostrarlas
$actividades = $bd->actividades->find([
    "_id" => ['$in' => $idsActividades]
]);
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <h2>Mis postulaciones 📌</h2>

    <table class="tabla">
        <tr>
            <th>Título</th>
            <th>Organización</th>
            <th>Ciudad</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>

        <?php foreach ($actividades as $actividad): ?>
            <?php
                $fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);
                $tiempoRestante = $fechaEvento - time();
                $puedeCancelar = $tiempoRestante > 86400; // más de 24h
            ?>
            <tr data-id="<?= $actividad['_id'] ?>">
                <td><?= htmlspecialchars($actividad['titulo']) ?></td>
                <td><?= htmlspecialchars($actividad['organizacion']) ?></td>
                <td><?= htmlspecialchars($actividad['ciudad'] ?? "No definida") ?></td>
<td>
                    <?php
                    $fechaMostrar = isset($v['fecha_hora']) ? $v['fecha_hora'] : $v['fecha'];

                    // Convertir y formatear fecha
                    $fechaFormateada = date("d/m/Y h:i A", strtotime($fechaMostrar));
                    echo $fechaFormateada;
                    ?>
                </td>                <td>
                    <?php if ($puedeCancelar): ?>
                        <button class="btn-eliminar" onclick="cancelar('<?= $actividad['_id'] ?>')">Cancelar</button>
                    <?php else: ?>
                        ⛔ Bloqueado
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
function cancelar(id) {
    Swal.fire({
        title: "¿Cancelar participación?",
        text: "Si cancelas, tu cupo se liberará.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#00724f",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No"
    }).then(async (result) => {
        if (result.isConfirmed) {
            const fila = document.querySelector(`tr[data-id='${id}']`);
            fila.style.opacity = "0";
            fila.style.transform = "translateX(-20px)";
            fila.style.transition = "0.4s ease";

            const request = await fetch("funciones/cancelar_postulacion.php?id=" + id);
            const data = await request.json();

            setTimeout(() => {
                if (data.status === "success") fila.remove();

                Swal.fire({
                    icon: data.status,
                    text: data.message,
                });

            }, 400);
        }
    });
}
</script>


<?php include 'includes/layout_footer.php'; ?>
