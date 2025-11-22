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

<div class="main-content">
    <h2>📢 Foro General</h2>

    <!-- DEBUG: Información de organizaciones -->
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
        <h4>DEBUG - Organizaciones encontradas (<?= count($organizacionesArray) ?>):</h4>
        <?php if (count($organizacionesArray) > 0): ?>
            <ul>
            <?php foreach ($organizacionesArray as $org): ?>
                <li>
                    ID: <?= $org['_id'] ?>, 
                    Nombre: <?= $org['nombre_org'] ?? $org['nombre'] ?? $org['nombre_organizacion'] ?? 'No encontrado' ?>,
                    Email: <?= $org['email'] ?? 'No tiene' ?>,
                    Rol: <?= $org['rol'] ?? 'No especificado' ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No se encontraron organizaciones en la base de datos</p>
            <p>Colecciones disponibles: 
                <?php 
                $colecciones = iterator_to_array($bd->listCollectionNames());
                echo implode(', ', $colecciones);
                ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (count($organizacionesArray) > 0): ?>
    <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel">
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

        <button type="submit">Publicar</button>
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