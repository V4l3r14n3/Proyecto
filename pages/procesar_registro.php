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

// Crear documento base
$documento = [
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'rol' => $rol,
    'nombre_org' => $nombre_org
];

// Si es organización, se registra como "pendiente"
if ($rol === 'organizacion') {
    $documento['estado'] = 'pendiente';
}

try {
    $bd->usuarios->insertOne($documento);

    echo json_encode([
        'status' => 'success',
        'mensaje' => $rol === 'organizacion'
            ? 'Registro exitoso. Tu organización debe ser aprobada antes de iniciar sesión.'
            : 'Registro exitoso. ¡Ya puedes iniciar sesión!'
    ]);

} catch (MongoDB\Driver\Exception\BulkWriteException $e) {

    if ($e->getCode() === 11000) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El correo ya está registrado en el sistema.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el usuario: ' . $e->getMessage()
        ]);
    }
}
