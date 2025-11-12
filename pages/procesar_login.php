<?php
include '../includes/conexion.php';

// Obtener datos del formulario
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Buscar el usuario en la base de datos
$usuario = $bd->usuarios->findOne(['email' => $email]);

if ($usuario && $usuario['password'] === $password) {

    // Iniciar sesión
    session_start();
    $_SESSION['usuario'] = $usuario;

    // Redirección según el rol
    if ($usuario['rol'] === 'voluntario') {
        header("Location: ../voluntario/index.php");
    } elseif ($usuario['rol'] === 'organizacion') {
        header("Location: ../organizacion/index.php");
    } else {
        // Por si hay algún rol desconocido
        header("Location: ../index.php");
    }
    exit();

} else {
    echo "<h2>Credenciales incorrectas</h2>";
    echo "<a href='login.php'>Volver</a>";
}
?>