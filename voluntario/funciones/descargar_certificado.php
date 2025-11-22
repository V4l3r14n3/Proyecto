<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    exit('Acceso denegado');
}

$certificadoId = $_GET['id'];
$certificado = $bd->certificados->findOne(['_id' => new ObjectId($certificadoId)]);

if (!$certificado || $certificado['voluntario_id'] !== $_SESSION['usuario']['_id']['$oid']) {
    exit('Certificado no encontrado');
}

// Generar PDF (necesitarías una librería como TCPDF o Dompdf)
// Por ahora redirigimos a la vista para imprimir
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - <?= htmlspecialchars($certificado['titulo_actividad']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .certificado { border: 3px solid #3498db; padding: 2rem; text-align: center; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom: 1rem;">🖨️ Imprimir Certificado</button>
    
    <div class="certificado">
        <h1>CERTIFICADO DE VOLUNTARIADO</h1>
        <p>Se otorga el presente certificado a:</p>
        <h2><?= htmlspecialchars($certificado['nombre_voluntario']) ?></h2>
        <p>Por su participación como voluntario en:</p>
        <h3>"<?= htmlspecialchars($certificado['titulo_actividad']) ?>"</h3>
        <p><strong>Organización:</strong> <?= htmlspecialchars($certificado['nombre_organizacion']) ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($certificado['fecha_actividad']) ?></p>
        <p><strong>Horas:</strong> <?= $certificado['horas_voluntariado'] ?> horas</p>
        <p><strong>Código:</strong> <?= $certificado['codigo_certificado'] ?></p>
        <p><em>En reconocimiento a su valiosa contribución</em></p>
        <p>Emitido el: <?= htmlspecialchars($certificado['fecha_emision']) ?></p>
    </div>
</body>
</html>