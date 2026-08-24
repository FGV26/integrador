<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/CitaDAO.php';
require_once dirname(__DIR__) . '/Repositories/IngresoDAO.php';
require_once dirname(__DIR__) . '/Repositories/PerdidaDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'abogado') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$citaDAO = new CitaDAO();
$ingresoDAO = new IngresoDAO();
$perdidaDAO = new PerdidaDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citaId = $_POST['cita_id'];
    $fechaActual = date('Y-m-d');

    if (isset($_POST['aceptar'])) {
        $resultado = $citaDAO->confirmarCita($citaId);
        $_SESSION['mensaje'] = $resultado ? 'La cita fue aceptada correctamente.' : 'No se pudo aceptar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    } elseif (isset($_POST['terminar'])) {
        $resultado = $citaDAO->terminarCita($citaId);
        if ($resultado) {
            $ingreso = new Ingreso();
            $ingreso->setCitaId($citaId);
            $montoIngreso = rand(100, 250); 
            $ingreso->setMonto($montoIngreso);
            $ingreso->setFecha($fechaActual); 
            $ingresoDAO->crear($ingreso);
        }
        $_SESSION['mensaje'] = $resultado ? 'La cita fue marcada como terminada.' : 'No se pudo actualizar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    } elseif (isset($_POST['cancelar'])) {
        $resultado = $citaDAO->cancelarCitaPorAbogado($citaId);
        if ($resultado) {
            $perdida = new Perdida();
            $perdida->setCitaId($citaId);
            $montoPerdida = rand(100, 250);
            $perdida->setMonto($montoPerdida);
            $perdida->setFecha($fechaActual); 
            $perdidaDAO->crear($perdida);
        }
        $_SESSION['mensaje'] = $resultado ? 'La cita fue cancelada correctamente.' : 'No se pudo cancelar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    }
}
?>
