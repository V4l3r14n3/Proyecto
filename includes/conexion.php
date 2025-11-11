<?php
require_once __DIR__ . '/../vendor/autoload.php';  // carga el driver de MongoDB

use MongoDB\Client;

try {
    // Conexión local (puedes cambiar el nombre de la base)
    $cliente = new Client("mongodb://localhost:27017/");
    $bd = $cliente->plataforma_voluntariado;

    // Colecciones que usaremos (puedes agregar más)
    $usuarios = $bd->usuarios;
    $actividades = $bd->actividades;

} catch (Exception $e) {
    die("Error al conectar con MongoDB: " . $e->getMessage());
}
?>
