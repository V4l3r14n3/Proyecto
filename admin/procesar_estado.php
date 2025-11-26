<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

$id = $_POST['id'] ?? null;
$accion = $_POST['accion'] ?? null;
$motivo = $_POST['motivo'] ?? null;

if (!$id || !$accion) {
    echo json_encode(["titulo" => "Error", "mensaje" => "Solicitud inválida.", "tipo" => "error"]);
    exit;
}

$objectId = new ObjectId($id);

if ($accion === "aprobar") {
    $bd->usuarios->updateOne(
        ['_id' => $objectId],
        ['$set' => ['estado' => 'aprobado']]
    );
    echo json_encode([
        "titulo" => "Organización aprobada 🎉",
        "mensaje" => "Ahora puede iniciar sesión.",
        "tipo" => "success"
    ]);
}

elseif ($accion === "rechazar") {
    $bd->usuarios->updateOne(
        ['_id' => $objectId],
        ['$set' => ['estado' => 'rechazado', 'motivo_rechazo' => $motivo]]
    );
    echo json_encode([
        "titulo" => "Organización rechazada",
        "mensaje" => "Se guardó el motivo del rechazo.",
        "tipo" => "info"
    ]);
}
?>
