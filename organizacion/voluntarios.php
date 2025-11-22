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
    
    if (!$voluntario || !$actividad) return;
    
    $certificado = [
        'voluntario_id' => $inscripcion['voluntario_id'],
        'actividad_id' => $inscripcion['actividad_id'],
        'organizacion_id' => $_SESSION['usuario']['_id']['$oid'],
        'titulo_actividad' => $actividad['titulo'],
        'nombre_voluntario' => $voluntario['nombre'],
        'nombre_organizacion' => $_SESSION['usuario']['nombre_org'],
        'fecha_actividad' => $actividad['fecha_hora'],
        'fecha_emision' => date('Y-m-d H:i:s'),
        'codigo_certificado' => uniqid('CERT_'),
        'horas_voluntariado' => $actividad['duracion_horas'] ?? 4
    ];
    
    $bd->certificados->insertOne($certificado);
}

// Obtener TODAS las inscripciones y filtrar por organización
$inscripciones = $bd->inscripciones->find();
$actividadesOrganizacion = [];

// Agrupar inscripciones por actividad que pertenezcan a esta organización
foreach ($inscripciones as $inscripcion) {
    try {
        $actividad = $bd->actividades->findOne([
            '_id' => new ObjectId($inscripcion['actividad_id'])
        ]);
        
        // Verificar que la actividad existe y pertenece a esta organización
        if ($actividad && $actividad['organizacion'] === $_SESSION['usuario']['nombre_org']) {
            $actividadId = $actividad['_id']->__toString();
            
            if (!isset($actividadesOrganizacion[$actividadId])) {
                $actividadesOrganizacion[$actividadId] = [
                    'actividad' => $actividad,
                    'inscripciones' => []
                ];
            }
            
            $actividadesOrganizacion[$actividadId]['inscripciones'][] = $inscripcion;
        }
    } catch (Exception $e) {
        // Ignorar inscripciones con IDs inválidos
        continue;
    }
}

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

        <!-- DEBUG: Información -->
        <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
            <h4>Información de organización:</h4>
            <p><strong>Organización:</strong> <?= htmlspecialchars($_SESSION['usuario']['nombre_org']) ?></p>
            <p><strong>Actividades con inscripciones:</strong> <?= count($actividadesOrganizacion) ?></p>
            <p><strong>Total de inscripciones en BD:</strong> <?= iterator_count($bd->inscripciones->find()) ?></p>
        </div>

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

        <?php if (count($actividadesOrganizacion) > 0): ?>
            <?php foreach ($actividadesOrganizacion as $grupo): 
                $actividad = $grupo['actividad'];
                $inscripcionesEvento = $grupo['inscripciones'];
            ?>
            <div class="evento-grupo">
                <h3 class="titulo-evento">🎯 <?= htmlspecialchars($actividad['titulo']) ?> 
                    <small>(<?= htmlspecialchars($actividad['fecha_hora'] ?? 'Fecha no especificada') ?>)</small>
                </h3>
                
                <div style="background: #e8f4fd; padding: 8px; margin: 10px 0; border-radius: 3px;">
                    <small><?= count($inscripcionesEvento) ?> voluntario(s) inscrito(s)</small>
                </div>

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
                        <td><?= htmlspecialchars($voluntario['nombre'] ?? 'Desconocido') ?></td>
                        <td><?= htmlspecialchars($voluntario['email'] ?? 'No disponible') ?></td>
                        <td><?= htmlspecialchars($i['fecha_registro'] ?? 'Fecha no disponible') ?></td>
                        <td>
                            <form method="POST" class="form-asistencia">
                                <input type="hidden" name="inscripcion_id" value="<?= $i['_id']->__toString() ?>">
                                <select name="asistio" onchange="this.form.submit()">
                                    <option value="">Seleccionar</option>
                                    <option value="si" <?= (isset($i['asistio']) && $i['asistio'] === true) ? 'selected' : '' ?>>✅ Asistió</option>
                                    <option value="no" <?= (isset($i['asistio']) && $i['asistio'] === false) ? 'selected' : '' ?>>❌ No asistió</option>
                                </select>
                                <input type="hidden" name="marcar_asistencia" value="1">
                            </form>
                        </td>
                        <td>
                            <?php if ($certificado): ?>
                                <span class="badge success">✅ Emitido</span>
                                <br>
                                <small><?= $certificado['codigo_certificado'] ?? 'Sin código' ?></small>
                            <?php else: ?>
                                <span class="badge secondary">⏳ Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-inscripciones">
                <p style="text-align: center; color: #e74c3c; padding: 2rem;">
                    No hay voluntarios inscritos en ninguna de tus actividades.
                </p>
                <div style="text-align: center;">
                    <p>Para que aparezcan voluntarios aquí:</p>
                    <ol style="display: inline-block; text-align: left;">
                        <li>Crea actividades en "Crear Voluntariado"</li>
                        <li>Los voluntarios deben inscribirse en tus actividades</li>
                        <li>Las inscripciones aparecerán automáticamente aquí</li>
                    </ol>
                </div>
            </div>
        <?php endif; ?>
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
        
        .no-inscripciones {
            text-align: center;
            padding: 2rem;
            background: #f8d7da;
            border-radius: 10px;
            border: 1px solid #f5c6cb;
        }
        
        .no-inscripciones ol {
            margin: 1rem 0;
        }
    </style>
</body>
</html>