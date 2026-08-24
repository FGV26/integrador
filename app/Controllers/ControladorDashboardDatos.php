<?php
require_once dirname(__DIR__) . '/Repositories/DashboardDAO.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesion no autorizada']);
    exit();
}

function dashboard_int_param(string $name, int $default, int $min, int $max): int
{
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        return $default;
    }

    return max($min, min($max, $value));
}

function dashboard_mes(int $mes): string
{
    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $meses[$mes] ?? '';
}

function dashboard_calendario_mes(DashboardDAO $dashboardDAO, int $year, int $month): array
{
    $primerDia = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $inicioCalendario = $primerDia->modify('-' . ((int) $primerDia->format('N') - 1) . ' days');
    $diasConCitas = $dashboardDAO->obtenerDiasConCitasPorMes($year, $month);
    $dias = [];

    for ($i = 0; $i < 42; $i++) {
        $dia = $inicioCalendario->modify('+' . $i . ' days');
        $fecha = $dia->format('Y-m-d');
        $dias[] = [
            'fecha' => $fecha,
            'numero' => (int) $dia->format('j'),
            'actual' => $dia->format('m') === sprintf('%02d', $month),
            'hoy' => $fecha === date('Y-m-d'),
            'actividad' => isset($diasConCitas[$fecha]),
        ];
    }

    return [
        'year' => $year,
        'month' => $month,
        'titulo' => dashboard_mes($month) . ' ' . $year,
        'dias' => $dias,
    ];
}

function dashboard_estado_citas_payload(array $estados, int $year, int $month): array
{
    $meta = [
        'pendiente' => ['label' => 'Pendientes', 'color' => '#d9b84f', 'hoverColor' => '#e7ca67'],
        'confirmada' => ['label' => 'Confirmadas', 'color' => '#6f8192', 'hoverColor' => '#8193a4'],
        'cancelada' => ['label' => 'Canceladas', 'color' => '#c45f52', 'hoverColor' => '#d57264'],
        'terminado' => ['label' => 'Terminadas', 'color' => '#2f6f5e', 'hoverColor' => '#3d846f'],
    ];
    $now = new DateTimeImmutable('now', new DateTimeZone('America/Lima'));
    $isCurrentMonth = (int) $now->format('Y') === $year && (int) $now->format('n') === $month;
    $keys = $isCurrentMonth ? ['pendiente', 'confirmada', 'cancelada', 'terminado'] : ['cancelada', 'terminado'];
    $total = 0;

    foreach ($keys as $key) {
        $total += (int) ($estados[$key] ?? 0);
    }

    $items = [];
    foreach ($keys as $key) {
        $value = (int) ($estados[$key] ?? 0);
        if (!$isCurrentMonth && $value <= 0) {
            continue;
        }

        $items[] = [
            'key' => $key,
            'label' => $meta[$key]['label'],
            'value' => $value,
            'percent' => $total > 0 ? round(($value / $total) * 100, 1) : 0,
            'color' => $meta[$key]['color'],
            'hoverColor' => $meta[$key]['hoverColor'],
        ];
    }

    return [
        'items' => $items,
        'labels' => array_column($items, 'label'),
        'estados' => array_column($items, 'value'),
        'colors' => array_column($items, 'color'),
        'hoverColors' => array_column($items, 'hoverColor'),
        'total' => $total,
        'showAllStates' => $isCurrentMonth,
    ];
}

$dashboardDAO = new DashboardDAO();
$accion = $_GET['accion'] ?? '';
$year = dashboard_int_param('year', (int) date('Y'), 2020, 2100);
$month = dashboard_int_param('month', (int) date('n'), 1, 12);

switch ($accion) {
    case 'citas_anio':
        $citas = $dashboardDAO->obtenerCantidadCitasPorAnio($year);
        echo json_encode([
            'ok' => true,
            'data' => [
                'year' => $year,
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                'citas' => $citas,
                'total' => array_sum($citas),
            ],
        ]);
        break;

    case 'balance_anio':
        echo json_encode([
            'ok' => true,
            'data' => [
                'year' => $year,
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                'ingresos' => $dashboardDAO->obtenerMontoIngresosPorAnio($year),
                'perdidas' => $dashboardDAO->obtenerMontoPerdidasPorAnio($year),
            ],
        ]);
        break;

    case 'estados_mes':
        $estados = $dashboardDAO->obtenerCitasPorEstadoPorMes($year, $month);
        $payload = dashboard_estado_citas_payload($estados, $year, $month);
        echo json_encode([
            'ok' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'titulo' => dashboard_mes($month) . ' ' . $year,
                'labels' => $payload['labels'],
                'estados' => $payload['estados'],
                'colors' => $payload['colors'],
                'hoverColors' => $payload['hoverColors'],
                'items' => $payload['items'],
                'total' => $payload['total'],
                'showAllStates' => $payload['showAllStates'],
            ],
        ]);
        break;

    case 'balance_mes':
        $balance = $dashboardDAO->obtenerBalanceDiarioPorMes($year, $month);
        echo json_encode([
            'ok' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'monthName' => dashboard_mes($month),
                'labels' => $balance['labels'],
                'ingresos' => $balance['ingresos'],
                'perdidas' => $balance['perdidas'],
                'totalIngresos' => $dashboardDAO->obtenerTotalIngresosPorMes($year, $month),
                'totalPerdidas' => $dashboardDAO->obtenerTotalPerdidasPorMes($year, $month),
            ],
        ]);
        break;

    case 'calendario_mes':
        echo json_encode([
            'ok' => true,
            'data' => dashboard_calendario_mes($dashboardDAO, $year, $month),
        ]);
        break;

    default:
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Accion no valida']);
}
