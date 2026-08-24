<?php
require_once 'dao/CitaDAO.php';

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $citaDAO = new CitaDAO();
    $horasOcupadas = $citaDAO->obtenerCitasPorFecha($fecha);

    $horariosDisponibles = [
        '09:30:00', '10:30:00', '11:30:00', '12:30:00',
        '14:00:00', '15:00:00', '16:00:00', '17:00:00'
    ];

    $horasDisponibles = array_diff($horariosDisponibles, $horasOcupadas);

    echo json_encode(array_values($horasDisponibles));
}
?>
