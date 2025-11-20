<?php
session_start();
include '../../includes/conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$fecha = $_POST['fecha']; // contiene fecha y hora
$ubicacion = $_POST['ubicacion'];
$ciudad = $_POST['ciudad'] ?? '';

$fechaEvento = strtotime($fecha);
$mañana = strtotime("+1 day");

if ($fechaEvento <= $mañana) {
    echo json_encode(["status" => "error", "mensaje" => "La fecha debe ser mínimo dentro de 2 días."]);
    exit();
}

$bd->actividades->insertOne([
    "titulo" => $titulo,
    "descripcion" => $descripcion,
    "fecha_hora" => $fecha,
    "ciudad" => $ciudad,
    "ubicacion" => $ubicacion,
    "organizacion" => $_SESSION['usuario']['nombre_org'],
    "creado" => date("Y-m-d H:i:s")
]);

echo json_encode(["status" => "success", "mensaje" => "Voluntariado creado correctamente"]);
