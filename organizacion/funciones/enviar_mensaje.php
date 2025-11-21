<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

session_start();

$mensaje = trim($_POST['mensaje'] ?? "");
$receptor = $_POST['receptor'] ?? null;
$rawId = $_SESSION['usuario']['_id'] ?? null;

// Normalizar ID
$remitente = is_array($rawId) ? $rawId['$oid'] : $rawId;

if (!$mensaje || !$receptor || !$remitente) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({ icon: 'error', title: 'Error', text: 'Debe escribir un mensaje.' })
            .then(() => history.back());
    </script>";
    exit();
}

$bd->mensajes->insertOne([
    "remitente_id" => new ObjectId($remitente),
    "receptor_id" => new ObjectId($receptor),
    "mensaje" => $mensaje,
    "fecha" => new UTCDateTime(time() * 1000)
]);

echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Mensaje enviado',
        timer: 1500,
        showConfirmButton: false
    }).then(() => location.reload());
</script>";
exit();
?>
