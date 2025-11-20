<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

$id = $_GET['id'] ?? null;

if ($id) {
    $bd->actividades->deleteOne(['_id' => new ObjectId($id)]);
    echo json_encode(["status" => "success", "mensaje" => "Voluntariado eliminado"]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "ID no encontrado"]);
}
