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

// Forzar descarga como PDF (el usuario puede elegir "Guardar como PDF" en imprimir)
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - <?= htmlspecialchars($certificado['titulo_actividad']) ?></title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0;
            padding: 2rem;
            background: white;
        }
        .certificado { 
            border: 3px solid #3498db; 
            padding: 3rem; 
            text-align: center; 
            max-width: 800px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        h2 {
            color: #e74c3c;
            margin: 1.5rem 0;
        }
        h3 {
            color: #3498db;
            margin: 1rem 0;
        }
        .codigo {
            background: #34495e;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-family: monospace;
            display: inline-block;
            margin: 10px 0;
        }
        @media print {
            body { 
                margin: 0;
                padding: 0;
            }
            .no-print { 
                display: none; 
            }
            .certificado {
                border: none;
                padding: 2rem;
                box-shadow: none;
            }
        }
    </style>
    <script>
        // Auto-imprimir al cargar la página
        window.onload = function() {
            window.print();
            
            // Opcional: cerrar ventana después de imprimir (en algunos navegadores)
            setTimeout(function() {
                // window.close(); // Descomenta si quieres que se cierre automáticamente
            }, 1000);
        };
    </script>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 2rem;">
        <button onclick="window.print()" style="
            background: #00724f;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin: 10px;
        ">🖨️ Imprimir Certificado</button>
        
        <button onclick="window.close()" style="
            background: #95a5a6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin: 10px;
        ">❌ Cerrar Ventana</button>
        
        <p style="color: #7f8c8d; margin-top: 1rem;">
            <strong>Instrucciones:</strong> En el diálogo de impresión, selecciona "Guardar como PDF" como destino
        </p>
    </div>
    
    <div class="certificado">
        <h1>🎗️ CERTIFICADO DE VOLUNTARIADO</h1>
        <p style="color: #7f8c8d; font-size: 1.1rem;">Se otorga el presente certificado a:</p>
        
        <h2><?= htmlspecialchars($certificado['nombre_voluntario']) ?></h2>
        
        <p style="font-size: 1.1rem;">Por su participación como voluntario en la actividad:</p>
        <h3>"<?= htmlspecialchars($certificado['titulo_actividad']) ?>"</h3>
        
        <div style="margin: 2rem 0; text-align: left; display: inline-block;">
            <p><strong>🏢 Organización:</strong> <?= htmlspecialchars($certificado['nombre_organizacion']) ?></p>
            <p><strong>📅 Fecha de la actividad:</strong> <?= htmlspecialchars($certificado['fecha_actividad']) ?></p>
            <p><strong>⏱️ Horas de voluntariado:</strong> <?= $certificado['horas_voluntariado'] ?> horas</p>
            <p><strong>🔖 Código del certificado:</strong></p>
            <div class="codigo"><?= $certificado['codigo_certificado'] ?></div>
        </div>
        
        <p style="font-style: italic; font-size: 1.1rem; margin: 2rem 0;">
            En reconocimiento a su valiosa contribución y compromiso con la comunidad.
        </p>
        
        <div style="border-top: 2px solid #3498db; padding-top: 1rem; margin-top: 2rem;">
            <p><strong>Fecha de emisión:</strong> <?= htmlspecialchars($certificado['fecha_emision']) ?></p>
        </div>
    </div>
</body>
</html>