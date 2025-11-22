<?php
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
include "includes/layout.php";

if ($_SESSION['usuario']['rol'] !== "voluntario") {
    header("Location: ../index.php");
    exit;
}

// Función para buscar organizaciones
function obtenerOrganizaciones($bd) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .swal2-popup { font-size: 1.6rem; }
        
        .contador-caracteres {
            text-align: right;
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        
        .contador-caracteres.almost-full { color: #e67e22; }
        .contador-caracteres.full { color: #e74c3c; font-weight: bold; }
        
        .mensaje-corto {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
            color: #3498db;
        }
        
        .mensaje-corto:hover {
            text-decoration: underline;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
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
        
        .close:hover { color: #000; }
        
        .modal-header {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .mensaje-completo {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.5;
        }
        
        .badge-largo {
            background: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/layout_footer.php'; ?>

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
                    $nombreOrg = $org['nombre_org'] ?? $org['nombre'] ?? $org['nombre_organizacion'] ?? 'Organización';
                ?>
                    <option value="<?= htmlspecialchars($orgId) ?>">
                        <?= htmlspecialchars($nombreOrg) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Título</label>
            <input type="text" name="titulo" required maxlength="100" 
                   placeholder="Título breve (máximo 100 caracteres)">

            <label>Mensaje</label>
            <textarea name="mensaje" id="mensaje" required maxlength="1000" 
                      placeholder="Escribe tu mensaje aquí (máximo 1000 caracteres)"
                      rows="5"></textarea>
            <div class="contador-caracteres" id="contadorContainer">
                <span id="contador">0</span>/1000 caracteres
            </div>

            <button type="submit" id="btnPublicar">Publicar</button>
        </form>
        <?php else: ?>
        <div class="alert error">
            No hay organizaciones disponibles para enviar mensajes.
        </div>
        <?php endif; ?>

        <!-- Modal para mensajes completos -->
        <div id="modalMensaje" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3 id="modalTitulo"></h3>
                <div class="modal-header">
                    <span id="modalAutor"></span> - 
                    <span id="modalFecha"></span> - 
                    <span id="modalOrganizacion"></span>
                </div>
                <div class="mensaje-completo" id="modalMensajeTexto"></div>
            </div>
        </div>

        <table class="tabla">
            <tr>
                <th>Fecha</th>
                <th>Organización</th>
                <th>Título</th>
                <th>Mensaje</th>
                <th>Autor</th>
            </tr>

            <?php foreach ($mensajes as $m):
                try {
                    $orgId = $m['id_organizacion'] ?? null;
                    $nombreOrg = "N/A";
                    
                    if ($orgId) {
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
                
                $mensajeTexto = $m['mensaje'] ?? '';
                $esMensajeLargo = strlen($mensajeTexto) > 100;
                $mensajeMostrar = $esMensajeLargo ? substr($mensajeTexto, 0, 100) . '...' : $mensajeTexto;
            ?>
                <tr>
                    <td><?= htmlspecialchars($m['fecha'] ?? '') ?></td>
                    <td><?= htmlspecialchars($nombreOrg) ?></td>
                    <td><?= htmlspecialchars($m['titulo'] ?? '') ?></td>
                    <td>
                        <div class="mensaje-corto" 
                             data-titulo="<?= htmlspecialchars($m['titulo'] ?? '') ?>"
                             data-mensaje="<?= htmlspecialchars($mensajeTexto) ?>"
                             data-autor="<?= ($m['autor'] ?? '') == 'organizacion' ? '👩‍💼 Organización' : '🙋‍♂️ Voluntario' ?>"
                             data-fecha="<?= htmlspecialchars($m['fecha'] ?? '') ?>"
                             data-organizacion="<?= htmlspecialchars($nombreOrg) ?>">
                            <?= htmlspecialchars($mensajeMostrar) ?>
                            <?php if ($esMensajeLargo): ?>
                                <span class="badge-largo">Largo</span>
                            <?php endif; ?>
                        </div>
                    </td>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        // Contador de caracteres
        const textarea = document.getElementById('mensaje');
        const contador = document.getElementById('contador');
        const contadorContainer = document.getElementById('contadorContainer');
        
        if (textarea) {
            textarea.addEventListener('input', function() {
                const longitud = this.value.length;
                contador.textContent = longitud;
                
                // Cambiar color según la longitud
                contadorContainer.className = 'contador-caracteres';
                if (longitud > 800) {
                    contadorContainer.classList.add('almost-full');
                }
                if (longitud > 950) {
                    contadorContainer.classList.add('full');
                }
            });
            
            // Inicializar contador
            contador.textContent = textarea.value.length;
        }

        // Modal functionality
        const modal = document.getElementById('modalMensaje');
        const span = document.getElementsByClassName('close')[0];
        const mensajesCortos = document.querySelectorAll('.mensaje-corto');
        
        mensajesCortos.forEach(mensaje => {
            mensaje.addEventListener('click', function() {
                document.getElementById('modalTitulo').textContent = this.dataset.titulo;
                document.getElementById('modalMensajeTexto').textContent = this.dataset.mensaje;
                document.getElementById('modalAutor').textContent = this.dataset.autor;
                document.getElementById('modalFecha').textContent = this.dataset.fecha;
                document.getElementById('modalOrganizacion').textContent = this.dataset.organizacion;
                modal.style.display = 'block';
            });
        });
        
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // SweetAlert functionality
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

        // Confirmación antes de enviar
        document.getElementById('formForo')?.addEventListener('submit', function(e) {
            const titulo = document.querySelector('input[name="titulo"]').value;
            const mensaje = document.querySelector('textarea[name="mensaje"]').value;
            
            if (titulo && mensaje) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Publicar mensaje?',
                    html: `
                        <div style="text-align: left;">
                            <strong>Título:</strong> ${titulo.substring(0, 50)}${titulo.length > 50 ? '...' : ''}<br>
                            <strong>Mensaje:</strong> ${mensaje.substring(0, 100)}${mensaje.length > 100 ? '...' : ''}
                            ${mensaje.length > 100 ? `<br><small><em>+ ${mensaje.length - 100} caracteres más</em></small>` : ''}
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, publicar',
                    cancelButtonText: 'Cancelar',
                    width: '600px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Publicando...',
                            text: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        document.getElementById('formForo').submit();
                    }
                });
            }
        });
    </script>
</body>
</html>