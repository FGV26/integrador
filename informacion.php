<?php
require_once 'model/Usuario.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/UsuarioDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'cliente') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$base_url = 'http://localhost/INTEGRADOR/';

if (!isset($_GET['id'])) {
    header('Location: VerCitas.php');
    exit();
}

$citaDAO = new CitaDAO();
$cita = $citaDAO->obtenerPorId($_GET['id']);

if (!$cita) {
    header('Location: VerCitas.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$abogado = $usuarioDAO->obtenerPorId($cita->getAbogadoId());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    if ($cita->getEstado() == 'pendiente') {
        $citaDAO->cancelarCita($cita->getId());
        header('Location: VerCitas.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Cita - Estudio Jurídico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/admin.css">
    <style>
        .button-group {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header class="bg-dark text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Estudio Jurídico Ortiz y Asociados</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="PerfilCliente.php">Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VerCitas.php">Ver Citas</a>
                        </li>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-warning text-dark me-2">Volver</a>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main class="container pt-5 mt-5">
        <h1>Datos de tu Cita</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detalles de la Cita</h5>
                <p class="card-text"><strong>Cliente:</strong> <?php echo $usuario->getNombre() . ' ' . $usuario->getApellidoPaterno(); ?></p>
                <p class="card-text"><strong>Abogado:</strong> <?php echo $abogado->getNombre() . ' ' . $abogado->getApellidoPaterno(); ?></p>
                <p class="card-text"><strong>Fecha:</strong> <?php echo $cita->getFecha(); ?></p>
                <p class="card-text"><strong>Hora:</strong> <?php echo $cita->getHora(); ?></p>
                <p class="card-text"><strong>Tipo de Caso:</strong> <?php echo $cita->getTipoDeCasoId(); ?></p> <!-- Cambiar para mostrar el nombre del tipo de caso -->
                <p class="card-text"><strong>Mensaje:</strong> <?php echo $cita->getMensaje(); ?></p>
                <div class="button-group">
                    <a href="VerCitas.php" class="btn btn-secondary">Volver</a>
                    <?php if ($cita->getEstado() == 'pendiente'): ?>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancelar Cita</button>
                    <?php else: ?>
                        <p class="text-success">Su cita ha sido aceptada</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirmar Cancelación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas cancelar esta cita?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="confirm_cancel" value="1">
                        <button type="submit" class="btn btn-danger">Sí, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

