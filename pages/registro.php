<?php include '../includes/header.php'; ?>
<section class="formulario">
    <h2>Registro de Usuario</h2>
    <form action="procesar_registro.php" method="POST">
        <label>Nombre completo:</label>
        <input type="text" name="nombre" required>

        <label>Correo electrónico:</label>
        <input type="email" name="email" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <label>Rol:</label>
        <select name="rol">
            <option value="voluntario">Voluntario</option>
            <option value="organizacion">Organización</option>
        </select>

        <button type="submit">Registrar</button>
    </form>
</section>
<?php include '../includes/footer.php'; ?>
