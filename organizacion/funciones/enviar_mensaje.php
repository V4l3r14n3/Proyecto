<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

session_start();

$mensaje = trim($_POST['mensaje'] ?? "");
$receptor = $_POST['receptor'] ?? null;
$remitenteRaw = $_SESSION['usuario']['_id'] ?? null;

// Normalizar ID
$remitente = is_array($remitenteRaw) ? $remitenteRaw['$oid'] : $remitenteRaw;

// Validación
if (!$mensaje || !$receptor || !$remitente) {
    $_SESSION['alerta'] = [
        "tipo" => "error",
        "titulo" => "Error",
        "texto" => "Debe seleccionar un usuario y escribir un mensaje."
    ];
    header("Location: ../mensajes.php");
    exit();
}

// Guardar mensaje
$bd->mensajes->insertOne([
    "remitente_id" => new ObjectId((string)$remitente),
    "receptor_id" => new ObjectId((string)$receptor),
    "mensaje" => $mensaje,
    "fecha" => new UTCDateTime(time() * 1000)
]);

// Mensaje de éxito
$_SESSION['alerta'] = [
    "tipo" => "success",
    "titulo" => "Mensaje enviado",
    "texto" => "El mensaje fue enviado correctamente."
];

header("Location: ../mensajes.php");
exit();
?>
