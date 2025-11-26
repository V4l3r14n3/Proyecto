<?php
session_start();
include '../includes/conexion.php';

$id = $_POST['id'] ?? '';
$nuevo_url = $_POST['verificacion_url'] ?? '';

if (!$id || !$nuevo_url) {
    echo json_encode([
        "titulo" => "Error",
        "mensaje" => "Faltan datos para actualizar.",
        "tipo" => "error"
    ]);
    exit;
}

$bd->usuarios->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($id)],
    ['$set' => [
        'verificacion_url' => $nuevo_url,
        'estado' => 'pendiente',
        'motivo_rechazo' => null
    ]]
);

unset($_SESSION['usuario_temporal']);

echo json_encode([
    "titulo" => "Documentos enviados",
    "mensaje" => "Tu organización fue enviada nuevamente para verificación.",
    "tipo" => "success"
]);
