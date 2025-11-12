<?php 
include '../includes/header.php'; 
?>

<!-- El body ya está abierto dentro del header -->
<div class="form-page">
    <section class="formulario">
        <h2>Iniciar Sesión</h2>
        <form action="procesar_login.php" method="POST">
            <label>Correo electrónico:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
