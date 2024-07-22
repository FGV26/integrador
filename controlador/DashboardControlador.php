<?php
require_once dirname(__DIR__) . '/dao/DashboardDAO.php';

class DashboardControlador {
    private $dashboardDAO;

    public function __construct() {
        $this->dashboardDAO = new DashboardDAO();
    }

    public function obtenerAniosDisponibles() {
        return $this->dashboardDAO->obtenerAniosDisponibles();
    }

    public function obtenerDatosFiltrados($anio, $mes) {
        return $this->dashboardDAO->obtenerDatosFiltrados($anio, $mes);
    }
}
?>
