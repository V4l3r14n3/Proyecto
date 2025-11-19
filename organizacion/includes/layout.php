<?php
session_start();

// Evita acceso sin iniciar sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    header("Location: ../pages/login.php");
    exit();
}

include '../../includes/conexion.php';
?>

<link rel="stylesheet" href="../../css/panel.css">

<div class="sidebar">
    <h2><?= htmlspecialchars($_SESSION['usuario']['nombre_org'] ?? 'Organización') ?></h2>
    <a href="../perfil.php">Perfil</a>
    <a href="../crear_voluntariado.php">Crear Voluntariado</a>
    <a href="../voluntarios.php">Voluntarios Inscritos</a>
    <a href="../mensajes.php">Mensajes</a>
    <a href="../../includes/logout.php">Cerrar sesión</a>
</div>

<div class="main-content">
