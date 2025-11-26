<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if ($_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../index.php");
    exit();
}

if (!isset($_POST['id'])) {
    header("Location: ../ver_blog.php");
    exit();
}

$id = new ObjectId($_POST['id']);
$titulo = $_POST['titulo'];
$contenido = $_POST['contenido'];

$updateData = [
    "titulo" => $titulo,
    "contenido" => $contenido
];

// Si hay imagen nueva
if (!empty($_FILES['imagen']['name'])) {

    $carpeta = $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/uploads/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = time() . "_" . basename($_FILES['imagen']['name']);
    $rutaCompleta = $carpeta . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        $updateData["imagen"] = "uploads/" . $nombreArchivo;
    }
}

// Guardar cambios
$bd->blog->updateOne(["_id" => $id], ['$set' => $updateData]);

// REDIRECCIÓN silenciosa con indicador
header("Location: ../ver_blog.php?editado=1");
exit();
