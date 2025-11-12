<?php include '../includes/conexion.php'; ?>
<link rel="stylesheet" href="../css/panel.css">

<div class="sidebar">
    <h2>Voluntario</h2>
    <a href="#">Mi Perfil</a>
    <a href="#">Mis Actividades</a>
    <a href="#">Buscar Voluntariados</a>
    <a href="#">Mensajes</a>
    <a href="../pages/logout.php">Cerrar Sesión</a>
</div>

<div class="main-content">
    <header>
        <h1>Bienvenido, Voluntario 👋</h1>
        <button class="logout" onclick="location.href='../pages/logout.php'">Salir</button>
    </header>

    <section class="cards">
        <div class="card">
            <h3>Actividades Disponibles</h3>
            <p>Explora nuevas oportunidades para participar.</p>
        </div>

        <div class="card">
            <h3>Mis Participaciones</h3>
            <p>Consulta tu historial de voluntariados.</p>
        </div>

        <div class="card">
            <h3>Certificados</h3>
            <p>Descarga tus certificados de participación.</p>
        </div>
    </section>
</div>
