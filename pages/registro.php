<?php 
include '../includes/header.php'; 
include '../includes/conexion.php';
?>
<div class="form-page">
    <section class="formulario">
        <h2>Registro de Usuario</h2>
        <form id="registroForm" method="POST" action="procesar_registro.php">
            <label>Nombre completo:</label>
            <input type="text" name="nombre" required>

            <label>Correo electrónico:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <label>Rol:</label>
            <select name="rol" id="rol" required>
                <option value="voluntario">Voluntario</option>
                <option value="organizacion">Organización</option>
            </select>

            <div id="campoOrganizacion" style="display:none; margin-top:15px;">
                <label>Nombre de la Organización:</label>
                <select id="selectOrganizacion" name="nombre_org" style="display:none;">
                    <option value="">Selecciona una organización existente</option>
                    <?php 
                    $organizaciones_existentes = $bd->usuarios->find(
                        ['rol' => 'organizacion'],
                        ['projection' => ['nombre_org' => 1, '_id' => 0]]
                    );
                    foreach ($organizaciones_existentes as $org) {
                        if (!empty($org['nombre_org'])) {
                            echo '<option value="'.$org['nombre_org'].'">'.$org['nombre_org'].'</option>';
                        }
                    }
                    ?>
                    <option value="nueva">+ Nueva organización</option>
                </select>
                <input type="text" id="inputOrganizacion" name="nombre_org_nueva" placeholder="Nueva organización" style="display:none;">
            </div>

            <button type="submit">Registrar</button>
            <p class="form-link">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById("rol").addEventListener("change", function () {
    const campo = document.getElementById("campoOrganizacion");
    const select = document.getElementById("selectOrganizacion");
    const input = document.getElementById("inputOrganizacion");

    if (this.value === "organizacion") {
        campo.style.display = "block";
        select.style.display = "block";
    } else {
        campo.style.display = "none";
        select.style.display = "none";
        input.style.display = "none";
    }
});

document.getElementById("selectOrganizacion").addEventListener("change", function () {
    const input = document.getElementById("inputOrganizacion");
    input.style.display = (this.value === "nueva") ? "block" : "none";
});
</script>

<?php include '../includes/footer.php'; ?>
