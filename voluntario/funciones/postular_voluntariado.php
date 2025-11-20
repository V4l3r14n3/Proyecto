<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== "voluntario") {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

$idVoluntario = $_SESSION['usuario']['_id'];
$idActividad = new ObjectId($_GET['id']);

// Verificar si ya existe inscripción
$existe = $bd->inscripciones->findOne([
    "id_voluntario" => $idVoluntario,
    "id_actividad" => $idActividad
]);

if ($existe) {
    echo json_encode(["status" => "error", "mensaje" => "Ya estás inscrito en este voluntariado."]);
    exit();
}

// Verificar tiempo mínimo (24 horas)
$actividad = $bd->actividades->findOne(["_id" => $idActividad]);
$fechaEvento = strtotime($actividad['fecha_hora'] ?? $actividad['fecha']);

if ($fechaEvento - time() < 86400) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "No puedes inscribirte, falta menos de 24 horas."
    ]);
    exit();
}

// Guardar inscripción
$bd->inscripciones->insertOne([
    "id_voluntario" => $idVoluntario,
    "id_actividad" => $idActividad,
    "fecha_registro" => date("Y-m-d H:i")
]);

echo json_encode(["status" => "success", "mensaje" => "Postulación registrada con éxito."]);
exit();
