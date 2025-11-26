<?php
include "includes/layout.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

// Obtener organizaciones pendientes
$pendientes = $bd->usuarios->find([
    'rol' => 'organizacion',
    'estado' => ['$ne' => 'aprobado'] // pendientes o sin estado
]);
?>

<h1>📋 Verificar organizaciones</h1>

<?php if (!$pendientes->isDead()): ?>
    <table class="tabla" style="width:100%; margin-top:20px;">
        <tr>
            <th>Nombre organización</th>
            <th>Correo</th>
            <th>URL de verificación</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($pendientes as $org): ?>
            <tr>
                <td><?= htmlspecialchars($org['nombre_org'] ?? 'No definido') ?></td>
                <td><?= htmlspecialchars($org['email']) ?></td>
                <td>
                    <?php if (!empty($org['verificacion_url'])): ?>
                        <a href="<?= $org['verificacion_url'] ?>" target="_blank" style="
            display:inline-block;
            background:#4a8fe7;
            padding:6px 10px;
            color:white;
            border-radius:5px;
            font-size:13px;
            text-decoration:none;
        ">📄 Documentos</a>
                    <?php else: ?>
                        <span style="color:gray;">No proporcionado</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="aprobar" data-id="<?= $org['_id'] ?>">✅ Aprobar</button>
                    <button class="rechazar" data-id="<?= $org['_id'] ?>">❌ Rechazar</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p style="margin-top:20px; font-size:18px;">🎉 No hay organizaciones pendientes.</p>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll(".aprobar").forEach(btn => {
        btn.addEventListener("click", () => {
            let id = btn.getAttribute("data-id");

            Swal.fire({
                title: "¿Aprobar organización?",
                text: "La organización podrá acceder al sistema.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, aprobar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    fetch("procesar_estado.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id=" + id + "&accion=aprobar"
                        })
                        .then(r => r.json())
                        .then(data => {
                            Swal.fire(data.titulo, data.mensaje, data.tipo).then(() => location.reload());
                        });
                }
            });
        });
    });

    document.querySelectorAll(".rechazar").forEach(btn => {
        btn.addEventListener("click", () => {
            let id = btn.getAttribute("data-id");

            Swal.fire({
                title: "Rechazar organización",
                input: "text",
                inputPlaceholder: "Motivo del rechazo (opcional)",
                showCancelButton: true,
                confirmButtonText: "Rechazar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    fetch("procesar_estado.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id=" + id + "&accion=rechazar&motivo=" + (result.value ?? "")
                        })
                        .then(r => r.json())
                        .then(data => {
                            Swal.fire(data.titulo, data.mensaje, data.tipo).then(() => location.reload());
                        });
                }
            });
        });
    });
</script>

<?php include "includes/layout_footer.php"; ?>