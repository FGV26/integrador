<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/CitaDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() !== 'cliente') {
    header('Location: ../IniciarSesion.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Cliente/Citas.php');
    exit();
}

$avisoId = isset($_POST['aviso_id']) ? (int) $_POST['aviso_id'] : 0;
$clienteId = (int) $_SESSION['usuario']->getId();
$citaDAO = new CitaDAO();

$resultado = $avisoId > 0 && $citaDAO->ocultarAvisoCliente($avisoId, $clienteId);
$_SESSION['mensaje'] = $resultado ? 'Aviso retirado de tu vista.' : 'No se pudo retirar el aviso.';
$_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';

header('Location: ../Cliente/Citas.php');
exit();
