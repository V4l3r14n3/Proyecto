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
    
    // Si llegamos aquí, mostrar el certificado
?>
<div class="certificado-completo">
    <!-- Tu HTML del certificado aquí -->
    <div class="certificado-header" style="text-align: center; border-bottom: 3px solid #3498db; padding-bottom: 1rem;">
        <h1 style="color: #2c3e50; margin: 0;">CERTIFICADO DE VOLUNTARIADO</h1>
        <p style="color: #7f8c8d; margin: 0.5rem 0;">Se otorga el presente certificado a:</p>
    </div>
    
    <div class="certificado-body" style="text-align: center; padding: 2rem 0;">
        <h2 style="color: #e74c3c; margin: 1rem 0;"><?= htmlspecialchars($certificado['nombre_voluntario']) ?></h2>
        <p>Por su participación como voluntario en la actividad:</p>
        <h3 style="color: #3498db;">"<?= htmlspecialchars($certificado['titulo_actividad']) ?>"</h3>
        
        <div style="margin: 2rem 0;">
            <p><strong>Organización:</strong> <?= htmlspecialchars($certificado['nombre_organizacion']) ?></p>
            <p><strong>Fecha de la actividad:</strong> <?= htmlspecialchars($certificado['fecha_actividad']) ?></p>
            <p><strong>Horas de voluntariado:</strong> <?= $certificado['horas_voluntariado'] ?> horas</p>
            <p><strong>Código del certificado:</strong> <?= $certificado['codigo_certificado'] ?></p>
        </div>
        
        <p style="font-style: italic;">En reconocimiento a su valiosa contribución y compromiso con la comunidad.</p>
    </div>
    
    <div class="certificado-footer" style="text-align: center; border-top: 2px solid #3498db; padding-top: 1rem;">
        <p>Fecha de emisión: <?= htmlspecialchars($certificado['fecha_emision']) ?></p>
    </div>
</div>
<?php
} catch (Exception $e) {
    error_log("Error al buscar certificado: " . $e->getMessage());
    exit('Error al procesar el certificado');
}
?>