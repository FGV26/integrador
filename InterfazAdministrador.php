<?php
require_once 'model/Usuario.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'administrador') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$base_url = 'http://localhost/INTEGRADOR/';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Estudio Jurídico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/admin.css">
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
                            <a class="nav-link active" aria-current="page" href="InterfazAdministrador.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="PerfilAdmin.php">Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_abogados.php">Gestión de Abogados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_administradores.php">Gestión de Administradores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Gestión de Citas</a>
                        </li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row flex-grow-1">
            <div class="col-md-2 sidebar d-flex flex-column justify-content-center align-items-start">
                <a href="gestion_abogados.php">Gestión de Abogados</a>
                <a href="gestion_administradores.php">Gestión de Administrador</a>
                <a href="#">Gestión de Citas</a>
            </div>
            <div class="col-md-10 main-content text-center d-flex flex-column justify-content-center align-items-center">
                <div class="profile-img">
                    <img src="<?php echo $base_url; ?>src/img/admin.png" alt="Administrador">
                </div>
                <h2>Bienvenido Administrador</h2>
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