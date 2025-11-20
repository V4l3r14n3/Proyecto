<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Seguridad
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID inválido"]);
    exit();
}

// Normalizar ID voluntario
$rawVolId = $_SESSION['usuario']['_id'];
if (is_array($rawVolId) && isset($rawVolId['$oid'])) {
    $rawVolId = $rawVolId['$oid'];
}
$voluntarioId = new ObjectId($rawVolId);

// ID de actividad como STRING (no objectId)
$actividadId = $_GET['id'];

// Buscar actividad para validar fecha
$actividad = $bd->actividades->findOne(["_id" => new ObjectId($actividadId)]);

if (!$actividad) {
    echo json_encode(["status" => "error", "message" => "Actividad no encontrada"]);
    exit();
}

// Validar tiempo límite de cancelación (24h antes)
$fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);
if ($fechaEvento - time() <= 86400) {
    echo json_encode([
        "status" => "error",
        "message" => "No puedes cancelar. Faltan menos de 24 horas."
    ]);
    exit();
}

// Eliminar inscripción usando strings
$resultado = $bd->inscripciones->deleteOne([
    "voluntario_id" => $rawVolId,
    "actividad_id" => $actividadId
]);

if ($resultado->getDeletedCount() > 0) {
    echo json_encode(["status" => "success", "message" => "Inscripción cancelada correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "No se encontró tu registro en la actividad."]);
}

exit();
?>
