<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

session_start();

$id_otro = $_GET['id_otro'] ?? null;
$id_usuarioRaw = $_SESSION['usuario']['_id'] ?? null;

// Normalizar ID
$id_usuario = is_array($id_usuarioRaw) ? $id_usuarioRaw['$oid'] : $id_usuarioRaw;

if (!$id_otro || !$id_usuario) {
    echo json_encode([]);
    exit();
}

$idUsuario = new ObjectId((string)$id_usuario);
$idOtro = new ObjectId((string)$id_otro);

$mensajes = $bd->mensajes->find(
    [
        '$or' => [
            ['remitente_id' => $idUsuario, 'receptor_id' => $idOtro],
            ['remitente_id' => $idOtro, 'receptor_id' => $idUsuario]
        ]
    ],
    ['sort' => ['fecha' => 1]]
);

$resultado = [];

foreach ($mensajes as $m) {
    $resultado[] = [
        "remitente_id" => (string)$m['remitente_id'],
        "mensaje" => $m['mensaje'],
        "fecha" => $m['fecha']->toDateTime()->format("d/m/Y h:i A")
    ];
}

echo json_encode($resultado);
?>
