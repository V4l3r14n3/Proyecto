<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

// Protege acceso
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../pages/login.php");
    exit();
}

// --- FILTROS ---
$filtro = ["organizacion" => $_SESSION['usuario']['nombre_org']];

if (!empty($_GET['buscar'])) {
    $filtro["titulo"] = ['$regex' => $_GET['buscar'], '$options' => 'i'];
}

if (!empty($_GET['fecha'])) {
    $filtro["fecha"] = $_GET['fecha'];
}

if (!empty($_GET['ciudad'])) {
    $filtro["ciudad"] = ['$regex' => $_GET['ciudad'], '$options' => 'i'];
}

// Obtener resultados
$voluntariados = $bd->actividades->find($filtro);
?>

<link rel="stylesheet" href="../css/panel.css">

<div class="main-content">

    <h2>Mis voluntariados 🗂️</h2>

    <!-- ================= FILTROS ================= -->
    <form method="GET" class="filtros">
        <input type="text" name="buscar" placeholder="Buscar por título..." value="<?= $_GET['buscar'] ?? '' ?>">

        <input type="date" name="fecha" value="<?= $_GET['fecha'] ?? '' ?>">

        <input type="text" name="ciudad" placeholder="Ciudad..." value="<?= $_GET['ciudad'] ?? '' ?>">

        <button type="submit">Filtrar</button>
        <a href="mis_voluntariados.php" class="btn-reset">Limpiar</a>
    </form>

    <!-- ================= TABLA ================= -->
    <table class="tabla">
        <tr>
            <th>Título</th>
            <th>Descripción</th>
            <th>Ciudad</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($voluntariados as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['titulo']) ?></td>
                <td><?= htmlspecialchars($v['descripcion']) ?></td>
                <td><?= htmlspecialchars($v['ciudad'] ?? 'No definida') ?></td>
                <td>
                    <?php
                    $fechaMostrar = isset($v['fecha_hora']) ? $v['fecha_hora'] : $v['fecha'];

                    // Convertir y formatear fecha
                    $fechaFormateada = date("d/m/Y h:i A", strtotime($fechaMostrar));
                    echo $fechaFormateada;
                    ?>
                </td>
                <td>
                    <a href="funciones/editar_voluntariado.php?id=<?= $v['_id'] ?>" class="btn-accion btn-editar">✏️ Editar</a>
                    <a href="funciones/eliminar_voluntariado.php?id=<?= $v['_id'] ?>" class="btn-accion btn-eliminar">🗑️ Eliminar</a>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function eliminarVoluntariado(id) {
        Swal.fire({
            title: "¿Eliminar voluntariado?",
            text: "Esta acción no puede deshacerse",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#00724f",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(async (result) => {
            if (result.isConfirmed) {
                const response = await fetch("funciones/eliminar_voluntariado.php?id=" + id);
                const resultData = await response.json();

                Swal.fire({
                    icon: resultData.status,
                    text: resultData.mensaje,
                }).then(() => location.reload());
            }
        });
    }
</script>

<?php include 'includes/layout_footer.php'; ?>