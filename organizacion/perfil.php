<?php include 'includes/layout.php'; ?>

<h2>Mi Perfil 🏷️</h2>

<form method="POST" action="update_perfil.php" class="perfil-form">
    <label>Nombre responsable:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>" required>

    <label>Email:</label>
    <input type="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>" disabled>

    <label>Nombre de organización:</label>
    <input type="text" name="nombre_org" value="<?= htmlspecialchars($_SESSION['usuario']['nombre_org']) ?>" required>

    <button type="submit">Guardar Cambios</button>
</form>

<?php include 'includes/layout_footer.php'; ?>
