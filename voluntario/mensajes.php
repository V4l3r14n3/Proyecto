<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Validación de acceso
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== "voluntario") {
    header("Location: ../pages/login.php");
    exit();
}

$idVoluntario = (string)$_SESSION['usuario']['_id'];

// Obtener mensajes dirigidos al voluntario
$mensajes = $bd->mensajes->find([
    "para" => $idVoluntario
]);
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <h2>📩 Mis mensajes</h2>

    <form method="POST" action="funciones/enviar_mensaje.php" class="filtros">
        <input type="text" name="para" placeholder="Enviar mensaje a organización (Nombre exacto)">
        <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
        <button type="submit">Enviar</button>
    </form>

    <table class="tabla">
        <tr>
            <th>De</th>
            <th>Mensaje</th>
            <th>Fecha</th>
        </tr>

        <?php foreach ($mensajes as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['de']) ?></td>
                <td><?= htmlspecialchars($m['mensaje']) ?></td>
                <td><?= date("d/m/Y h:i A", strtotime($m['fecha'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'includes/layout_footer.php'; ?>
