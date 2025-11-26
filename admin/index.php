<?php 
require_once "../includes/conexion.php";
require_once "includes/layout.php"; 
?>

// Verificar rol admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// Obtener organizaciones pendientes
$pendientes = $bd->usuarios->find(['rol' => 'organizacion', 'estado' => 'pendiente']);
?>



<div class="main-content">
    <h2>Panel Admin 👑</h2>
    <p>Gestiona las organizaciones y voluntarios en el sistema.</p>

    <h3>Organizaciones Pendientes de Aprobación</h3>

    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Acción</th>
        </tr>

        <?php foreach ($pendientes as $org): ?>
        <tr>
            <td><?= htmlspecialchars($org['nombre_org'] ?? $org['nombre']) ?></td>
            <td><?= htmlspecialchars($org['email']) ?></td>
            <td>
                <a href="aprobar.php?id=<?= $org['_id'] ?>" class="btn-editar">Aprobar</a>
                <a href="rechazar.php?id=<?= $org['_id'] ?>" class="btn-eliminar">Rechazar</a>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
</div>

<?php include '../includes/footer.php'; ?>
