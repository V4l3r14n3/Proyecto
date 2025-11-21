<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

session_start();

// ID del otro usuario (el que escogió del select)
$id_otro = $_GET['id_otro'] ?? null;
$id_usuario = $_SESSION['usuario']['_id'] ?? null;

if (!$id_otro || !$id_usuario) {
    echo json_encode([]);
    exit();
}

// Normalizar IDs
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
        "receptor_id" => (string)$m['receptor_id'],
        "mensaje" => $m['mensaje'],
        "fecha" => $m['fecha']->toDateTime()->format("d/m/Y h:i A")
    ];
}

echo json_encode($resultado);
?>
