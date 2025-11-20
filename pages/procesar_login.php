<?php
session_start();
include '../includes/conexion.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$usuario = $bd->usuarios->findOne(['email' => $email]);

if ($usuario && $usuario['password'] === $password) {
    // Convertir objeto MongoDB a array antes de guardarlo
    $_SESSION['usuario'] = json_decode(json_encode($usuario), true);


    if ($usuario['rol'] === 'voluntario') {
        header("Location: ../voluntario/index.php");
    } elseif ($usuario['rol'] === 'organizacion') {
        header("Location: ../organizacion/index.php");
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
