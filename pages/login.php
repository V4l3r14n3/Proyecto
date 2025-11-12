<?php 
include '../includes/header.php'; 
?>

<div class="form-page">
    <section class="formulario">
        <h2>Iniciar Sesión</h2>
        <form action="procesar_login.php" method="POST">
            <label>Correo electrónico:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Ingresar</button>

            <!-- Enlace para registro -->
            <p class="form-link">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </p>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php include '../includes/footer.php'; ?>
