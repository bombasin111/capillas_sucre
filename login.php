<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_iglesia = $_POST['iglesia'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Validar credenciales
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
?>
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