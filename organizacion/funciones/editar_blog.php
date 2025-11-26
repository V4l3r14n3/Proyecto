<?php
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";
use MongoDB\BSON\ObjectId;

if (!isset($_GET['id'])) {
    echo "<script>window.location='../ver_blog.php'</script>";
    exit;
}

$id = new ObjectId($_GET['id']);
$post = $bd->blog->findOne(["_id" => $id]);

if (!$post) {
    echo "<script>window.location='../ver_blog.php'</script>";
    exit;
}

include '../includes/layout.php';
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">

<div class="main-content">
    <h2>✏ Editar Publicación</h2>

    <form action="guardar_edicion_blog.php" method="POST" enctype="multipart/form-data" class="formulario">
        
        <input type="hidden" name="id" value="<?= $post['_id'] ?>">

        <label>Título:</label>
        <input type="text" name="titulo" value="<?= $post['titulo'] ?>" required>

        <label>Contenido:</label>
        <textarea name="contenido" rows="6" required><?= $post['contenido'] ?></textarea>

        <label>Imagen actual:</label><br>
        <?php if (!empty($post['imagen'])): ?>
            <img src="/Proyecto/<?= $post['imagen'] ?>" width="250"><br>
        <?php else: ?>
            <em>No hay imagen</em><br>
        <?php endif; ?>

        <label>Subir nueva imagen (opcional):</label>
        <input type="file" name="imagen">

        <button type="submit" class="btn btn-primary mt-3">Guardar cambios</button>
        <a href="../ver_blog.php" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>

<?php include '../includes/layout_footer.php'; ?>
