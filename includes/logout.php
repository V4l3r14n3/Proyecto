<?php
session_start();
session_unset();  // elimina todas las variables de sesión
session_destroy(); // destruye la sesión

// Redirigir al index o al login
header("Location: ../index.php");
exit();
?>
