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
$rawId = $_SESSION['usuario']['_id'];
if (is_array($rawId) && isset($rawId['$oid'])) {
    $rawId = $rawId['$oid'];
}
$voluntarioId = new ObjectId($rawId);

// ID del evento
$actividadIdString = $_GET['id'];
$actividadIdObject = new ObjectId($actividadIdString);

// --- VALIDAR FECHA ---
$actividad = $bd->actividades->findOne(["_id" => $actividadIdObject]);

if (!$actividad) {
    echo json_encode(["status" => "error", "message" => "Actividad no encontrada"]);
    exit();
}

$fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);

if ($fechaEvento - time() <= 86400) {
    echo json_encode([
        "status" => "error",
        "message" => "No puedes cancelar. Faltan menos de 24 horas."
    ]);
    exit();
}

// --- BORRAR POSTULACIÓN (compatibilidad string/ObjectId) ---
$resultado = $bd->inscripciones->deleteOne([
    "voluntario_id" => $voluntarioId,
    "$or" => [
        ["actividad_id" => $actividadIdString],
        ["actividad_id" => $actividadIdObject]
    ]
]);

if ($resultado->getDeletedCount() > 0) {
    echo json_encode(["status" => "success", "message" => "Tu inscripción ha sido cancelada correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "No se encontró la inscripción para eliminar."]);
}

exit();
?>
