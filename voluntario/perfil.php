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
    <link rel="stylesheet" href="../css/panel.css">
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
                                <div class="certificado-actions">
                                    <a href="#" class="btn-ver" onclick="mostrarMensajeCertificado()">Ver Certificado</a>
                                    <a href="#" class="btn-descargar" onclick="mostrarMensajeDescarga()">Descargar PDF</a>
                                </div>

                                <script>
                                    function mostrarMensajeCertificado() {
                                        alert('📄 Los certificados estarán disponibles próximamente. Estamos trabajando en esta funcionalidad.');
                                        return false;
                                    }

                                    function mostrarMensajeDescarga() {
                                        alert('⏳ La descarga de PDF estará habilitada en los próximos días. Gracias por tu paciencia.');
                                        return false;
                                    }
                                </script>
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