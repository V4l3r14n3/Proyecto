<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

session_start();

$mensaje = trim($_POST['mensaje'] ?? "");
$receptor = $_POST['receptor'] ?? null;
$remitente = $_SESSION['usuario']['_id'] ?? null;

if (!$mensaje || !$receptor || !$remitente) {
    die("Datos incompletos.");
}

$bd->mensajes->insertOne([
    "remitente_id" => new ObjectId((string)$remitente),
    "receptor_id" => new ObjectId((string)$receptor),
    "mensaje" => $mensaje,
    "fecha" => new UTCDateTime(time() * 1000)
]);

echo "<script>history.back();</script>";
?>
