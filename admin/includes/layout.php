<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/rutas.php";

// Bloquear acceso si no es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Administrador') {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>

    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="sidebar">
        <h2>👑 Admin</h2>

        <p style="font-size:14px; margin-top:-10px; color:#ccc;">
            <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
        </p>

        <nav>
            <a href="<?= ADMIN_URL ?>index.php">🏠 Inicio</a>
            <a href="<?= ADMIN_URL ?>verificar_organizaciones.php">📋 Verificar organizaciones</a>
        </nav>

        <a href="<?= BASE_URL ?>includes/logout.php" class="logout">🚪 Cerrar sesión</a>
    </div>

    <div class="main-content">
