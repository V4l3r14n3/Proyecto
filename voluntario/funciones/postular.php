<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Verifica acceso
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

// Normalizar ID del voluntario
$rawId = $_SESSION['usuario']['_id'];
if (is_array($rawId) && isset($rawId['$oid'])) {
    $rawId = $rawId['$oid'];
}
$idVoluntario = new ObjectId($rawId);

// ID del voluntariado
$idActividad = new ObjectId($_GET['id'] ?? "");

// Validar si ya está inscrito
$existe = $bd->inscripciones->findOne([
    "voluntario_id" => $idVoluntario,
    "actividad_id" => $idActividad
]);

if ($existe) {
    echo json_encode(["status" => "error", "message" => "Ya estás inscrito en este evento"]);
    exit();
}

// Guardar inscripción
$bd->inscripciones->insertOne([
    "voluntario_id" => $idVoluntario,
    "actividad_id" => $idActividad,
    "fecha_registro" => date("Y-m-d H:i:s")
]);

echo json_encode(["status" => "success", "message" => "¡Inscripción exitosa!"]);
exit();
?>
