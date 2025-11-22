<?php
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
include "includes/layout.php";

if ($_SESSION['usuario']['rol'] !== "voluntario") {
    header("Location: ../index.php");
    exit;
}

// Voluntario ve todo el foro global
$mensajes = $bd->foro->find();
$organizaciones = $bd->organizaciones->find();

// Convertir a array para poder reutilizar
$organizacionesArray = iterator_to_array($organizaciones);

// DEBUG: Verificar qué datos tenemos
error_log("Número de organizaciones: " . count($organizacionesArray));
foreach ($organizacionesArray as $org) {
    error_log("Organización: " . print_r($org, true));
}
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
                Nombre: <?= $org['nombre_org'] ?? $org['nombre'] ?? 'No encontrado' ?>,
                Email: <?= $org['email'] ?? 'No tiene' ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No se encontraron organizaciones en la base de datos</p>
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

    <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel">
        <label>Enviar mensaje a:</label>
        <select name="id_organizacion" required>
            <option value="" disabled selected>Selecciona una organización</option>
            <?php foreach ($organizacionesArray as $org): 
                // Asegurar el formato correcto del ObjectId
                $orgId = $org['_id'];
                if ($orgId instanceof MongoDB\BSON\ObjectId) {
                    $orgId = $orgId->__toString();
                }
            ?>
                <option value="<?= htmlspecialchars($orgId) ?>">
                    <?= htmlspecialchars($org['nombre_org'] ?? $org['nombre'] ?? 'Sin nombre') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Mensaje</label>
        <textarea name="mensaje" required></textarea>

        <button type="submit">Publicar</button>
    </form>

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
                    if (is_string($orgId)) {
                        $orgId = new MongoDB\BSON\ObjectId($orgId);
                    }
                    
                    $org = $bd->organizaciones->findOne(["_id" => $orgId]);
                    if ($org) {
                        $nombreOrg = $org['nombre_org'] ?? $org['nombre'] ?? "N/A";
                    }
                }
            } catch (Exception $e) {
                $nombreOrg = "N/A";
                error_log("Error obteniendo organización: " . $e->getMessage());
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