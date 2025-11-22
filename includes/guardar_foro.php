<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];

    // Detectar quién está escribiendo
    if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === "organizacion") {
        
        $autor = "organizacion";
        // ID guardado en sesión para organización
        $id_organizacion = $_SESSION['usuario']['_id']['$oid'];
        $id_voluntario = null;

    } else {
    $autor = "voluntario";

    // Buscar organización asignada desde la BD
    $voluntarioBD = $bd->voluntarios->findOne([
        "_id" => new MongoDB\BSON\ObjectId($_SESSION['usuario']['_id']['$oid'])
    ]);

    $id_organizacion = $voluntarioBD['id_organizacion'] ?? null;

    $id_voluntario = $_SESSION['usuario']['_id']['$oid'];
}

    // Estructura del documento que se guardará en MongoDB
    $foro = [
        "titulo" => $titulo,
        "mensaje" => $mensaje,
        "id_organizacion" => $id_organizacion,
        "id_voluntario" => $id_voluntario,
        "autor" => $autor,
        "fecha" => date("Y-m-d H:i:s")
    ];

    // Guardar en base de datos
    $bd->foro->insertOne($foro);

    // Redirigir según el tipo de usuario
    if ($_SESSION['usuario']['rol'] === "organizacion") {
        header("Location: ../organizacion/foro.php");
    } else {
        header("Location: ../voluntario/foro.php");
    }

    exit;
}
