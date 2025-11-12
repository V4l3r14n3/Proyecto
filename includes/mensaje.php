<?php
// includes/mensaje.php
$titulo = $titulo ?? "Mensaje del sistema";
$mensaje = $mensaje ?? "Acción completada";
$tipo = $tipo ?? "info"; // puede ser: success, error, info
?>

<?php include 'header.php'; ?>

<div class="mensaje-container <?= $tipo ?>">
    <h2><?= htmlspecialchars($titulo) ?></h2>
    <p><?= htmlspecialchars($mensaje) ?></p>
    <a href="<?= $link ?? '../index.php' ?>" class="btn">Volver</a>
</div>

<?php include 'footer.php'; ?>
