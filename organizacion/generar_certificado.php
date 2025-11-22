<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    die('Acceso denegado. Debes estar logueado como organización.');
}

echo "<h2>🔧 Generar Certificado Manualmente</h2>";

// Función para formatear fechas
function formatearFecha($fechaISO) {
    if (empty($fechaISO)) return 'Fecha no especificada';
    try {
        if (strpos($fechaISO, 'T') !== false) {
            $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $fechaISO);
            if ($fecha) {
                return $fecha->format('d/m/Y \a \l\a\s H:i');
            }
        }
        $fecha = new DateTime($fechaISO);
        return $fecha->format('d/m/Y \a \l\a\s H:i');
    } catch (Exception $e) {
        return $fechaISO;
    }
}

// ID de la inscripción específica
$inscripcionId = '691f749c76f85aa3950d9d9d';

try {
    // 1. Buscar la inscripción
    $inscripcion = $bd->inscripciones->findOne(['_id' => new ObjectId($inscripcionId)]);
    
    if (!$inscripcion) {
        die("❌ Inscripción no encontrada con ID: $inscripcionId");
    }
    
    echo "✅ <strong>Inscripción encontrada:</strong><br>";
    echo "ID: " . $inscripcion['_id'] . "<br>";
    echo "Voluntario ID: " . $inscripcion['voluntario_id'] . "<br>";
    echo "Actividad ID: " . $inscripcion['actividad_id'] . "<br>";
    echo "Asistió: " . ($inscripcion['asistio'] ? 'SÍ' : 'NO') . "<br><br>";
    
    // 2. Buscar el voluntario
    $voluntario = $bd->usuarios->findOne(['_id' => new ObjectId($inscripcion['voluntario_id'])]);
    
    if (!$voluntario) {
        die("❌ Voluntario no encontrado con ID: " . $inscripcion['voluntario_id']);
    }
    
    echo "✅ <strong>Voluntario encontrado:</strong><br>";
    echo "Nombre: " . $voluntario['nombre'] . "<br>";
    echo "Email: " . $voluntario['email'] . "<br><br>";
    
    // 3. Buscar la actividad
    $actividad = $bd->actividades->findOne(['_id' => new ObjectId($inscripcion['actividad_id'])]);
    
    if (!$actividad) {
        die("❌ Actividad no encontrada con ID: " . $inscripcion['actividad_id']);
    }
    
    echo "✅ <strong>Actividad encontrada:</strong><br>";
    echo "Título: " . $actividad['titulo'] . "<br>";
    echo "Organización: " . $actividad['organizacion'] . "<br>";
    echo "Fecha: " . $actividad['fecha_hora'] . "<br><br>";
    
    // 4. Verificar si ya existe certificado
    $certificadoExistente = $bd->certificados->findOne([
        'voluntario_id' => $inscripcion['voluntario_id'],
        'actividad_id' => $inscripcion['actividad_id']
    ]);
    
    if ($certificadoExistente) {
        echo "⚠️ <strong>Certificado ya existe:</strong><br>";
        echo "Código: " . $certificadoExistente['codigo_certificado'] . "<br>";
        echo "Fecha emisión: " . $certificadoExistente['fecha_emision'] . "<br>";
    } else {
        // 5. Crear nuevo certificado
        $certificado = [
            'voluntario_id' => $inscripcion['voluntario_id'],
            'actividad_id' => $inscripcion['actividad_id'],
            'organizacion_id' => $_SESSION['usuario']['_id']['$oid'],
            'titulo_actividad' => $actividad['titulo'],
            'nombre_voluntario' => $voluntario['nombre'],
            'nombre_organizacion' => $_SESSION['usuario']['nombre_org'],
            'fecha_actividad' => formatearFecha($actividad['fecha_hora']),
            'fecha_emision' => date('Y-m-d H:i:s'),
            'codigo_certificado' => uniqid('CERT_'),
            'horas_voluntariado' => $actividad['duracion_horas'] ?? 4
        ];
        
        $result = $bd->certificados->insertOne($certificado);
        
        if ($result->getInsertedCount() === 1) {
            echo "🎉 <strong>✅ CERTIFICADO GENERADO EXITOSAMENTE:</strong><br>";
            echo "Código: " . $certificado['codigo_certificado'] . "<br>";
            echo "Fecha emisión: " . $certificado['fecha_emision'] . "<br>";
            echo "Horas: " . $certificado['horas_voluntariado'] . "<br>";
        } else {
            echo "❌ Error al insertar el certificado en la base de datos";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
}

echo "<br><br><a href='voluntarios.php' style='background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>← Volver a Voluntarios Inscritos</a>";
?>