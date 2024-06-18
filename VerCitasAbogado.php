<?php
require_once 'model/Usuario.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'abogado') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$base_url = 'http://localhost/INTEGRADOR/';

$citaDAO = new CitaDAO();
$citas = $citaDAO->obtenerCitasPorAbogado($usuario->getId());

$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Programadas - Estudio Jurídico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
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
                            <a class="nav-link" href="InterfazAbogado.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="PerfilAbogado.php">Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="VerCitasAbogado.php">Ver Citas</a>
                        </li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container mt-5 pt-5">
        <h1>Citas Programadas</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Cita</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Tipo de Caso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $index => $cita) : ?>
                    <?php
                        $cliente = $usuarioDAO->obtenerPorId($cita->getClienteId());
                        $tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
                    ?>
                    <tr>
                        <td><?php echo "CITA " . ($index + 1); ?></td>
                        <td><?php echo $cita->getHora(); ?></td>
                        <td><?php echo $cliente->getNombre() . ' ' . $cliente->getApellidoPaterno() . ' ' . $cliente->getApellidoMaterno(); ?></td>
                        <td><?php echo $tipoDeCaso->getTipo(); ?></td>
                        <td>
                            <button class="btn btn-info" disabled>Ver Información</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
