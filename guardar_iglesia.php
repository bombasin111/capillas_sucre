<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Encriptar la contraseña
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        // Intentamos insertar el nuevo registro
        $query = $conexion->prepare("INSERT INTO iglesiaspt (nombre, usuario, contrasena) VALUES (?, ?, ?)");
        $query->execute([$nombre, $usuario, $contrasena]);
    
        // Si la inserción es exitosa, mostramos un mensaje de éxito
        echo "¡Iglesia registrada correctamente! 🎉";
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) {  // 23505 es el código de error para violar una restricción de unicidad en PostgreSQL
            echo "¡Ya usaste esa iglesia, estimado! 😅";
        } else {
            echo "Error al registrar la iglesia: " . $e->getMessage();
        }
    }
    
}
?>
