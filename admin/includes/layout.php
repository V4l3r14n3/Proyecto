<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/rutas.php";

// Evitar acceso no autorizado
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <meta charset="UTF-8">
    <title>Panel Voluntario</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body> <!-- NECESARIO -->

    <div class="sidebar">
        <h2>
            <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? "Voluntario") ?>
        </h2>
/*
        <nav>
            <a href="<?= VOL_URL ?>index.php">🏠 Inicio</a>
            <a href="<?= VOL_URL ?>perfil.php">👤 Mi Perfil</a>
            <a href="<?= VOL_URL ?>voluntariados.php">🌱 Buscar voluntariados</a>
            <a href="<?= VOL_URL ?>mis_postulaciones.php">📌 Mis postulaciones</a>
            <a href="<?= VOL_URL ?>foro.php">📢 Foro</a>
            <a href="<?= VOL_URL ?>blog.php">📣 Blog</a>
        </nav>
*/
        <div class="theme-toggle">
            <span class="label">Modo oscuro</span>
            <label class="switch">
                <input type="checkbox" id="darkToggle">
                <span class="slider">
                    <span class="icon sun">☀️</span>
                    <span class="icon moon">🌙</span>
                </span>
            </label>
        </div>

        <a href="<?= BASE_URL ?>includes/logout.php" class="logout">🚪 Cerrar sesión</a>
    </div>

    <div class="main-content">