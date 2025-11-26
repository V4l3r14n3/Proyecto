<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// Buscar certificados
$todosLosCertificados = $bd->certificados->find();
$misCertificados = [];

foreach ($todosLosCertificados as $cert) {
    if ((string)$cert['voluntario_id'] === (string)$_SESSION['usuario']['_id']['$oid']) {
        $misCertificados[] = $cert;
    }
}
?>

<h2>Mi Perfil 🏷️</h2>

<form method="POST" action="funciones/update_perfil.php" class="perfil-form">
    <label>Nombre Completo:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>" required>

    <label>Email:</label>
    <input type="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>" disabled>

    <label>Nueva contraseña (opcional):</label>
    <input type="password" name="password_nueva" placeholder="Dejar vacío para no cambiar">

    <button type="submit">Guardar Cambios</button>
</form>

<br><br>

<h2>📜 Mis Certificados</h2>

<?php if (count($misCertificados) > 0): ?>
    <table class="tabla">
        <tr>
            <th>Actividad</th>
            <th>Organización</th>
            <th>Fecha</th>
            <th>Horas</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($misCertificados as $cert): ?>
            <tr>
                <td><?= htmlspecialchars($cert['titulo_actividad']) ?></td>
                <td><?= htmlspecialchars($cert['nombre_organizacion']) ?></td>
                <td><?= htmlspecialchars($cert['fecha_actividad']) ?></td>
                <td><?= htmlspecialchars($cert['horas_voluntariado']) ?> h</td>
                <td>
                    <a href="funciones/obtener_certificado.php?id=<?= $cert['_id'] ?>" class="btn-accion btn-editar" target="_blank">👁️ Ver</a>
                    <a href="funciones/descargar_certificado.php?id=<?= $cert['_id'] ?>" class="btn-accion btn-editar" target="_blank">📄 PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php else: ?>
    <p style="text-align:center; color:#777;">Aún no tienes certificados.</p>
<?php endif; ?>

<?php include 'includes/layout_footer.php'; ?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Perfil actualizado',
    text: 'Tus cambios han sido guardados correctamente 💾',
    confirmButtonColor: '#00724f'
}).then(() => {
    history.replaceState(null, "", location.pathname);
});
</script>
<?php endif; ?>
