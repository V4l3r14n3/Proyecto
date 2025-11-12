<?php
include '../includes/conexion.php';

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = $_POST['password'];
$rol = $_POST['rol'];

if ($rol === 'organizacion') {
    $nombre_org = !empty($_POST['nombre_org_nueva'])
        ? $_POST['nombre_org_nueva']
        : $_POST['nombre_org'];
} else {
    $nombre_org = null;
}

$usuarios->insertOne([
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'rol' => $rol,
    'nombre_org' => $nombre_org
]);

$titulo = "Registro exitoso";
$mensaje = "Tu cuenta ha sido creada correctamente. Ya puedes iniciar sesión.";
$tipo = "success";
$link = "login.php";
include '../includes/mensaje.php';
