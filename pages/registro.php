<?php include '../includes/header.php'; 
include '../includes/conexion.php';
?>
<div class="form-page">
    <section class="formulario">
        <h2>Registro de Usuario</h2>
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

            <div id="campoOrganizacion" style="display:none; margin-top:15px;">
                <label>Nombre de la Organización:</label>
                <select id="selectOrganizacion" name="nombre_org" style="display:none;">
                    <option value="">Selecciona una organización existente</option>
                    <?php 
                    include '../includes/conexion.php';
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
                <input type="text" id="inputOrganizacion" name="nombre_org_nueva" placeholder="Escribe el nombre de la nueva organización" style="display:none;">
            </div>

            <button type="submit">Registrar</button>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('registroForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch('procesar_registro.php', {
        method: 'POST',
        body: formData
    });

    const data = await response.json();

    if (data.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: data.mensaje,
            confirmButtonColor: '#00724f'
        }).then(() => {
            window.location.href = 'login.php';
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: data.mensaje,
            confirmButtonColor: '#00724f'
        });
    }
});

// Lógica de mostrar organización
const rolSelect = document.getElementById("rol");
const campoOrg = document.getElementById("campoOrganizacion");
const selectOrg = document.getElementById("selectOrganizacion");
const inputOrg = document.getElementById("inputOrganizacion");

rolSelect.addEventListener("change", () => {
    if (rolSelect.value === "organizacion") {
        campoOrg.style.display = "block";
        selectOrg.style.display = "block";
        inputOrg.style.display = "none";
    } else {
        campoOrg.style.display = "none";
    }
});

selectOrg.addEventListener("change", () => {
    inputOrg.style.display = selectOrg.value === "nueva" ? "block" : "none";
});
</script>
<?php include '../includes/footer.php'; ?>
