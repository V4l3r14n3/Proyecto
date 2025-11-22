<?php
include $_SERVER['DOCUMENT_ROOT']."/Proyecto/includes/conexion.php";
include "includes/layout.php";

if ($_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../index.php");
    exit;
}

// Obtener mensajes SOLO de su organización
$mensajes = $bd->foro->find([
    "id_organizacion" => $_SESSION['usuario']['_id']['$oid']
]);
?>

<div class="main-content">
    <h2>📢 Foro de Mi Organización</h2>

    <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel">
        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Mensaje</label>
        <textarea name="mensaje" required></textarea>

        <button type="submit">Publicar</button>
    </form>

    <table class="tabla">
        <tr>
            <th>Fecha</th>
            <th>Título</th>
            <th>Mensaje</th>
            <th>Autor</th>
        </tr>

        <?php foreach ($mensajes as $m): ?>
        <tr>
            <td><?= $m['fecha'] ?></td>
            <td><?= $m['titulo'] ?></td>
            <td><?= $m['mensaje'] ?></td>
            <td><?= $m['autor'] == "organizacion" ? "📌 Yo" : "🙋‍♂️ Voluntario"; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include 'includes/layout_footer.php'; ?>

<!-- Al final del archivo foro.php de organización, antes del </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // Mostrar alerta si hay mensaje en sesión
    <?php if (isset($_SESSION['alert'])): ?>
        const alertData = <?= json_encode($_SESSION['alert']) ?>;
        
        Swal.fire({
            icon: alertData.type,
            title: alertData.title,
            text: alertData.message,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3085d6',
            timer: alertData.type === 'success' ? 3000 : 5000,
            timerProgressBar: true
        });

        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    // Confirmación antes de enviar el formulario (opcional para organización)
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const titulo = document.querySelector('input[name="titulo"]').value;
        const mensaje = document.querySelector('textarea[name="mensaje"]').value;
        
        if (titulo && mensaje) {
            e.preventDefault();
            
            Swal.fire({
                title: '¿Publicar mensaje?',
                text: '¿Estás seguro de que quieres publicar este mensaje en el foro de tu organización?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, publicar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading mientras se envía
                    Swal.fire({
                        title: 'Publicando...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar formulario
                    document.querySelector('form').submit();
                }
            });
        }
    });
</script>