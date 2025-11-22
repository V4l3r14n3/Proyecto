<?php
include $_SERVER['DOCUMENT_ROOT']."/Proyecto/includes/conexion.php";
include "includes/layout.php";
echo "<pre>";
print_r($_SESSION['usuario']);
echo "</pre>";
exit;
if ($_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../index.php");
    exit;
}

// Obtener mensajes SOLO de su organización
$mensajes = $bd->foro->find([
    "id_organizacion" => $_SESSION['usuario']['_id']['$oid']
]);
?>

<div class="main-content">
    <h2>📢 Foro de Mi Organización</h2>

    <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel">
        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Mensaje</label>
        <textarea name="mensaje" required></textarea>

        <button type="submit">Publicar</button>
    </form>

    <table class="tabla">
        <tr>
            <th>Fecha</th>
            <th>Título</th>
            <th>Mensaje</th>
            <th>Autor</th>
        </tr>

        <?php foreach ($mensajes as $m): ?>
        <tr>
            <td><?= $m['fecha'] ?></td>
            <td><?= $m['titulo'] ?></td>
            <td><?= $m['mensaje'] ?></td>
            <td><?= $m['autor'] == "organizacion" ? "📌 Yo" : "🙋‍♂️ Voluntario"; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include 'includes/layout_footer.php'; ?>