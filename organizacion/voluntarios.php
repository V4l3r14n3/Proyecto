<?php 
session_start();
include '../includes/conexion.php';

$voluntarios = $bd->inscripciones->find([
    "organizacion" => $_SESSION['usuario']['nombre_org']
]);
?>

<link rel="stylesheet" href="../css/panel.css">

<div class="main-content">
    <h2>Voluntarios inscritos 👥</h2>

    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Evento</th>
        </tr>

        <?php foreach ($voluntarios as $v): ?>
        <tr>
            <td><?= $v['nombre'] ?></td>
            <td><?= $v['email'] ?></td>
            <td><?= $v['actividad'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
