<?php
session_start();
include '../../includes/conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$fecha = $_POST['fecha'];

$bd->actividades->insertOne([
    "titulo" => $titulo,
    "descripcion" => $descripcion,
    "fecha" => $fecha,
    "organizacion" => $_SESSION['usuario']['nombre_org']
]);

echo json_encode(["status" => "success", "mensaje" => "Voluntariado creado correctamente"]);
