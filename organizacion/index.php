<?php include '../includes/conexion.php'; ?>
<link rel="stylesheet" href="../css/panel.css">

<div class="sidebar">
    <h2>Organización</h2>
    <a href="#">Perfil</a>
    <a href="#">Crear Voluntariado</a>
    <a href="#">Voluntarios Inscritos</a>
    <a href="#">Mensajes</a>
    <a href="../includes/logout.php">Cerrar Sesión</a>
</div>

<div class="main-content">
    <header>
        <h1>Panel de la Organización 🏢</h1>
        <button class="logout" onclick="location.href='../includes/logout.php'">Salir</button>
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
</div>
