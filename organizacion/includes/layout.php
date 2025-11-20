<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/rutas.php";

// Evitar acceso no autorizado
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="sidebar">
    <h2>
        <?= htmlspecialchars(
            !empty($_SESSION['usuario']['nombre_org'])
                ? $_SESSION['usuario']['nombre_org']
                : $_SESSION['usuario']['nombre']
        ) ?>
    </h2>



    <a href="<?= ORG_URL ?>index.php">Inicio</a>
    <a href="<?= ORG_URL ?>perfil.php">Perfil</a>
    <a href="<?= ORG_URL ?>crear_voluntariado.php">Crear Voluntariado</a>
    <a href="<?= ORG_URL ?>mis_voluntariados.php">Mis voluntariados</a>
    <a href="<?= ORG_URL ?>voluntarios.php">Voluntarios</a>
    <a href="<?= ORG_URL ?>mensajes.php">Mensajes</a>
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
    <a href="<?= BASE_URL ?>includes/logout.php">Cerrar sesión</a>
</div>

<div class="main-content">