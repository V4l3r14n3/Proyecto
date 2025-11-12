<?php
include '../includes/conexion.php';

// Leer datos del formulario
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';

if ($rol === 'organizacion') {
    $nombre_org = !empty($_POST['nombre_org_nueva'])
        ? $_POST['nombre_org_nueva']
        : $_POST['nombre_org'];
} else {
    $nombre_org = null;
}

// Verificar si el correo ya existe
$existe = $bd->usuarios->findOne(['email' => $email]);

if ($existe) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'El correo ya está registrado. Intenta con otro.'
    ]);
    exit;
}

// Guardar usuario
$bd->usuarios->insertOne([
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'rol' => $rol,
    'nombre_org' => $nombre_org
]);

echo json_encode([
    'status' => 'success',
    'mensaje' => 'Registro exitoso. ¡Bienvenido!',
]);
