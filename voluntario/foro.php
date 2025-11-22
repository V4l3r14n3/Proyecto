<?php
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
include "includes/layout.php";

if ($_SESSION['usuario']['rol'] !== "voluntario") {
    header("Location: ../index.php");
    exit;
}

// Función para buscar organizaciones
function obtenerOrganizaciones($bd) {
    // Intentar diferentes nombres de colección
    $colecciones = ['organizaciones', 'organizacion', 'usuarios'];
    
    foreach ($colecciones as $coleccion) {
        if (in_array($coleccion, iterator_to_array($bd->listCollectionNames()))) {
            if ($coleccion === 'usuarios') {
                $result = $bd->$coleccion->find(['rol' => 'organizacion']);
            } else {
                $result = $bd->$coleccion->find();
            }
            
            $organizaciones = iterator_to_array($result);
            if (count($organizaciones) > 0) {
                return $organizaciones;
            }
        }
    }
    return [];
}

$organizacionesArray = obtenerOrganizaciones($bd);
$mensajes = $bd->foro->find();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro General</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .swal2-popup {
            font-size: 1.6rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/layout_header.php'; ?>

    <div class="main-content">
        <h2>📢 Foro General</h2>

        <?php if (count($organizacionesArray) > 0): ?>
        <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel" id="formForo">
            <label>Enviar mensaje a:</label>
            <select name="id_organizacion" required>
                <option value="" disabled selected>Selecciona una organización</option>
                <?php foreach ($organizacionesArray as $org): 
                    $orgId = $org['_id'];
                    if ($orgId instanceof MongoDB\BSON\ObjectId) {
                        $orgId = $orgId->__toString();
                    }
                    
                    // Buscar el nombre en diferentes campos posibles
                    $nombreOrg = $org['nombre_org'] ?? $org['nombre'] ?? $org['nombre_organizacion'] ?? 'Organización';
                ?>
                    <option value="<?= htmlspecialchars($orgId) ?>">
                        <?= htmlspecialchars($nombreOrg) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Mensaje</label>
            <textarea name="mensaje" required></textarea>

            <button type="submit" id="btnPublicar">Publicar</button>
        </form>
        <?php else: ?>
        <div class="alert error">
            No hay organizaciones disponibles para enviar mensajes.
        </div>
        <?php endif; ?>

        <table class="tabla">
            <tr>
                <th>Fecha</th>
                <th>Organización</th>
                <th>Título</th>
                <th>Mensaje</th>
                <th>Autor</th>
            </tr>

            <?php foreach ($mensajes as $m):
                // Obtener el nombre de la organización para cada mensaje
                try {
                    $orgId = $m['id_organizacion'] ?? null;
                    $nombreOrg = "N/A";
                    
                    if ($orgId) {
                        // Buscar en diferentes colecciones
                        $orgEncontrada = null;
                        foreach (['organizaciones', 'organizacion', 'usuarios'] as $coleccion) {
                            if (in_array($coleccion, iterator_to_array($bd->listCollectionNames()))) {
                                if (is_string($orgId)) {
                                    $orgId = new MongoDB\BSON\ObjectId($orgId);
                                }
                                
                                $org = $bd->$coleccion->findOne(["_id" => $orgId]);
                                if ($org) {
                                    $orgEncontrada = $org;
                                    break;
                                }
                            }
                        }
                        
                        if ($orgEncontrada) {
                            $nombreOrg = $orgEncontrada['nombre_org'] ?? $orgEncontrada['nombre'] ?? $orgEncontrada['nombre_organizacion'] ?? "N/A";
                        }
                    }
                } catch (Exception $e) {
                    $nombreOrg = "N/A";
                }
            ?>
                <tr>
                    <td><?= htmlspecialchars($m['fecha'] ?? '') ?></td>
                    <td><?= htmlspecialchars($nombreOrg) ?></td>
                    <td><?= htmlspecialchars($m['titulo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($m['mensaje'] ?? '') ?></td>
                    <td>
                        <?php 
                        if (($m['autor'] ?? '') == "organizacion") {
                            echo "👩‍💼 Organización";
                        } else {
                            echo "🙋‍♂️ Voluntario";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php include 'includes/layout_footer.php'; ?>

    <!-- SweetAlert2 JS -->
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

        // Opcional: Confirmación antes de enviar el formulario
        document.getElementById('formForo')?.addEventListener('submit', function(e) {
            const titulo = document.querySelector('input[name="titulo"]').value;
            const mensaje = document.querySelector('textarea[name="mensaje"]').value;
            
            if (titulo && mensaje) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Publicar mensaje?',
                    text: '¿Estás seguro de que quieres publicar este mensaje en el foro?',
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
                        document.getElementById('formForo').submit();
                    }
                });
            }
        });
    </script>
</body>
</html>