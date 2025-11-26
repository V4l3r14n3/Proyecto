<?php
require_once "../includes/conexion.php";

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$bd->usuarios->deleteOne(['_id' => $id]);

echo "<script>alert('Organización rechazada'); window.location='index.php';</script>";
