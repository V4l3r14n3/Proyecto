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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro de Mi Organización</title>
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
        
        .badge-propio {
            background: #3498db;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .autor-organizacion {
            color: #27ae60;
            font-weight: bold;
        }
        
        .autor-voluntario {
            color: #e67e22;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <h2>📢 Foro de Mi Organización</h2>

        <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel" id="formForo">
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

            <button type="submit" id="btnPublicar">Publicar en mi organización</button>
        </form>

        <!-- Modal para mensajes completos -->
        <div id="modalMensaje" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3 id="modalTitulo"></h3>
                <div class="modal-header">
                    <span id="modalAutor"></span> - 
                    <span id="modalFecha"></span>
                </div>
                <div class="mensaje-completo" id="modalMensajeTexto"></div>
            </div>
        </div>

        <table class="tabla">
            <tr>
                <th>Fecha</th>
                <th>Título</th>
                <th>Mensaje</th>
                <th>Autor</th>
            </tr>

            <?php foreach ($mensajes as $m):
                $mensajeTexto = $m['mensaje'] ?? '';
                $esMensajeLargo = strlen($mensajeTexto) > 100;
                $mensajeMostrar = $esMensajeLargo ? substr($mensajeTexto, 0, 100) . '...' : $mensajeTexto;
                $esAutorOrganizacion = ($m['autor'] ?? '') == "organizacion";
                $autorTexto = $esAutorOrganizacion ? "📌 Yo" : "🙋‍♂️ Voluntario";
                $claseAutor = $esAutorOrganizacion ? "autor-organizacion" : "autor-voluntario";
            ?>
                <tr>
                    <td><?= htmlspecialchars($m['fecha'] ?? '') ?></td>
                    <td><?= htmlspecialchars($m['titulo'] ?? '') ?></td>
                    <td>
                        <div class="mensaje-corto" 
                             data-titulo="<?= htmlspecialchars($m['titulo'] ?? '') ?>"
                             data-mensaje="<?= htmlspecialchars($mensajeTexto) ?>"
                             data-autor="<?= $autorTexto ?>"
                             data-fecha="<?= htmlspecialchars($m['fecha'] ?? '') ?>">
                            <?= htmlspecialchars($mensajeMostrar) ?>
                            <?php if ($esMensajeLargo): ?>
                                <span class="badge-largo">Largo</span>
                            <?php endif; ?>
                            <?php if ($esAutorOrganizacion): ?>
                                <span class="badge-propio">Tú</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="<?= $claseAutor ?>">
                        <?= $autorTexto ?>
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
                            <p><strong>Este mensaje se publicará en el foro de tu organización:</strong></p>
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
                            text: 'Publicando mensaje en tu organización',
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

        // Validación en tiempo real del título
        const inputTitulo = document.querySelector('input[name="titulo"]');
        if (inputTitulo) {
            inputTitulo.addEventListener('input', function() {
                if (this.value.length > 90) {
                    this.style.borderColor = '#e67e22';
                } else {
                    this.style.borderColor = '';
                }
            });
        }

        // Validación en tiempo real del mensaje
        if (textarea) {
            textarea.addEventListener('input', function() {
                if (this.value.length > 900) {
                    this.style.borderColor = '#e67e22';
                } else {
                    this.style.borderColor = '';
                }
            });
        }
    </script>
</body>
</html>