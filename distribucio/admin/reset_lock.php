<?php
/**
 * Desbloquear cuenta - ELIMINAR DESPUÉS DE USAR
 */
session_start();
unset($_SESSION['login_attempts']);
session_destroy();

echo "<h1>Cuenta desbloqueada</h1>";
echo "<p style='color:green'>Se han eliminado los intentos fallidos.</p>";
echo "<p><a href='index.php'>Ir al Login</a></p>";
echo "<hr>";
echo "<p style='color:red'><strong>Elimina este archivo después de usarlo!</strong></p>";
?>
