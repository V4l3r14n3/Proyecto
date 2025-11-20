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

// Normalizar voluntario ID
$rawVolId = $_SESSION['usuario']['_id'];
if (is_array($rawVolId) && isset($rawVolId['$oid'])) {
    $rawVolId = $rawVolId['$oid'];
}
$voluntarioIdObj = new ObjectId($rawVolId);

// Normalizar ID actividad
$actividadId = $_GET['id'];
$actividadIdObj = new ObjectId($actividadId);

// Validar fecha antes de borrar
$actividad = $bd->actividades->findOne(["_id" => $actividadIdObj]);

if (!$actividad) {
    echo json_encode(["status" => "error", "message" => "Actividad no encontrada"]);
    exit();
}

// Validar límite de 24 horas
$fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);
if ($fechaEvento - time() <= 86400) {
    echo json_encode(["status" => "error", "message" => "No puedes cancelar. Faltan menos de 24 horas."]);
    exit();
}

// 🔥 Eliminar la inscripción aceptando string u ObjectId
$resultado = $bd->inscripciones->deleteOne([
    '$and' => [
        [
            '$or' => [
                ["voluntario_id" => $rawVolId],
                ["voluntario_id" => $voluntarioIdObj],
            ]
        ],
        [
            '$or' => [
                ["actividad_id" => $actividadId],
                ["actividad_id" => $actividadIdObj],
            ]
        ]
    ]
]);


if ($resultado->getDeletedCount() > 0) {
    echo json_encode(["status" => "success", "message" => "Tu inscripción ha sido cancelada correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "No se encontró tu registro en esta actividad."]);
}

exit();
?>
