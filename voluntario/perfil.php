<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// SOLUCIÓN MEJORADA: Buscar certificados con comparación flexible
$todosLosCertificados = $bd->certificados->find();
$misCertificados = [];

foreach ($todosLosCertificados as $cert) {
    // Conversión segura a string para comparar
    $idCertificado = (string)$cert['voluntario_id'];
    $idSesion = (string)$_SESSION['usuario']['_id']['$oid'];
    
    // Comparación flexible
    if ($idCertificado === $idSesion) {
        $misCertificados[] = $cert;
    }
}

// Si no encuentra certificados, probar búsqueda directa
if (empty($misCertificados)) {
    try {
        $misCertificados = $bd->certificados->find([
            'voluntario_id' => $_SESSION['usuario']['_id']['$oid']
        ])->toArray();
    } catch (Exception $e) {
        $misCertificados = [];
    }
}

$certificados = new ArrayIterator($misCertificados);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Voluntario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .perfil-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .form-section,
        .certificados-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .certificados-grid {
            display: grid;
            gap: 1rem;
        }

        .certificado-card {
            border: 2px solid #3498db;
            border-radius: 10px;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .certificado-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .certificado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
        }

        .certificado-header h4 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .certificado-codigo {
            background: #34495e;
            color: white;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.7rem;
            font-family: monospace;
        }

        .certificado-body p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* === BOTONES DE CERTIFICADOS === */
        .certificado-acciones {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-ver,
        .btn-descargar {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            font-weight: 500;
        }

        .btn-ver {
            background: #00724f;
            color: white;
        }

        .btn-ver:hover {
            background: #005a3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 114, 79, 0.3);
        }

        .btn-descargar {
            background: #00bfa6;
            color: white;
        }

        .btn-descargar:hover {
            background: #009c7e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 191, 166, 0.3);
        }

        .no-certificados {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }

        .perfil-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .perfil-form label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .perfil-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .perfil-form input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        .perfil-form button {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s ease;
        }

        .perfil-form button:hover {
            background: #2980b9;
        }

        /* Modo oscuro */
        body.dark-mode .btn-ver {
            background: #00bfa6;
            color: #1b1b1b;
        }

        body.dark-mode .btn-ver:hover {
            background: #00d4b0;
        }

        body.dark-mode .btn-descargar {
            background: #00724f;
            color: white;
        }

        body.dark-mode .btn-descargar:hover {
            background: #008a65;
        }

        @media (max-width: 768px) {
            .perfil-container {
                grid-template-columns: 1fr;
            }

            .certificado-acciones {
                flex-direction: column;
            }

            .certificado-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="main-content">
        <h2>Mi Perfil 🏷️</h2>

        <div class="perfil-container">
            <div class="form-section">
                <h3>Información Personal</h3>
                <form method="POST" action="funciones/update_perfil.php" class="perfil-form">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>" required>

                    <label>Email:</label>
                    <input type="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>" disabled>

                    <label>Nueva contraseña (opcional):</label>
                    <input type="password" name="password_nueva" placeholder="Dejar vacío para no cambiar">

                    <button type="submit">💾 Guardar Cambios</button>
                </form>
            </div>

            <div class="certificados-section">
                <h3>📜 Mis Certificados de Voluntariado</h3>

                <?php if (count($misCertificados) > 0): ?>
                    <div class="certificados-grid">
                        <?php foreach ($misCertificados as $cert):
                            // Obtener información adicional de la actividad
                            $actividad = $bd->actividades->findOne([
                                '_id' => new ObjectId($cert['actividad_id'])
                            ]);
                        ?>
                            <div class="certificado-card">
                                <div class="certificado-header">
                                    <h4><?= htmlspecialchars($cert['titulo_actividad']) ?></h4>
                                    <span class="certificado-codigo"><?= $cert['codigo_certificado'] ?></span>
                                </div>
                                <div class="certificado-body">
                                    <p><strong>🏢 Organización:</strong> <?= htmlspecialchars($cert['nombre_organizacion']) ?></p>
                                    <p><strong>📅 Fecha de actividad:</strong> <?= htmlspecialchars($cert['fecha_actividad']) ?></p>
                                    <p><strong>⏱️ Horas de voluntariado:</strong> <?= $cert['horas_voluntariado'] ?> horas</p>
                                    <p><strong>🕐 Fecha de emisión:</strong> <?= htmlspecialchars($cert['fecha_emision']) ?></p>
                                    <?php if ($actividad && isset($actividad['lugar'])): ?>
                                        <p><strong>📍 Lugar:</strong> <?= htmlspecialchars($actividad['lugar']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="certificado-acciones">
                                    <a href="funciones/obtener_certificado.php?id=<?= $cert['_id'] ?>" class="btn-ver" target="_blank">👁️ Ver Certificado</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-certificados">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                        <h4>Aún no tienes certificados de voluntariado</h4>
                        <p>Los certificados se generan automáticamente cuando la organización marca tu asistencia en los eventos.</p>
                        <div style="margin-top: 1rem; padding: 1rem; background: #e8f4fd; border-radius: 5px;">
                            <small><strong>💡 Tip:</strong> Participa en más actividades y asegúrate de que la organización registre tu asistencia.</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/layout_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Perfil actualizado',
                    text: 'Tus cambios han sido guardados correctamente 💾',
                    confirmButtonColor: '#00724f',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    history.replaceState(null, "", location.pathname);
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>