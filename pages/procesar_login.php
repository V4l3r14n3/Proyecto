<?php
session_start();
include '../includes/conexion.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$usuario = $bd->usuarios->findOne(['email' => $email]);

if ($usuario && $usuario['password'] === $password) {

    // Si es organización y está pendiente → bloqueo
    if ($usuario['rol'] === 'organizacion' && ($usuario['estado'] ?? 'pendiente') !== 'aprobado') {
        $titulo = "Cuenta en revisión";
        $mensaje = "Tu organización aún no ha sido aprobada por un administrador.";
        $tipo = "warning";
        $link = "login.php";
        include '../includes/mensaje.php';
        exit();
    }

    // Guardar sesión correctamente (MongoDocument → array)
    $_SESSION['usuario'] = json_decode(json_encode($usuario), true);

    // Redirección según rol
    if ($usuario['rol'] === 'voluntario') {
        header("Location: ../voluntario/index.php");
    } elseif ($usuario['rol'] === 'organizacion') {
        header("Location: ../organizacion/index.php");
    } elseif ($usuario['rol'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
} else {
    $titulo = "Error de inicio de sesión";
    $mensaje = "El correo o la contraseña son incorrectos.";
    $tipo = "error";
    $link = "login.php";
    include '../includes/mensaje.php';
}
