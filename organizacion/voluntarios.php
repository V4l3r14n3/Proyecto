<?php 
include 'includes/layout.php'; 
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

$voluntarios = $bd->inscripciones->find([
    "organizacion" => $_SESSION['usuario']['nombre_org']
]);
?>

<div class="main-content">
    <h2>Voluntarios inscritos 👥</h2>

    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Evento</th>
        </tr>

        <?php foreach ($voluntarios as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['nombre']) ?></td>
            <td><?= htmlspecialchars($v['email']) ?></td>
            <td><?= htmlspecialchars($v['actividad']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'includes/layout_footer.php'; ?>
