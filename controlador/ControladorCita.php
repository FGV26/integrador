<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../dao/CitaDAO.php';
require_once '../model/Cita.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clienteId = $_POST['cliente_id'];
    $abogadoId = $_POST['abogado_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora']; // Nueva hora
    $tipoDeCasoId = $_POST['tipo_de_caso_id'];
    $mensaje = $_POST['mensaje'];
    $estado = 'pendiente';

    // Validar si la hora ya está ocupada para la fecha seleccionada
    $citaDAO = new CitaDAO();
    $horasOcupadas = $citaDAO->obtenerCitasPorFecha($fecha);

    if (in_array($hora, $horasOcupadas)) {
        $_SESSION['mensaje'] = 'La hora seleccionada ya está ocupada. Por favor, elija otra hora.';
        header('Location: ../citas.php');
        exit();
    }

    $cita = new Cita();
    $cita->setClienteId($clienteId);
    $cita->setAbogadoId($abogadoId);
    $cita->setFecha($fecha);
    $cita->setHora($hora); // Nueva hora
    $cita->setTipoDeCasoId($tipoDeCasoId);
    $cita->setMensaje($mensaje);
    $cita->setEstado($estado);

    if ($citaDAO->crear($cita)) {
        $_SESSION['mensaje'] = 'Cita agendada correctamente.';
    } else {
        $_SESSION['mensaje'] = 'Error al agendar la cita.';
    }

    header('Location: ../index.php');
    exit();
}

?>
