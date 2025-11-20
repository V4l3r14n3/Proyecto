<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Validación
if (empty($_POST['id'])) {
    echo json_encode(["status" => "error", "mensaje" => "ID inválido"]);
    exit();
}

$id = new ObjectId($_POST['id']);
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$ciudad = $_POST['ciudad'];
$fecha_hora = $_POST['fecha_hora'];

// Validar fecha futura
if (strtotime($fecha_hora) <= strtotime('today')) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "La fecha debe ser posterior a hoy."
    ]);
    exit();
}

// Actualizar
$bd->actividades->updateOne(
    ['_id' => $id],
    ['$set' => [
        "titulo" => $titulo,
        "descripcion" => $descripcion,
        "ciudad" => $ciudad,
        "fecha_hora" => $fecha_hora
    ]]
);

echo json_encode(["status" => "success", "mensaje" => "Voluntariado actualizado correctamente"]);
exit;
?>
