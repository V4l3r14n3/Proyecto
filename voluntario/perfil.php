<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

use MongoDB\BSON\ObjectId;

// Verifica sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

// Obtener certificados del voluntario
$certificados = $bd->certificados->find([
    'voluntario_id' => $_SESSION['usuario']['_id']['$oid']
]);

?>

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
                <input type="password" name="password_nueva">

                <button type="submit">Guardar Cambios</button>
            </form>
        </div>

        <div class="certificados-section">
            <h3>📜 Mis Certificados de Voluntariado</h3>

            <!-- DEBUG: Información -->
            <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
                <h4>DEBUG - Información del voluntario:</h4>
                <p><strong>ID del voluntario:</strong> <?= $_SESSION['usuario']['_id']['$oid'] ?></p>
                <p><strong>Certificados encontrados:</strong> <?= iterator_count($certificados) ?></p>

                <?php
                // Verificar si hay certificados para esta actividad específica
                $certificadoEspecifico = $bd->certificados->findOne([
                    'voluntario_id' => $_SESSION['usuario']['_id']['$oid'],
                    'actividad_id' => '691f22c6b54f63958f04a3a6'
                ]);
                ?>
                <p><strong>Certificado para actividad 691f22c6b54f63958f04a3a6:</strong>
                    <?= $certificadoEspecifico ? '✅ ENCONTRADO' : '❌ NO ENCONTRADO' ?>
                </p>
            </div>

            <?php if (iterator_count($certificados) > 0): ?>
                <div class="certificados-grid">
                    <?php foreach ($certificados as $cert):
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
                                <p><strong>Organización:</strong> <?= htmlspecialchars($cert['nombre_organizacion']) ?></p>
                                <p><strong>Fecha de actividad:</strong> <?= htmlspecialchars($cert['fecha_actividad']) ?></p>
                                <p><strong>Horas de voluntariado:</strong> <?= $cert['horas_voluntariado'] ?> horas</p>
                                <p><strong>Fecha de emisión:</strong> <?= htmlspecialchars($cert['fecha_emision']) ?></p>
                            </div>
                            <div class="certificado-actions">
                                <button onclick="verCertificado('<?= $cert['_id'] ?>')" class="btn-ver">👁️ Ver Certificado</button>
                                <button onclick="descargarCertificado('<?= $cert['_id'] ?>')" class="btn-descargar">📥 Descargar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-certificados">
                    <p>📝 Aún no tienes certificados de voluntariado.</p>
                    <p>Los certificados se generan automáticamente cuando marcas asistencia en los eventos.</p>
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
        fetch(`funciones/obtener_certificado.php?id=${certificadoId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('contenidoCertificado').innerHTML = html;
                document.getElementById('modalCertificado').style.display = 'block';
            });
    }

    function descargarCertificado(certificadoId) {
        Swal.fire({
            title: 'Descargando certificado...',
            text: 'Preparando tu certificado para descarga',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Redirigir a la función de descarga
        window.location.href = `funciones/descargar_certificado.php?id=${certificadoId}`;
    }

    // Cerrar modal
    document.querySelector('.close').onclick = function() {
        document.getElementById('modalCertificado').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('modalCertificado')) {
            document.getElementById('modalCertificado').style.display = 'none';
        }
    }
</script>

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
    }

    .certificado-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .certificado-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .certificado-codigo {
        background: #34495e;
        color: white;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
    }

    .certificado-body p {
        margin: 0.5rem 0;
        font-size: 0.9rem;
    }

    .certificado-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }

    .btn-ver,
    .btn-descargar {
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.8rem;
    }

    .btn-ver {
        background: #3498db;
        color: white;
    }

    .btn-descargar {
        background: #27ae60;
        color: white;
    }

    .no-certificados {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
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
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }

    @media (max-width: 768px) {
        .perfil-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Perfil actualizado',
            text: 'Tus cambios han sido guardados correctamente 💾',
            confirmButtonColor: '#00724f'
        }).then(() => {
            history.replaceState(null, "", location.pathname);
        });
    </script>
<?php endif; ?>