<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

session_start();

$id_otro = $_GET['id_otro'] ?? null;
$rawUser = $_SESSION['usuario']['_id'] ?? null;

// Normalizar ID
$id_usuario = is_array($rawUser) ? $rawUser['$oid'] : $rawUser;

if (!$id_otro || !$id_usuario) {
    echo json_encode([]);
    exit();
}

$idUsuarioObj = new ObjectId($id_usuario);
$idOtroObj = new ObjectId($id_otro);

$mensajes = $bd->mensajes->find(
    [
        '$or' => [
            ['remitente_id' => $idUsuarioObj, 'receptor_id' => $idOtroObj],
            ['remitente_id' => $idOtroObj, 'receptor_id' => $idUsuarioObj]
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
