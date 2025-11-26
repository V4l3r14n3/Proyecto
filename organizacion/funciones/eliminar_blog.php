<?php
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_GET['id'])) {
    header("Location: ../ver_blog.php");
    exit;
}

$id = new ObjectId($_GET['id']);
$bd->blog->deleteOne(["_id" => $id]);

header("Location: ../ver_blog.php?eliminado=1");
exit;
?>
