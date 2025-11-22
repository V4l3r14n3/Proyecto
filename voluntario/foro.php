<?php
include $_SERVER['DOCUMENT_ROOT']."/Proyecto/includes/conexion.php";
include "includes/layout.php";

if ($_SESSION['usuario']['rol'] !== "voluntario") {
    header("Location: ../index.php");
    exit;
}

// Voluntario ve todo el foro global
$mensajes = $bd->foro->find();
$organizaciones = $bd->organizaciones->find();
?>

<div class="main-content">
    <h2>📢 Foro General</h2>

    <form action="../includes/guardar_foro.php" method="POST" class="formulario-panel">
        <label>Enviar mensaje a:</label>
        <select name="id_organizacion" required>
            <option disabled selected>Selecciona una organización</option>
            <?php foreach($organizaciones as $org): ?>
                <option value="<?=$org['_id']?>"><?=$org['nombre_org']?></option>
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
            $org = $bd->organizaciones->findOne(["_id"=>$m['id_organizacion']]);
        ?>
        <tr>
            <td><?= $m['fecha'] ?></td>
            <td><?= $org['nombre'] ?? "N/A" ?></td>
            <td><?= $m['titulo'] ?></td>
            <td><?= $m['mensaje'] ?></td>
            <td><?= $m['autor']=="organizacion" ? "👩‍💼 Organización" : "🙋‍♂️ Voluntario" ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include 'includes/layout_footer.php'; ?>