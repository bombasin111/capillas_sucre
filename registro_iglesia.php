<!DOCTYPE html>
<html>
<head>
    <title>Registro de Iglesia</title>
</head>
<body>
    <h2>Registrar Nueva Iglesia</h2>
    <form action="guardar_iglesia.php" method="POST">
        <label>Nombre de la iglesia:</label>
        <input type="text" name="nombre" required><br><br>

        <label>Usuario:</label>
        <input type="text" name="usuario" required><br><br>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required><br><br>

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
