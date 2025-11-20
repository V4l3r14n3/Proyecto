<?php include 'includes/layout.php'; ?>

<h2>Mi Perfil 🏷️</h2>

<form method="POST" action="funciones/update_perfil.php" class="perfil-form">
    <label>Nombre Completo:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>" required>

    <label>Email:</label>
    <input type="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>" disabled>

    <label>Nueva contraseña (opcional):</label>
    <input type="password" name="password_nueva">

    <button type="submit">Guardar Cambios</button>
</form>

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
