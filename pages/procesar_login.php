<?php
session_start();
include '../includes/conexion.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$usuario = $bd->usuarios->findOne(['email' => $email]);

if ($usuario && $usuario['password'] === $password) {

    // Si es organización, validar estados
    if ($usuario['rol'] === 'organizacion') {

        // ---- ORGANIZACIÓN RECHAZADA ----
        if (($usuario['estado'] ?? 'pendiente') === 'rechazado') {

            $_SESSION['usuario_temporal'] = [
                '_id' => (string)$usuario['_id'],
                'email' => $usuario['email'],
                'nombre_org' => $usuario['nombre_org'],
                'verificacion_url' => $usuario['verificacion_url'] ?? '',
                'motivo_rechazo' => $usuario['motivo_rechazo'] ?? ''
            ];

            header("Location: reintentar_verificacion.php");
            exit();
        }

        // ---- ORGANIZACIÓN PENDIENTE ----
        if (($usuario['estado'] ?? 'pendiente') === 'pendiente') {

            $titulo = "Cuenta en revisión";
            $mensaje = "Tu organización aún no ha sido aprobada por un administrador.";
            $tipo = "warning";
            $link = "login.php";
            include '../includes/mensaje.php';
            exit();
        }
    }

    // ---- LOGIN NORMAL ----
    $_SESSION['usuario'] = json_decode(json_encode($usuario), true);

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
