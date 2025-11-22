<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // Validar que los campos requeridos estén presentes
    if (empty($_POST['titulo']) || empty($_POST['mensaje'])) {
        $_SESSION['error'] = "Todos los campos son requeridos";
        header("Location: " . ($_SESSION['usuario']['rol'] === "organizacion" ? "../organizacion/foro.php" : "../voluntario/foro.php"));
        exit;
    }

    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);

    // Detectar quién está escribiendo
    if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === "organizacion") {
        
        $autor = "organizacion";
        // ID guardado en sesión para organización
        $id_organizacion = $_SESSION['usuario']['_id']['$oid'];
        $id_voluntario = null;

    } else {
        $autor = "voluntario";
        
        // Validar que se haya seleccionado una organización
        if (empty($_POST['id_organizacion'])) {
            $_SESSION['error'] = "Debes seleccionar una organización";
            header("Location: ../voluntario/foro.php");
            exit;
        }
        
        // USAR el id_organizacion del select
        $id_organizacion = $_POST['id_organizacion'];
        $id_voluntario = $_SESSION['usuario']['_id']['$oid'];
    }

    // Validar que el id_organizacion sea válido
    try {
        if (!empty($id_organizacion)) {
            $orgObjectId = new MongoDB\BSON\ObjectId($id_organizacion);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "ID de organización inválido";
        header("Location: " . ($_SESSION['usuario']['rol'] === "organizacion" ? "../organizacion/foro.php" : "../voluntario/foro.php"));
        exit;
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

    try {
        // Guardar en base de datos
        $result = $bd->foro->insertOne($foro);
        
        if ($result->getInsertedCount() === 1) {
            $_SESSION['success'] = "Mensaje publicado correctamente";
        } else {
            $_SESSION['error'] = "Error al publicar el mensaje";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }

    // Redirigir según el tipo de usuario
    if ($_SESSION['usuario']['rol'] === "organizacion") {
        header("Location: ../organizacion/foro.php");
    } else {
        header("Location: ../voluntario/foro.php");
    }

    exit;
} else {
    // Si no es POST, redirigir
    header("Location: ../index.php");
    exit;
}
?>