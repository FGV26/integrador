<?php
require_once 'model/Usuario.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

$base_url = 'http://localhost/INTEGRADOR/';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() !== 'cliente') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];

$citaDAO = new CitaDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();

$abogados = $citaDAO->obtenerAbogados();
$tiposDeCasos = $tipoDeCasoDAO->obtenerTodos();

// Mensaje de confirmación de cita
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : '';
unset($_SESSION['mensaje']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Estudio Jurídico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/citas.css?v=<?php echo time(); ?>">
</head>
<style>
    .bg-dark {
        background-color: #000000 !important;
    }
</style>

<body>

    <header class="bg-dark text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-static-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Estudio Jurídico Ortiz y Asociados</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav"></div>
                <a href="index.php" class="btn btn-warning text-dark me-2">Volver</a>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main class="container pt-5">
        <h1>Agendar Cita</h1>
        <form id="citaForm" action="controlador/ControladorCita.php" method="POST">
            <input type="hidden" name="cliente_id" value="<?php echo $usuario->getId(); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre" class="form-control" value="<?php echo $usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno(); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="abogado_id" class="form-label">Abogado</label>
                    <select name="abogado_id" id="abogado_id" class="form-control" required>
                        <option value="">Seleccione un abogado</option>
                        <?php foreach ($abogados as $abogado) : ?>
                            <option value="<?php echo $abogado->getId(); ?>">
                                <?php echo $abogado->getNombre() . ' ' . $abogado->getApellidoPaterno(); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" id="correo" class="form-control" value="<?php echo $usuario->getCorreo(); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" class="form-control" value="<?php echo $usuario->getTelefono(); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="hora" class="form-label">Hora</label>
                    <select name="hora" id="hora" class="form-control" required disabled>
                        <option value="">Seleccione un horario</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="tipo_de_caso_id" class="form-label">Tipo de Caso</label>
                <select name="tipo_de_caso_id" id="tipo_de_caso_id" class="form-control" required>
                    <option value="">Seleccione un tipo de caso</option>
                    <?php foreach ($tiposDeCasos as $tipoDeCaso) : ?>
                        <option value="<?php echo $tipoDeCaso->getId(); ?>">
                            <?php echo $tipoDeCaso->getTipo(); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="mensaje" class="form-label">Mensaje</label>
                <textarea name="mensaje" id="mensaje" class="form-control" rows="4" required></textarea>
            </div>
            <input type="hidden" name="estado" value="pendiente">
            <button type="submit" class="btn btn-primary">Agendar Cita</button>
        </form>
    </main>


    <!-- Modal de Confirmación -->
    <div class="modal fade" id="citaConfirmadaModal" tabindex="-1" aria-labelledby="citaConfirmadaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-voucher">
                <div class="modal-header">
                    <h5 class="modal-title" id="citaConfirmadaModalLabel">Cita Agendada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="voucher-details">
                        <span>Cliente:</span>
                        <p><?php echo $usuario->getNombre() . ' ' . $usuario->getApellidoPaterno(); ?></p>
                    </div>
                    <div class="voucher-details">
                        <span>Abogado:</span>
                        <p id="abogadoNombre"></p>
                    </div>
                    <div class="voucher-details">
                        <span>Fecha:</span>
                        <p id="fechaCita"></p>
                    </div>
                    <div class="voucher-details">
                        <span>Hora:</span>
                        <p id="horaCita"></p>
                    </div>
                    <div class="voucher-details">
                        <span>Tipo de Caso:</span>
                        <p id="tipoCaso"></p>
                    </div>
                    <div class="voucher-details">
                        <span>Mensaje:</span>
                        <p id="mensajeCita"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="submitForm();">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg-dark text-white text-center py-3 mt-3">
        <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo $base_url; ?>src/js/citas.js?v=<?php echo time(); ?>"></script>

</body>
</html>
