<?php
$conn = new mysqli("localhost", "usuario_balistica", "password_seguro", "balistica");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    echo "¡Conexión exitosa a la base de datos!";
}
$conn->close();
?>

