<?php 
require_once "../includes/conexion.php";
require_once "includes/layout.php"; 
?>

<header>
    <h1>Panel de la Organización 🏢</h1>
</header>

<section class="cards">
    <div class="card">
        <h3>Publicar Voluntariado</h3>
        <p>Crea nuevas oportunidades para los voluntarios.</p>
    </div>

    <div class="card">
        <h3>Gestión de Voluntarios</h3>
        <p>Consulta la lista de inscritos en tus programas.</p>
    </div>

    <div class="card">
        <h3>Reportes</h3>
        <p>Visualiza el impacto de tus actividades.</p>
    </div>
</section>

<?php include "includes/layout_footer.php"; ?>
