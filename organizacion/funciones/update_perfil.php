<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/rutas.php";
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Obtener ID correctamente (podría ser string o arreglo con "$oid")
$idRaw = $_SESSION['usuario']['_id'];
$id = new ObjectId(is_array($idRaw) ? $idRaw['$oid'] : $idRaw);

// Datos base a actualizar
$actualizar = [
    'nombre' => $_POST['nombre'],
    'nombre_org' => $_POST['nombre_org']
];

// Contraseña opcional
if (!empty($_POST['password_nueva'])) {
    $actualizar['password'] = $_POST['password_nueva'];
}

// Guardar cambios en MongoDB
$bd->usuarios->updateOne(
    ['_id' => $id],
    ['$set' => $actualizar]
);

// Actualizar datos en sesión
$_SESSION['usuario']['nombre'] = $_POST['nombre'];
$_SESSION['usuario']['nombre_org'] = $_POST['nombre_org'];

// Redirección con indicador de éxito
header("Location: ../perfil.php?status=ok");
exit;
?>
