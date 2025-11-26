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

//=========   FILTROS 🚀   ==========//
$filtro = [];

if (!empty($_GET['buscar'])) {
    $filtro['titulo'] = ['$regex' => $_GET['buscar'], '$options' => 'i'];
}

if (!empty($_GET['ciudad'])) {
    $filtro['ciudad'] = ['$regex' => $_GET['ciudad'], '$options' => 'i'];
}

if (!empty($_GET['organizacion'])) {
    $texto = trim($_GET['organizacion']);
    $texto = preg_quote($texto, '/'); // evita errores con espacios o caracteres raros
    $filtro['organizacion'] = ['$regex' => $texto, '$options' => 'i'];
}

// Corrección: usar fecha_hora con regex
if (!empty($_GET['fecha'])) {
    $filtro['fecha_hora'] = ['$regex' => $_GET['fecha']];
}

// Obtener eventos filtrados
$voluntariados = $bd->actividades->find($filtro);

// Obtener sus postulaciones
$postulaciones = $bd->inscripciones->find([
    'voluntario_id' => $idVoluntario
]);

$postuladosIds = [];
foreach ($postulaciones as $p) {
    $postuladosIds[] = (string)$p['actividad_id'];
}
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">

    <h2>Voluntariados Disponibles 🔍</h2>

    <!-- ======== FILTROS ======== -->
    <form method="GET" class="filtros">
        <input type="text" name="buscar" placeholder="Buscar por título..." value="<?= $_GET['buscar'] ?? '' ?>">

        <input type="text" name="ciudad" placeholder="Ciudad..." value="<?= $_GET['ciudad'] ?? '' ?>">

        <input type="text" name="organizacion" placeholder="Organización..." value="<?= $_GET['organizacion'] ?? '' ?>">

        <input type="date" name="fecha" value="<?= $_GET['fecha'] ?? '' ?>">

        <button type="submit">Filtrar</button>
        <a href="voluntariados.php" class="btn-reset">Limpiar</a>
    </form>

    <table class="tabla">
        <tr>
            <th>Título</th>
            <th>Descripción</th>
            <th>Organización</th>
            <th>Ciudad</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>

        <?php foreach ($voluntariados as $v): ?>
            <?php
            $idActividad = (string)$v['_id'];
            $fechaEvento = strtotime($v['fecha_hora']);
            $falta24Horas = $fechaEvento - time() <= 86400;
            ?>
            <tr>
                <td><?= htmlspecialchars($v['titulo']) ?></td>
                <td><?= htmlspecialchars($v['descripcion']) ?></td>
                <td><?= htmlspecialchars($v['organizacion'] ?? "No definida") ?></td>
                <td><?= htmlspecialchars($v['ciudad'] ?? "No definida") ?></td>
                <td>
                    <?php
                    $fechaFormateada = date("d/m/Y h:i A", strtotime($v['fecha_hora']));
                    echo $fechaFormateada;
                    ?>
                </td>

                <td>
                    <?php if (in_array($idActividad, $postuladosIds)): ?>
                        <span style="color:green; font-weight:bold;">✔ Inscrito</span>
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
            title: "Cerrado ⛔",
            text: "Debes postularte mínimo 24 horas antes del evento."
        });
    }

    function postular(id) {
        Swal.fire({
            title: "¿Postularte?",
            text: "Confirmas que deseas inscribirte en este voluntariado.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#00724f",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, postularme",
            cancelButtonText: "Cancelar"
        }).then(async (result) => {
            if (result.isConfirmed) {
                const request = await fetch("funciones/postular.php?id=" + id);
                const data = await request.json();

                Swal.fire({
                    icon: data.status,
                    text: data.message
                }).then(() => location.reload());
            }
        });
    }
</script>

<?php include 'includes/layout_footer.php'; ?>
