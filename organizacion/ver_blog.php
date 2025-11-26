<?php 
include 'includes/layout.php';
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

$posts = $bd->blog->find(
    ["autor" => $_SESSION['usuario']['nombre_org']], 
    ["sort" => ["fecha" => -1]]
);
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <h2>📢 Blog Publicado</h2>

    <?php foreach ($posts as $post): ?>
        <div class="post-card">
            <h3><?= $post['titulo'] ?></h3>
            <small><strong>Publicado por:</strong> <?= $post['autor'] ?> | <?= $post['fecha']->toDateTime()->format("d-m-Y") ?></small>

            <p><?= nl2br($post['contenido']) ?></p>

            <?php if (!empty($post['imagen'])): ?>
                <img src="/Proyecto/<?= $post['imagen'] ?>" width="300" style="margin-top:10px;">
            <?php endif; ?>

            <div style="margin-top:15px;">
                <button class="btn btn-warning" onclick="location.href='funciones/editar_blog.php?id=<?= $post->_id ?>'">✏️ Editar</button>

                <button class="btn btn-danger" onclick="confirmarEliminar('<?= $post->_id ?>')">🚮 Eliminar</button>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>
</div>

<script>
function confirmarEliminar(id) {
    Swal.fire({
        title: "¿Eliminar publicación?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((r) => {
        if (r.isConfirmed) {
            window.location.href = "funciones/eliminar_blog.php?id=" + id;
        }
    });
}
</script>
<?php if (isset($_GET['eliminado'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Publicación eliminada',
    text: 'Se eliminó correctamente',
    confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>

<?php if (isset($_GET['editado'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Cambios guardados',
    text: 'La publicación fue actualizada correctamente.',
});
</script>
<?php endif; ?>



<?php include 'includes/layout_footer.php'; ?>
