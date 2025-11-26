<?php 
include 'includes/layout.php';
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if ($_SESSION['usuario']['rol'] !== "organizacion") {
    header("Location: ../index.php");
    exit();
}
?>
<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<div class="main-content">
    <h2>Publicar en el Blog 📝</h2>

    <form action="funciones/publicar_blog.php" method="POST" enctype="multipart/form-data" class="formulario">
        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Contenido</label>
        <textarea name="contenido" rows="6" required></textarea>

        <label>Imagen (Opcional)</label>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit">Publicar</button>
    </form>
</div>

<?php if (isset($_GET['creado'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Publicación creada 🥳',
    text: 'Tu publicación ya está visible para los voluntarios.',
    confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>


<?php include 'includes/layout_footer.php'; ?>