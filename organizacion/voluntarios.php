<?php 
include 'includes/layout.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    header("Location: ../pages/login.php");
    exit();
}

// Procesar asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_asistencia'])) {
    $inscripcionId = $_POST['inscripcion_id'];
    $asistio = $_POST['asistio'] === 'si';
    
    try {
        $bd->inscripciones->updateOne(
            ['_id' => new ObjectId($inscripcionId)],
            ['$set' => [
                'asistio' => $asistio,
                'fecha_asistencia' => date('Y-m-d H:i:s')
            ]]
        );
        
        // Si asistió, generar certificado
        if ($asistio) {
            generarCertificado($inscripcionId, $bd);
        }
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Éxito',
            'message' => 'Asistencia registrada correctamente'
        ];
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Error al registrar asistencia: ' . $e->getMessage()
        ];
    }
    
    header("Location: voluntarios_inscritos.php");
    exit();
}

// Función para generar certificado
function generarCertificado($inscripcionId, $bd) {
    $inscripcion = $bd->inscripciones->findOne(['_id' => new ObjectId($inscripcionId)]);
    
    if (!$inscripcion) return;
    
    $voluntario = $bd->usuarios->findOne(['_id' => new ObjectId($inscripcion['voluntario_id'])]);
    $actividad = $bd->actividades->findOne(['_id' => new ObjectId($inscripcion['actividad_id'])]);
    
    $certificado = [
        'voluntario_id' => $inscripcion['voluntario_id'],
        'actividad_id' => $inscripcion['actividad_id'],
        'organizacion_id' => $_SESSION['usuario']['_id']['$oid'],
        'titulo_actividad' => $actividad['titulo'],
        'nombre_voluntario' => $voluntario['nombre'],
        'nombre_organizacion' => $_SESSION['usuario']['nombre_org'],
        'fecha_actividad' => $actividad['fecha'],
        'fecha_emision' => date('Y-m-d H:i:s'),
        'codigo_certificado' => uniqid('CERT_'),
        'horas_voluntariado' => $actividad['duracion_horas'] ?? 4
    ];
    
    $bd->certificados->insertOne($certificado);
}

// Obtener actividades de esta organización para agrupar
$actividades = $bd->actividades->find([
    'organizacion' => $_SESSION['usuario']['nombre_org']
]);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voluntarios Inscritos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="main-content">
        <h2>Voluntarios inscritos 👥</h2>

        <?php if (isset($_SESSION['alert'])): ?>
            <script>
                Swal.fire({
                    icon: '<?= $_SESSION['alert']['type'] ?>',
                    title: '<?= $_SESSION['alert']['title'] ?>',
                    text: '<?= $_SESSION['alert']['message'] ?>'
                });
                <?php unset($_SESSION['alert']); ?>
            </script>
        <?php endif; ?>

        <?php foreach ($actividades as $actividad): ?>
        <div class="evento-grupo">
            <h3 class="titulo-evento">🎯 <?= htmlspecialchars($actividad['titulo']) ?> 
                <small>(<?= htmlspecialchars($actividad['fecha']) ?>)</small>
            </h3>
            
            <?php
            // Obtener inscripciones para esta actividad
            $inscripcionesEvento = $bd->inscripciones->find([
                'actividad_id' => $actividad['_id']->__toString()
            ]);
            ?>

            <table class="tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha Registro</th>
                    <th>Asistencia</th>
                    <th>Certificado</th>
                </tr>

                <?php foreach ($inscripcionesEvento as $i): 
                    $voluntario = $bd->usuarios->findOne([
                        "_id" => new ObjectId($i['voluntario_id'])
                    ]);
                    
                    if (!$voluntario) continue;
                    
                    $certificado = $bd->certificados->findOne([
                        'voluntario_id' => $i['voluntario_id'],
                        'actividad_id' => $i['actividad_id']
                    ]);
                ?>
                <tr>
                    <td><?= htmlspecialchars($voluntario['nombre']) ?></td>
                    <td><?= htmlspecialchars($voluntario['email']) ?></td>
                    <td><?= htmlspecialchars($i['fecha_registro']) ?></td>
                    <td>
                        <form method="POST" class="form-asistencia">
                            <input type="hidden" name="inscripcion_id" value="<?= $i['_id'] ?>">
                            <select name="asistio" onchange="this.form.submit()">
                                <option value="">Seleccionar</option>
                                <option value="si" <?= isset($i['asistio']) && $i['asistio'] ? 'selected' : '' ?>>✅ Asistió</option>
                                <option value="no" <?= isset($i['asistio']) && !$i['asistio'] ? 'selected' : '' ?>>❌ No asistió</option>
                            </select>
                            <input type="hidden" name="marcar_asistencia" value="1">
                        </form>
                    </td>
                    <td>
                        <?php if ($certificado): ?>
                            <span class="badge success">✅ Emitido</span>
                            <br>
                            <small><?= $certificado['codigo_certificado'] ?></small>
                        <?php else: ?>
                            <span class="badge secondary">⏳ Pendiente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        .evento-grupo {
            margin-bottom: 2rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 1rem;
            background: #f9f9f9;
        }
        
        .titulo-evento {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .form-asistencia {
            margin: 0;
        }
        
        .form-asistencia select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .badge.success {
            background: #27ae60;
            color: white;
        }
        
        .badge.secondary {
            background: #95a5a6;
            color: white;
        }
    </style>
</body>
</html>