<?php
require_once dirname(__DIR__) . '/Repositories/CitaDAO.php';
require_once dirname(__DIR__) . '/Models/Cita.php';
session_start();

date_default_timezone_set('America/Lima');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = $_POST['cliente_id'] ?? '';
    $abogadoId = $_POST['abogado_id'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $tipoDeCasoId = $_POST['tipo_de_caso_id'] ?? '';
    $mensaje = trim($_POST['mensaje'] ?? '');
    $contrasenaConfirmacion = $_POST['contrasena_confirmacion'] ?? '';
    $estado = 'pendiente';

    if ($clienteId === '' || $abogadoId === '' || $fecha === '' || $hora === '' || $tipoDeCasoId === '' || $mensaje === '') {
        $_SESSION['mensaje'] = 'Completa todos los pasos antes de confirmar la cita.';
        $_SESSION['mensaje_tipo'] = 'warning';
        header('Location: ../Cliente/SolicitarCita.php');
        exit();
    }

    $usuarioSesion = $_SESSION['usuario'] ?? null;
    if (!$usuarioSesion || !password_verify($contrasenaConfirmacion, $usuarioSesion->getContrasena())) {
        $_SESSION['mensaje'] = 'La contrasena de validacion es incorrecta.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: ../Cliente/SolicitarCita.php');
        exit();
    }

    $hoy = new DateTimeImmutable('today');
    $fechasPermitidas = [];
    for ($offset = 1; $offset <= 3; $offset++) {
        $fechasPermitidas[] = $hoy->modify('+' . $offset . ' day')->format('Y-m-d');
    }

    if (!in_array($fecha, $fechasPermitidas, true)) {
        $_SESSION['mensaje'] = 'Selecciona uno de los proximos tres dias disponibles.';
        $_SESSION['mensaje_tipo'] = 'warning';
        header('Location: ../Cliente/SolicitarCita.php');
        exit();
    }

    $citaDAO = new CitaDAO();
    $horasOcupadas = $citaDAO->obtenerCitasPorFecha($fecha);

    if (in_array($hora, $horasOcupadas, true)) {
        $_SESSION['mensaje'] = 'La hora seleccionada ya esta ocupada. Por favor, elige otra hora.';
        $_SESSION['mensaje_tipo'] = 'warning';
        header('Location: ../Cliente/SolicitarCita.php');
        exit();
    }

    $cita = new Cita();
    $cita->setClienteId($clienteId);
    $cita->setAbogadoId($abogadoId);
    $cita->setFecha($fecha);
    $cita->setHora($hora);
    $cita->setTipoDeCasoId($tipoDeCasoId);
    $cita->setMensaje($mensaje);
    $cita->setEstado($estado);

    if ($citaDAO->crear($cita)) {
        $_SESSION['mensaje'] = 'Cita agendada correctamente.';
        $_SESSION['mensaje_tipo'] = 'success';
        header('Location: ../Cliente/Citas.php');
        exit();
    }

    $_SESSION['mensaje'] = 'Error al agendar la cita.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ../Cliente/SolicitarCita.php');
    exit();
}
?>
