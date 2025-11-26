<?php 
include '../includes/header.php'; 
include '../includes/conexion.php';

// Obtener organizaciones ya registradas
$organizaciones_existentes = $bd->usuarios->find(
    ['rol' => 'organizacion'],
    ['projection' => ['nombre_org' => 1, '_id' => 0]]
);
?>

<div class="form-page">
    <section class="formulario">
        <h2>Registro de Usuario</h2>

        <!-- FORMULARIO SIN ACTION PARA USAR FETCH -->
        <form id="registroForm">

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

            <!-- CAMPO EXTRA PARA ORGANIZACIONES -->
            <div id="campoOrganizacion" style="display:none; margin-top:15px;">
                
                <label>Nombre de la Organización:</label>
                <select id="selectOrganizacion" name="nombre_org" style="display:none;">
                    <option value="">Selecciona una organización existente</option>

                    <?php 
                    foreach ($organizaciones_existentes as $org) {
                        if (!empty($org['nombre_org'])) {
                            echo '<option value="' . $org['nombre_org'] . '">' . $org['nombre_org'] . '</option>';
                        }
                    }
                    ?>
                    
                    <option value="nueva">+ Crear una nueva organización</option>
                </select>

                <input type="text" id="inputOrganizacion" name="nombre_org_nueva" 
                placeholder="Nombre de nueva organización" style="display:none;">

                <label>📎 Enlace de verificación (Drive/OneDrive/Web oficial):</label>
                <input type="url" name="verificacion_url" placeholder="https://drive.google.com/...">
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
// Mostrar/ocultar sección de organización
document.getElementById("rol").addEventListener("change", function () {
    const campo = document.getElementById("campoOrganizacion");
    const select = document.getElementById("selectOrganizacion");
    const input = document.getElementById("inputOrganizacion");

    if (this.value === "organizacion") {
        campo.style.display = "block";
        select.style.display = "block";
        input.style.display = "none";
    } else {
        campo.style.display = "none";
        select.style.display = "none";
        input.style.display = "none";
    }
});

// Mostrar input cuando eligen "Nueva organización"
document.getElementById("selectOrganizacion").addEventListener("change", function () {
    document.getElementById("inputOrganizacion").style.display = 
        this.value === "nueva" ? "block" : "none";
});

// Submit con Fetch + SweetAlert
document.getElementById("registroForm").addEventListener("submit", async function(e){
    e.preventDefault(); 

    const formData = new FormData(this);

    const response = await fetch("procesar_registro.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    Swal.fire({
        icon: result.status === "success" ? "success" : "error",
        title: result.status === "success" ? "Registro exitoso 🎉" : "Error",
        text: result.mensaje,
        confirmButtonText: result.status === "success" ? "Continuar" : "Intentar de nuevo"
    }).then(() => {
        if(result.status === "success"){
            window.location.href = "login.php";
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
