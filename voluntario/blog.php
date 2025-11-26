<?php 
include 'includes/layout.php';
include $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

$posts = $bd->blog->find([], ["sort" => ["fecha" => -1]]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Blog</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>panel.css">

<style>
.card {
    max-width: 800px;
    margin: auto;
    border-radius: 12px;
    overflow: hidden;
}

.card-img-top {
    width: 100%;
    height: auto;
    object-fit: cover;
}
</style>
</head>

<body style="background:#f3f7f9">

<div class="container mt-5">
    <h2 class="mb-4 text-center">📢 Blog Global</h2>

    <?php foreach ($posts as $post): ?>
        <div class="card mb-4 shadow">

            <?php if (!empty($post['imagen'])): ?>
                <img src="/Proyecto/<?= $post['imagen'] ?>" class="card-img-top">
            <?php endif; ?>

            <div class="card-body">
                <h4 class="card-title"><?= $post['titulo'] ?></h4>

                <p class="card-text"><?= nl2br($post['contenido']) ?></p>

                <small class="text-muted">
                    ✍ Publicado por: <strong><?= $post['autor'] ?></strong> |
                    📅 <?= $post['fecha']->toDateTime()->format("d-m-Y") ?>
                </small>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>

<?php include 'includes/layout_footer.php'; ?>
