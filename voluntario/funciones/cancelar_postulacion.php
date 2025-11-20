<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Validación
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID inválido"]);
    exit();
}

// Normalizar ID voluntario
$rawId = $_SESSION['usuario']['_id'];
if (is_array($rawId) && isset($rawId['$oid'])) {
    $rawId = $rawId['$oid'];
}
$voluntarioId = new ObjectId($rawId);
$actividadId = new ObjectId($_GET['id']);

// Validar fecha (no permitir cancelar si faltan < 24h)
$actividad = $bd->actividades->findOne(["_id" => $actividadId]);
$fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);

if ($fechaEvento - time() <= 86400) {
    echo json_encode([
        "status" => "error",
        "message" => "No puedes cancelar. Faltan menos de 24 horas."
    ]);
    exit();
}

// Eliminar postulación
$bd->inscripciones->deleteOne([
    "voluntario_id" => $voluntarioId,
    "actividad_id" => (string)$actividadId
]);

echo json_encode([
    "status" => "success",
    "message" => "Has cancelado tu participación."
]);
exit();
?>
