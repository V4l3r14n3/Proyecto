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
    <title>Voluntarios Inscritos - <?= htmlspecialchars($_SESSION['usuario']['nombre_org']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .evento-grupo {
            margin-bottom: 2rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 1.5rem;
            background: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .titulo-evento {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            margin-top: 0;
        }
        
        .form-asistencia {
            margin: 0;
        }
        
        .form-asistencia select {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
        }
        
        .form-asistencia select:hover {
            border-color: #3498db;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .badge.success {
            background: #27ae60;
            color: white;
        }
        
        .badge.secondary {
            background: #95a5a6;
            color: white;
        }
        
        .info-box {
            background: #e8f4fd;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .no-inscripciones {
            text-align: center;
            padding: 3rem;
            background: #fff3cd;
            border-radius: 10px;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .no-inscripciones ol {
            margin: 1rem auto;
            max-width: 400px;
            text-align: left;
        }
        
        .stats-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3498db;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/layout_header.php'; ?>
    
    <div class="main-content">
        <h2>👥 Voluntarios Inscritos en Mis Actividades</h2>

        <!-- Estadísticas -->
        <div class="stats-box">
            <h4>📊 Resumen de Inscripciones</h4>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= count($actividadesOrganizacion) ?></div>
                    <div class="stat-label">Actividades con inscritos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php 
                        $totalInscritos = 0;
                        foreach ($actividadesOrganizacion as $grupo) {
                            $totalInscritos += count($grupo['inscripciones']);
                        }
                        echo $totalInscritos;
                        ?>
                    </div>
                    <div class="stat-label">Total de inscripciones</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        $totalAsistencias = 0;
                        foreach ($actividadesOrganizacion as $grupo) {
                            foreach ($grupo['inscripciones'] as $inscripcion) {
                                if (isset($inscripcion['asistio']) && $inscripcion['asistio'] === true) {
                                    $totalAsistencias++;
                                }
                            }
                        }
                        echo $totalAsistencias;
                        ?>
                    </div>
                    <div class="stat-label">Asistencias registradas</div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['alert'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?= $_SESSION['alert']['type'] ?>',
                        title: '<?= $_SESSION['alert']['title'] ?>',
                        text: '<?= $_SESSION['alert']['message'] ?>',
                        confirmButtonColor: '#3085d6'
                    });
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
                    <small>- <?= htmlspecialchars($actividad['fecha_hora'] ?? 'Fecha no especificada') ?></small>
                </h3>
                
                <div class="info-box">
                    <strong>📋 Información de la actividad:</strong><br>
                    <strong>Lugar:</strong> <?= htmlspecialchars($actividad['lugar'] ?? 'No especificado') ?> | 
                    <strong>Descripción:</strong> <?= htmlspecialchars(substr($actividad['descripcion'] ?? 'Sin descripción', 0, 100)) ?>...
                </div>

                <div class="info-box">
                    <strong>👥 <?= count($inscripcionesEvento) ?> voluntario(s) inscrito(s) en esta actividad</strong>
                </div>

                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha Registro</th>
                            <th>Asistencia</th>
                            <th>Certificado</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td>
                                <strong><?= htmlspecialchars($voluntario['nombre'] ?? 'Desconocido') ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($voluntario['email'] ?? 'No disponible') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($i['fecha_registro'] ?? 'Fecha no disponible') ?>
                            </td>
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
                                    <small style="font-family: monospace;"><?= $certificado['codigo_certificado'] ?? 'Sin código' ?></small>
                                <?php else: ?>
                                    <span class="badge secondary">⏳ Pendiente</span>
                                    <br>
                                    <small>Se emitirá al marcar asistencia</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-inscripciones">
                <h3>📝 No hay voluntarios inscritos</h3>
                <p>Actualmente no hay voluntarios inscritos en ninguna de tus actividades.</p>
                
                <div style="margin: 2rem 0;">
                    <p><strong>Para que aparezcan voluntarios aquí:</strong></p>
                    <ol>
                        <li>Ve a <strong>"Crear Voluntariado"</strong> y crea actividades atractivas</li>
                        <li>Promociona tus actividades para que los voluntarios se inscriban</li>
                        <li>Las inscripciones aparecerán automáticamente en esta página</li>
                        <li>Podrás registrar su asistencia y generar certificados</li>
                    </ol>
                </div>
                
                <div style="margin-top: 2rem;">
                    <a href="crear_voluntariado.php" class="btn" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                        🎯 Crear Nueva Actividad
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/layout_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Confirmación antes de marcar asistencia
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.form-asistencia select');
            forms.forEach(select => {
                select.addEventListener('change', function(e) {
                    const form = this.form;
                    const asistio = this.value;
                    
                    if (asistio === 'si') {
                        e.preventDefault();
                        
                        Swal.fire({
                            title: '¿Confirmar asistencia?',
                            text: '¿Estás seguro de que este voluntario asistió a la actividad? Se generará un certificado automáticamente.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#27ae60',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, confirmar asistencia',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            } else {
                                this.value = '';
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>