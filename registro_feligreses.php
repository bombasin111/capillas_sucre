<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_iglesia'])) {
    header('Location: index.php');
    exit;
}

// Obtener ID de la iglesia desde la sesión
$id_iglesia = $_SESSION['id_iglesia'];

// Buscar feligreses por nombre
$resultados = [];
if (isset($_GET['buscar'])) {
    $busqueda = '%' . $_GET['buscar'] . '%';
    $query = $conexion->prepare("SELECT * FROM feligresespt 
                                WHERE nombre ILIKE ? AND id_iglesia = ?");
    $query->execute([$busqueda, $id_iglesia]);
    $resultados = $query->fetchAll();

    if (count($resultados) === 0) {
    $no_encontrado = true;
    }
}

// Registrar nuevo feligrés
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $ci = $_POST['ci'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $bautizo = isset($_POST['bautizo']) ? 1 : 0;
    $confirmacion = isset($_POST['confirmacion']) ? 1 : 0;
    $matrimonio = isset($_POST['matrimonio']) ? 1 : 0;
    $pag = $_POST['pag'] ?? null;

    $query = $conexion->prepare("INSERT INTO feligresespt 
        (nombre, ci, fecha_nacimiento, bautizo, confirmacion, matrimonio, pag, id_iglesia)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $query->execute([
        $nombre, $ci, $fecha_nacimiento, $bautizo, $confirmacion, $matrimonio, $pag, $id_iglesia
    ]);
}

// Obtener todos los feligreses de la iglesia
//$query = $conexion->prepare("SELECT * FROM feligreses WHERE id_iglesia = ?");
//$query->execute([$id_iglesia]);
//$feligreses = $query->fetchAll();

// Obtener datos de la iglesia
$query_iglesia = $conexion->prepare("SELECT * FROM iglesiaspt WHERE id = ?");
$query_iglesia->execute([$id_iglesia]);
$iglesia = $query_iglesia->fetch(PDO::FETCH_ASSOC);

// Obtener feligreses de ESA iglesia
$query_feligreses = $conexion->prepare("SELECT * FROM feligresespt WHERE id_iglesia = ?");
$query_feligreses->execute([$id_iglesia]);
$feligreses = $query_feligreses->fetchAll();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Registro de Feligreses</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .logout-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
    .custom-table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .icono-activo {
        color: #28a745 !important;
        opacity: 1 !important;
    }
    .icono-inactivo {
        color: #dc3545 !important;
        opacity: 0.5 !important;
    }
</style>

<body class="bg-light">
    <a href="logout.php" class="btn btn-danger logout-btn">Cerrar Sesión</a>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Registro  <?= htmlspecialchars($iglesia['nombre']) ?></h1>

        <!-- Sección 1: Buscar por nombre -->
        <div class="card mb-5 shadow">
            <div class="card-body">
                <h2 class="h4 mb-3 text-center">Buscar Feligrés</h2>
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6 mx-auto">
                            <div class="input-group">
                                <input type="text" name="buscar" class="form-control" placeholder="Nombre del feligrés">
                                <button type="submit" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if (!empty($resultados)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered custom-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-4 text-center">Nombre Completo</th>
                                <th class="text-center">Fecha de Nacimiento</th>
                                <th class="col-1 text-center">Bautizo</th>
                                <th class="col-1 text-center">Confirmación</th>
                                <th class="col-1 text-center">Matrimonio</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nombre']) ?></td>
                                <td class="text-center"><?= htmlspecialchars(date('d/m/Y', strtotime($f['fecha_nacimiento']))) ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['bautizo']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['confirmacion']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['matrimonio']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar_feligres.php?id=<?= htmlspecialchars($f['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar_feligres.php?id=<?= htmlspecialchars($f['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Eliminar a <?= htmlspecialchars($f['nombre']) ?>?')">
                                            Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección 2: Formulario de Registro -->
        <div class="card mb-5 shadow">
            <div class="card-body">
                <h2 class="h4 mb-4 text-center">Registrar Nuevo Feligrés</h2>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <center><label class="form-label">Nombre Completo</label></center>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        
                        <div class="col-md-2">
                            <center><label class="form-label">CI</label></center>
                            <input type="number" name="ci" class="form-control">
                        </div>
                        
                        <div class="col-md-2">
                            <center><label class="form-label">Fecha de Nacimiento</label></center>
                            <input type="date" name="fecha_nacimiento" class="form-control" required>
                        </div>
                        
                        <div class="col-md-2">
                            <center><label class="form-label">Pág</label></center>
                            <input type="text" name="pag" class="form-control">
                        </div>
                        
                        <div class="from-grup mb-4">
                            <center><label class="form-label">Sacramentos:</label></center>
                            <div class="gap-4 d-md-flex justify-content-md-center"> <!-- Mejor disposición horizontal -->
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="bautizo" id="bautizo">
                                    <label class="form-check-label" for="bautizo">Bautizo</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="confirmacion" id="confirmacion">
                                    <label class="form-check-label" for="confirmacion">Confirmación</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="matrimonio" id="matrimonio">
                                    <label class="form-check-label" for="matrimonio">Matrimonio</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success px-5" onclick="mostrarGuardado()">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sección 3: Listado de Feligreses -->
        <div class="card shadow">
            <div class="card-body">
                <h2 class="h4 mb-3">Listado de Feligreses</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover custom-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-4 text-center">Nombre Completo</th>
                                <th class="text-center">CI</th>
                                <th class="text-center">Fecha Nacimiento</th>
                                <th class="col-1">Página</th>
                                <th class="col-1">Bautizo</th>
                                <th class="col-1">Confirmación</th>
                                <th class="col-1">Matrimonio</th>
                                <th class="col-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feligreses as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['nombre']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['ci']) ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['fecha_nacimiento']))) ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['pag']) ?? 'N/A' ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['bautizo']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['confirmacion']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td class="text-center"><?= htmlspecialchars($f['matrimonio']) ? '<span style="color: #2ecc71; opacity: 0.5;">✅</span>' : '<span style="color: #e74c3c; opacity: 0.5;">❌</span>' ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar_feligres.php?id=<?= htmlspecialchars($f['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="#" 
                                        class="btn btn-sm btn-danger"
                                        onclick="confirmarEliminacion('<?= $f['id'] ?>', '<?= htmlspecialchars($f['nombre']) ?>')">
                                        Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<script>
function confirmarEliminacion(id, nombre) {
    Swal.fire({
        title: `¿Eliminar a ${nombre}?`,
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',        // rojo pastel
        cancelButtonColor: '#c8a2c8',      // lila pastel
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#fff0f5',             // fondo clarito
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `eliminar_feligres.php?id=${id}`;
        }
    });
}
</script>
<script>
function mostrarGuardado() {
    Swal.fire({
        title: '¡Guardado con éxito!',
        icon: 'success',
        timer: 2500,
        showConfirmButton: false,
        background: '#e6ffe6',              // verde pastel clarito
        iconColor: '#66bb6a',               // verde bonito
        customClass: {
            popup: 'rounded-4'
        }
    });

    // Opcional: si querés que luego se envíe el formulario automáticamente:
    // document.querySelector("form").submit();
}
</script>

<?php if ($no_encontrado) : ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'info',
    title: 'Oops...',
    text: 'No se encontró a "<?= addslashes($_GET['buscar']) ?>"',
    background: '#f3e8ff',  // lila pastel
    iconColor: '#9370db',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    customClass: {
      popup: 'rounded-3'
    }
});
</script>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
