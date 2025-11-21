<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

session_start();

$mensaje = trim($_POST['mensaje'] ?? "");
$receptor = $_POST['receptor'] ?? null;
$remitenteRaw = $_SESSION['usuario']['_id'] ?? null;

// Normalizar el id del remitente
$remitente = is_array($remitenteRaw) ? $remitenteRaw['$oid'] : $remitenteRaw;

if (!$mensaje || !$receptor || !$remitente) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo enviar el mensaje. Faltan datos.'
        }).then(() => {
            history.back();
        });
    </script>";
    exit();
}

$bd->mensajes->insertOne([
    "remitente_id" => new ObjectId((string)$remitente),
    "receptor_id" => new ObjectId((string)$receptor),
    "mensaje" => $mensaje,
    "fecha" => new UTCDateTime(time() * 1000)
]);

echo "
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Mensaje enviado',
        text: 'Tu mensaje fue enviado correctamente.',
        timer: 1800,
        showConfirmButton: false
    }).then(() => {
        history.back();
    });
</script>";
exit();
?>
