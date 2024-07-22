<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../model/Usuario.php';
require_once '../dao/CitaDAO.php';
require_once '../dao/IngresoDAO.php';
require_once '../dao/PerdidaDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'abogado') {
    header('Location: ../login.php');
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
        header("Location: ../informacionAbogado.php?id=$citaId");
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
        header("Location: ../informacionAbogado.php?id=$citaId");
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
        header("Location: ../informacionAbogado.php?id=$citaId");
        exit();
    }
}
?>
