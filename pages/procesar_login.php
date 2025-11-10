<?php
// Simulación temporal (más adelante se conecta a MongoDB)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === "admin@demo.com" && $password === "1234") {
    echo "<h2>Bienvenido, administrador</h2>";
    echo "<a href='dashboard.php'>Ir al panel</a>";
} else {
    echo "<h2>Credenciales incorrectas</h2>";
    echo "<a href='login.php'>Volver</a>";
}
