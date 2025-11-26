<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\UTCDateTime;

if ($_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../index.php");
    exit();
}

$titulo = $_POST['titulo'];
$contenido = $_POST['contenido'];
$autor = $_SESSION['usuario']['nombre_org'];

$imagenRuta = "";

// Manejo imagen
if (!empty($_FILES['imagen']['name'])) {
    $carpeta = $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/uploads/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = time() . "_" . basename($_FILES['imagen']['name']);
    $rutaCompleta = $carpeta . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        $imagenRuta = "uploads/" . $nombreArchivo;
    }
}

// Guardar en MongoDB
$documento = [
    "titulo" => $titulo,
    "contenido" => $contenido,
    "imagen" => $imagenRuta,
    "autor" => $autor,
    "fecha" => new UTCDateTime()
];

$bd->blog->insertOne($documento);

header("Location: ../crear_blog.php?creado=1");
exit;
