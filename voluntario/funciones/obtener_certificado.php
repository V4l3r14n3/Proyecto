<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    exit('Acceso denegado');
}

$certificadoId = $_GET['id'];
error_log("Buscando certificado con ID: " . $certificadoId);

try {
    $certificado = $bd->certificados->findOne(['_id' => new ObjectId($certificadoId)]);
    
    if (!$certificado) {
        error_log("Certificado no encontrado en BD con ID: " . $certificadoId);
        exit('Certificado no encontrado en base de datos');
    }
    
    error_log("Certificado encontrado - Voluntario ID en cert: " . $certificado['voluntario_id']);
    error_log("Sesión User ID: " . $_SESSION['usuario']['_id']['$oid']);
    
    // Comparación más flexible
    $idCertificado = (string)$certificado['voluntario_id'];
    $idSesion = (string)$_SESSION['usuario']['_id']['$oid'];
    
    if ($idCertificado !== $idSesion) {
        error_log("IDs NO coinciden: $idCertificado vs $idSesion");
        exit('Certificado no pertenece a este usuario');
    }
?>
<style>
    * {
        box-sizing: border-box;
    }

    body {
        background: #f4f7fb;
        padding: 30px;
    }

    .certificado {
        width: 85%;
        margin: 40px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 16px;
        border: 3px solid #3498db;
        box-shadow: 0 0 25px rgba(0,0,0,0.1);
        font-family: "Segoe UI", sans-serif;
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }

    .cert-header {
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 2.5px solid #3498db;
    }

    .cert-header h1 {
        margin: 0;
        font-size: 30px;
        letter-spacing: 2px;
        color: #2c3e50;
        font-weight: bold;
        text-transform: uppercase;
    }

    .cert-subtitle {
        margin: 8px 0 20px;
        color: #555;
        font-size: 17px;
        font-weight: 500;
    }

    .cert-body {
        text-align: center;
        padding: 30px 0;
    }

    .cert-body h2 {
        color: #e74c3c;
        font-size: 30px;
        font-weight: 700;
        margin: 15px 0;
        text-transform: capitalize;
    }

    .cert-body h3 {
        margin-top: 10px;
        font-size: 22px;
        color: #3498db;
    }

    .details {
        margin: 25px auto;
        width: 70%;
        font-size: 17px;
        color: #333;
        text-align: left;
        line-height: 1.8;
    }

    .details strong {
        color: #2c3e50;
    }

    .cert-footer {
        margin-top: 30px;
        text-align: center;
        padding-top: 15px;
        border-top: 2px solid #3498db;
        font-size: 15px;
        color: #555;
    }

    .btn-print {
        margin-top: 20px;
        display: inline-block;
        background: #3498db;
        padding: 10px 20px;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        transition: .3s;
        font-size: 15px;
        cursor: pointer;
    }

    .btn-print:hover {
        background: #2177b5;
    }

    @media print {
        .btn-print { display: none; }
        body { background: white; }
        .certificado { box-shadow: none; border: 2px solid black; }
    }
</style>

<div class="certificado">
    <div class="cert-header">
        <h1>CERTIFICADO DE VOLUNTARIADO</h1>
        <p class="cert-subtitle">Se otorga el presente reconocimiento a:</p>
    </div>

    <div class="cert-body">
        <h2><?= htmlspecialchars($certificado['nombre_voluntario']) ?></h2>
        <p style="font-size: 17px; color:#333;">Por su participación destacada en la actividad:</p>
        <h3>"<?= htmlspecialchars($certificado['titulo_actividad']) ?>"</h3>

        <div class="details">
            <p><strong>Organización:</strong> <?= htmlspecialchars($certificado['nombre_organizacion']) ?></p>
            <p><strong>Fecha de la actividad:</strong> <?= htmlspecialchars($certificado['fecha_actividad']) ?></p>
            <p><strong>Horas de voluntariado:</strong> <?= $certificado['horas_voluntariado'] ?> horas</p>
            <p><strong>Código del certificado:</strong> <?= $certificado['codigo_certificado'] ?></p>
        </div>

        <p style="font-style: italic; color:#555; font-size: 16px;">
            Este certificado se expide en reconocimiento a su compromiso, apoyo y dedicación con la comunidad.
        </p>
    </div>

    <div class="cert-footer">
        Fecha de emisión: <?= htmlspecialchars($certificado['fecha_emision']) ?>
    </div>

    <center><button class="btn-print" onclick="window.print()">📄 Imprimir Certificado</button></center>
</div>

<?php
} catch (Exception $e) {
    error_log("Error al buscar certificado: " . $e->getMessage());
    exit('Error al procesar el certificado');
}
?>
