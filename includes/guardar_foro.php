<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];

    // Identificar si quien escribe es voluntario u organización
    if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === "organizacion") {
        $autor = "organizacion";
        $id_organizacion = $_SESSION['usuario']['id'];
        $id_voluntario = null;
    } else {
        $autor = "voluntario";
        $id_organizacion = $_POST['id_organizacion']; 
        $id_voluntario = $_SESSION['usuario']['id'];
    }

    $foro = [
        "titulo" => $titulo,
        "mensaje" => $mensaje,
        "id_organizacion" => $id_organizacion,
        "id_voluntario" => $id_voluntario,
        "autor" => $autor,
        "fecha" => date("Y-m-d H:i:s")
    ];

    $bd->foro->insertOne($foro);

    header("Location: ../pages/foro.php");
    exit;
}
