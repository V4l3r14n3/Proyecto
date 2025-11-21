<?php
include 'includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'organizacion') {
    header("Location: ../pages/login.php");
    exit();
}

// Normalizar ID
$rawId = $_SESSION['usuario']['_id'];
$idOrganizacion = is_array($rawId) ? $rawId['$oid'] : $rawId;

$voluntarios = $bd->usuarios->find(["rol" => "voluntario"]);
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['alerta'])): ?>
<script>
Swal.fire({
    icon: '<?= $_SESSION['alerta']['tipo'] ?>',
    title: '<?= $_SESSION['alerta']['titulo'] ?>',
    text: '<?= $_SESSION['alerta']['texto'] ?>',
    timer: 1800,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['alerta']); endif; ?>

<script>
function cargarMensajes() {
    let id_otro = document.getElementById("usuario_receptor").value;
    if (!id_otro) return;

    fetch("funciones/cargar_mensajes.php?id_otro=" + id_otro)
        .then(res => res.json())
        .then(data => {
            let area = document.getElementById("chat");
            area.innerHTML = "";

            data.forEach(m => {
                let clase = m.remitente_id === "<?= $idOrganizacion ?>" ? "msg-right" : "msg-left";
                area.innerHTML += `<div class="${clase}">${m.mensaje}<br><small>${m.fecha}</small></div>`;
            });

            area.scrollTop = area.scrollHeight;
        });
}
</script>

<div class="main-content">
    <h2>Mensajes 📩</h2>

    <select id="usuario_receptor" onchange="cargarMensajes()">
        <option value="">Selecciona un voluntario</option>
        <?php foreach ($voluntarios as $vol): ?>
            <option value="<?= $vol['_id'] ?>"><?= $vol['nombre'] ?></option>
        <?php endforeach; ?>
    </select>

    <div id="chat" class="chat-box"></div>

    <form method="POST" action="funciones/enviar_mensaje.php">
        <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
        <input type="hidden" name="receptor" id="input-receptor">
        <button type="submit">Enviar</button>
    </form>
</div>

<script>
document.getElementById("usuario_receptor").addEventListener("change", e => {
    document.getElementById("input-receptor").value = e.target.value;
});
</script>

<?php include 'includes/layout_footer.php'; ?>
