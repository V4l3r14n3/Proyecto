<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// SOLUCIÓN: Buscar todos los certificados y filtrar manualmente
$todosLosCertificados = $bd->certificados->find();
$misCertificados = [];

foreach ($todosLosCertificados as $cert) {
    // Comparación flexible de IDs
    $idCertificado = (string)$cert['voluntario_id'];
    $idSesion = (string)$_SESSION['usuario']['_id']['$oid'];

    if ($idCertificado === $idSesion) {
        $misCertificados[] = $cert;
    }
}

// Convertir a ArrayIterator para mantener compatibilidad
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

        .certificado-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-ver,
        .btn-descargar {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background-color 0.2s ease;
        }

        .btn-ver {
            background: #3498db;
            color: white;
        }

        .btn-ver:hover {
            background: #2980b9;
        }

        .btn-descargar {
            background: #27ae60;
            color: white;
        }

        .btn-descargar:hover {
            background: #219a52;
        }

        .no-certificados {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .certificado-modal {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #000;
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

        @media (max-width: 768px) {
            .perfil-container {
                grid-template-columns: 1fr;
            }

            .certificado-actions {
                flex-direction: column;
            }

            .certificado-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* Estilos para el certificado en el modal */
        .certificado-completo {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
        }

        .certificado-titulo {
            text-align: center;
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .certificado-contenido {
            text-align: center;
            padding: 2rem 0;
        }

        .certificado-nombre {
            color: #e74c3c;
            font-size: 1.5rem;
            margin: 1rem 0;
            font-weight: bold;
        }

        .certificado-footer {
            text-align: center;
            border-top: 2px solid #3498db;
            padding-top: 1rem;
            margin-top: 1.5rem;
            color: #7f8c8d;
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
        }

        .btn-ver {
            background: #00724f;
            color: white;
        }

        .btn-descargar {
            background: #00bfa6;
            color: white;
        }

        .btn-ver:hover,
        .btn-descargar:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        /* Modo oscuro */
        body.dark-mode .btn-ver {
            background: #00bfa6;
        }

        body.dark-mode .btn-descargar {
            background: #00724f;
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
                                    <!-- Debug: muestra el ID del certificado -->
                                    <small style="display:block; color: #666;">ID: <?= $cert['_id'] ?></small>

                                    <a href="funciones/obtener_certificado.php?id=<?= $cert['_id'] ?>" class="btn-ver" target="_blank">👁️ Ver Certificado</a>
                                    <a href="funciones/descargar_certificado.php?id=<?= $cert['_id'] ?>" class="btn-descargar" target="_blank">📥 Descargar PDF</a>
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

    <!-- Modal para ver certificado -->
    <div id="modalCertificado" class="modal">
        <div class="modal-content certificado-modal">
            <span class="close">&times;</span>
            <div id="contenidoCertificado"></div>
        </div>
    </div>

    <?php include 'includes/layout_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function verCertificado(certificadoId) {
            // Mostrar loading
            Swal.fire({
                title: 'Cargando certificado...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`funciones/obtener_certificado.php?id=${certificadoId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar el certificado');
                    }
                    return response.text();
                })
                .then(html => {
                    Swal.close();
                    document.getElementById('contenidoCertificado').innerHTML = html;
                    document.getElementById('modalCertificado').style.display = 'block';
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el certificado: ' + error.message,
                        confirmButtonColor: '#e74c3c'
                    });
                });
        }

        function descargarCertificado(certificadoId) {
            Swal.fire({
                title: 'Preparando descarga...',
                text: 'Generando tu certificado en formato PDF',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Redirigir a la función de descarga
            setTimeout(() => {
                window.location.href = `funciones/descargar_certificado.php?id=${certificadoId}`;
            }, 1000);
        }

        // Cerrar modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('modalCertificado').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalCertificado')) {
                document.getElementById('modalCertificado').style.display = 'none';
            }
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('modalCertificado').style.display = 'none';
            }
        });
    </script>

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
                    // Limpiar URL sin recargar la página
                    history.replaceState(null, "", location.pathname);
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>