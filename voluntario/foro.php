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
?>

<div class="main-content">
    <h2>📢 Foro General</h2>

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
                    <?= htmlspecialchars($org['nombre_org'] ?? 'Sin nombre') ?>
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
                $orgId = $m['id_organizacion'];
                if ($orgId instanceof MongoDB\BSON\ObjectId) {
                    $orgId = $orgId->__toString();
                }
                
                $org = $bd->organizaciones->findOne([
                    "_id" => new MongoDB\BSON\ObjectId($orgId)
                ]);
                $nombreOrg = $org['nombre_org'] ?? "N/A";
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