<?php
include '../includes/conexion.php';

// Leer datos del formulario
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';
$verificacion_url = $_POST['verificacion_url'] ?? null;

// Organización seleccionada o nueva
if ($rol === 'organizacion') {
    $nombre_org = !empty($_POST['nombre_org_nueva']) 
        ? $_POST['nombre_org_nueva'] 
        : ($_POST['nombre_org'] ?? null);
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
    'nombre_org' => $nombre_org,
    'verificacion_url' => $verificacion_url
];

// Si es organización → queda pendiente
if ($rol === 'organizacion') {
    $documento['estado'] = 'pendiente';
}

try {
    $bd->usuarios->insertOne($documento);

    echo json_encode([
        'status' => 'success',
        'mensaje' => $rol === 'organizacion'
            ? 'Registro enviado. La organización debe ser aprobada antes de iniciar sesión.'
            : 'Registro exitoso. ¡Ya puedes iniciar sesión!'
    ]);

} catch (MongoDB\Driver\Exception\BulkWriteException $e) {

    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al registrar: ' . $e->getMessage()
    ]);
}
?>
