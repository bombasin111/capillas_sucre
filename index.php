<?php
session_start();
include 'conexion.php';

// Redirigir si ya está autenticado
if (isset($_SESSION['id_iglesia'])) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingresar'])) {
    $id_iglesia = $_POST['iglesia'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $query = $conexion->prepare("SELECT * FROM iglesiaspt WHERE id = ? AND usuario = ?");
    $query->execute([$id_iglesia, $usuario]);
    $iglesia = $query->fetch();

    if ($iglesia && password_verify($contrasena, $iglesia['contrasena'])) {
        $_SESSION['id_iglesia'] = $iglesia['id'];
        header('Location: registro_feligreses.php');
        exit;
    } else {
        $error_global = "¡Credenciales incorrectas 😅!";
    }
}

// Obtener lista de iglesias desde la base de datos
$query = $conexion->query("SELECT id, nombre FROM iglesiaspt");
$iglesias = $query->fetchAll();

// Procesar el formulario de autenticación para la búsqueda global
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_global'])) {
    $usuario_global = $_POST['usuario_global'];
    $contrasena_global = $_POST['contrasena_global'];

    // Obtener el hash de la contraseña desde la base de datos
    $query = $conexion->prepare("SELECT id, contrasena FROM iglesiaspt WHERE usuario = ?");
    $query->execute([$usuario_global]);
    $usuario = $query->fetch();

    if ($usuario) {
        // Verificar la contraseña usando password_verify
        if (password_verify($contrasena_global, $usuario['contrasena'])) {
            // Autenticación exitosa para búsqueda global
            $_SESSION['busqueda_global'] = true;
            header('Location: buscar_global.php');
            exit;
        } else {
            $error_global = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_global = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - Iglesias de la Localidad</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f8ff, #ffe4e1); /* Fondo pastel celeste y rosado */
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9); /* Fondo semi-transparente */
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #6a5acd; /* Color celeste oscuro */
            text-align: center;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.9); /* Fondo semi-transparente para inputs */
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #9370db; /* Color lila pastel */
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #7b68ee; /* Color lila más oscuro al pasar el mouse */
        }
        .btn-secondary {
            background-color: #ffb6c1; /* Color rosado pastel */
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        .btn-secondary:hover {
            background-color: #ff69b4; /* Color rosado más oscuro al pasar el mouse */
        }
        .error {
            color: #ff4500; /* Color naranja para errores */
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seleccione su Capilla</h1>
        <h2>Usuario y Contraseña</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="iglesia" class="form-label">Capilla:</label>
                <select class="form-control" name="iglesia" required>
                    <option value="">-- Elija una Capilla --</option>
                    <?php foreach ($iglesias as $iglesia): ?>
                        <option value="<?= htmlspecialchars($iglesia['id']) ?>"><?= htmlspecialchars($iglesia['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" class="form-control" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" name="contrasena" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" name="ingresar" class="btn btn-primary">Ingresar</button>
                <button type="button" onclick="mostrarBusquedaGlobal()" class="btn btn-secondary">Búsqueda Global</button>
            </div>
        </form>

        <!-- Formulario de búsqueda global (oculto inicialmente) -->
        <div id="busqueda-global" style="display: none; margin-top: 20px;">
            <h2>Búsqueda Global</h2>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="usuario_global" class="form-label">Usuario:</label>
                    <input type="text" class="form-control" name="usuario_global" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena_global" class="form-label">Contraseña:</label>
                    <input type="password" class="form-control" name="contrasena_global" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="buscar_global" class="btn btn-primary">Acceder a la búsqueda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS (opcional, si necesitas funcionalidades de Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarBusquedaGlobal() {
            document.getElementById('busqueda-global').style.display = 'block';
        }
    </script>
<?php if (!empty($error_global)) : ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    Swal.fire({
        title: '¡Error!',
        text: '<?= addslashes($error_global) ?>',
        icon: 'error',
        background: '#ffdddd',
        iconColor: '#b22222',
        confirmButtonColor: '#8b0000',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: true,
        customClass: {
          popup: 'rounded-3'
        }
    });
    </script>
<?php endif; ?>
</body>
</html>