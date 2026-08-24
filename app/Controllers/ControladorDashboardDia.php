<?php
require_once dirname(__DIR__) . '/Repositories/DashboardDAO.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesion no autorizada']);
    exit();
}

$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Fecha no valida']);
    exit();
}

$dashboardDAO = new DashboardDAO();

echo json_encode([
    'ok' => true,
    'data' => $dashboardDAO->obtenerResumenPorDia($fecha),
]);
