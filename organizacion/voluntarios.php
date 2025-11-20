<?php 
include 'includes/layout.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    header("Location: ../pages/login.php");
    exit();
}

// Obtener solo los eventos de esta organización
$inscripciones = $bd->inscripciones->find(); 
?>

<div class="main-content">
    <h2>Voluntarios inscritos 👥</h2>

    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Evento</th>
            <th>Fecha Registro</th>
        </tr>

        <?php 
        foreach ($inscripciones as $i): 

            // Convertir IDs correctamente
            $actividadId = new ObjectId($i['actividad_id']);
            $voluntarioId = new ObjectId($i['voluntario_id']);

            // Buscar datos del voluntario en colección usuarios
            $voluntario = $bd->usuarios->findOne(["_id" => $voluntarioId]);

            // Buscar datos del evento
            $actividad = $bd->actividades->findOne(["_id" => $actividadId]);

            // Validar que pertenece a la organización que está logueada
            if (!$actividad || $actividad['organizacion'] !== $_SESSION['usuario']['nombre_org']) {
                continue;
            }
        ?>

        <tr>
            <td><?= htmlspecialchars($voluntario['nombre'] ?? 'Desconocido') ?></td>
            <td><?= htmlspecialchars($voluntario['email'] ?? 'No disponible') ?></td>
            <td><?= htmlspecialchars($actividad['titulo'] ?? 'Evento eliminado') ?></td>
            <td><?= htmlspecialchars($i['fecha_registro']) ?></td>
        </tr>

        <?php endforeach; ?>
    </table>
</div>

<?php include 'includes/layout_footer.php'; ?>
