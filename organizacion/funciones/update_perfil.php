<?php
session_start();
include '../includes/conexion.php';

$nombre = $_POST['nombre'];
$nombre_org = $_POST['nombre_org'];

$bd->usuarios->updateOne(
    ['email' => $_SESSION['usuario']['email']],
    ['$set' => ['nombre' => $nombre, 'nombre_org' => $nombre_org]]
);

// Actualiza la sesión
$_SESSION['usuario']['nombre'] = $nombre;
$_SESSION['usuario']['nombre_org'] = $nombre_org;

header("Location: perfil.php?update=ok");
exit();
