<?php
require_once "../includes/conexion.php";

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$bd->usuarios->updateOne(
    ['_id' => $id],
    ['$set' => ['estado' => 'aprobado']]
);

echo "<script>alert('Organización aprobada'); window.location='index.php';</script>";
