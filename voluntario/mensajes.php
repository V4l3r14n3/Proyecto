<?php
include '../includes/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/Proyecto/includes/conexion.php";

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'voluntario') {
    header("Location: ../pages/login.php");
    exit();
}

$idVoluntario = (string)$_SESSION['usuario']['_id'];

$organizaciones = $bd->usuarios->find(["rol" => "organizacion"]);
?>

<link rel="stylesheet" href="<?= CSS_URL ?>panel.css">
<script>
function cargarMensajes() {
    let id_otro = document.getElementById("usuario_receptor").value;
    if (!id_otro) return;

    fetch("../funciones/cargar_mensajes.php?id_otro=" + id_otro)
        .then(res => res.json())
        .then(data => {
            let area = document.getElementById("chat");
            area.innerHTML = "";

            data.forEach(m => {
                let clase = m.remitente_id === "<?= $idVoluntario ?>" ? "msg-right" : "msg-left";
                area.innerHTML += `<div class="${clase}">${m.mensaje}<br><small>${m.fecha}</small></div>`;
            });

            area.scrollTop = area.scrollHeight;
        });
}
</script>

<div class="main-content">
<h2>Mensajes 📩</h2>

<select id="usuario_receptor" onchange="cargarMensajes()">
    <option value="">Selecciona organización</option>
    <?php foreach ($organizaciones as $org): ?>
        <option value="<?= $org['_id'] ?>"><?= $org['nombre'] ?></option>
    <?php endforeach; ?>
</select>

<div id="chat" class="chat-box"></div>

<form method="POST" action="../funciones/enviar_mensaje.php">
    <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
    <input type="hidden" name="receptor" id="input-receptor">
    <button type="submit">Enviar</button>
</form>
</div>

<script>
// Mantiene el ID seleccionado para enviar mensaje
document.getElementById("usuario_receptor").addEventListener("change", (e) => {
    document.getElementById("input-receptor").value = e.target.value;
});
</script>

<?php include '../includes/layout_footer.php'; ?>
