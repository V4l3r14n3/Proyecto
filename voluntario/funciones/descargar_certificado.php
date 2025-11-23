<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\BSON\ObjectId;
use Dompdf\Dompdf;

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    exit("Acceso denegado");
}

$certificadoId = $_GET['id'];
$certificado = $bd->certificados->findOne(['_id' => new ObjectId($certificadoId)]);

if (!$certificado) {
    exit("Certificado no encontrado");
}

// Crear HTML del PDF
$html = "
<style>
    body { font-family: Arial, sans-serif; text-align: center; }
    .certificado { border: 3px solid #3498db; padding: 30px; border-radius: 15px; width: 90%; margin: auto; }
    h1 { color: #2c3e50; text-transform: uppercase; }
    h2 { color: #e74c3c; }
    h3 { color: #3498db; }
</style>

<div class='certificado'>
    <h1>CERTIFICADO DE VOLUNTARIADO</h1>
    <p>Se certifica que:</p>
    <h2>{$certificado['nombre_voluntario']}</h2>
    <p>Participó como voluntario en:</p>
    <h3>\"{$certificado['titulo_actividad']}\"</h3>
    <br>
    <p><strong>Organización:</strong> {$certificado['nombre_organizacion']}</p>
    <p><strong>Fecha:</strong> {$certificado['fecha_actividad']}</p>
    <p><strong>Horas:</strong> {$certificado['horas_voluntariado']} horas</p>
    <p><strong>Código:</strong> {$certificado['codigo_certificado']}</p>
    <br><br>
    <em>Emitido el: {$certificado['fecha_emision']}</em>
</div>
";

// Instanciar dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Puede ser portrait o landscape
$dompdf->render();

// Descargar PDF
$dompdf->stream("certificado_voluntariado.pdf", ["Attachment" => true]);
