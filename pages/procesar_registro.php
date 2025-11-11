<?php
include '../includes/conexion.php';

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = $_POST['password'];
$rol = $_POST['rol'];

// Si elige organización
if ($rol === 'organizacion') {
    if (!empty($_POST['nombre_org_nueva'])) {
        $nombre_org = $_POST['nombre_org_nueva'];
    } else {
        $nombre_org = $_POST['nombre_org'];
    }
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

echo "<h2>Registro exitoso</h2>";
echo "<a href='login.php'>Iniciar sesión</a>";
?>
