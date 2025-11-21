<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

$de = $_SESSION['usuario']['rol'] === "organizacion"
    ? $_SESSION['usuario']['nombre_org']
    : (string)$_SESSION['usuario']['_id'];

$rol_emisor = $_SESSION['usuario']['rol'];
$para = $_POST['para'];
$mensaje = trim($_POST['mensaje']);

if (empty($para) || empty($mensaje)) {
    echo json_encode(["status" => "error", "message" => "Debe llenar todos los campos"]);
    exit();
}

$bd->mensajes->insertOne([
    "de" => $de,
    "para" => $para,
    "rol_emisor" => $rol_emisor,
    "mensaje" => $mensaje,
    "fecha" => date("Y-m-d H:i:s"),
    "estado" => "no_leido"
]);

header("Location: " . ($_SESSION['usuario']['rol'] == "organizacion" 
    ? "../mensajes_org.php" : "../mensajes_voluntario.php"));
exit();
