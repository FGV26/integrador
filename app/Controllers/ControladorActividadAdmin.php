<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/ActividadDAO.php';
require_once dirname(__DIR__) . '/Repositories/AuditLogDAO.php';

date_default_timezone_set('America/Lima');
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesion no autorizada']);
    exit();
}

function actividad_fecha_valida(?string $value): ?string
{
    if (!is_string($value) || trim($value) === '') {
        return null;
    }

    $fecha = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    if (!$fecha || $fecha->format('Y-m-d') !== $value) {
        return null;
    }

    return $value;
}

function actividad_fecha_param(?string $value): string
{
    return actividad_fecha_valida($value) ?? actividad_fecha_hoy();
}

function actividad_fecha_hoy(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d');
}

function actividad_mes_nombre(int $month): string
{
    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $meses[$month] ?? '';
}

function actividad_calendario_mes(ActividadDAO $actividadDAO, int $year, int $month): array
{
    $primerDia = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $inicio = $primerDia->modify('-' . ((int) $primerDia->format('N') - 1) . ' days');
    $fin = $inicio->modify('+41 days');
    $conteos = $actividadDAO->obtenerConteoNotasPorRango($inicio->format('Y-m-d'), $fin->format('Y-m-d'));
    $recordatorios = $actividadDAO->obtenerConteoRecordatoriosPorRango($inicio->format('Y-m-d'), $fin->format('Y-m-d'));
    $hoy = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d');
    $dias = [];

    for ($i = 0; $i < 42; $i++) {
        $dia = $inicio->modify('+' . $i . ' days');
        $fecha = $dia->format('Y-m-d');
        $dias[] = [
            'fecha' => $fecha,
            'numero' => (int) $dia->format('j'),
            'actual' => $dia->format('m') === sprintf('%02d', $month),
            'hoy' => $fecha === $hoy,
            'notas' => $conteos[$fecha] ?? 0,
            'recordatorios' => $recordatorios[$fecha] ?? 0,
        ];
    }

    return [
        'year' => $year,
        'month' => $month,
        'titulo' => actividad_mes_nombre($month) . ' ' . $year,
        'dias' => $dias,
    ];
}

$actividadDAO = new ActividadDAO();
$auditLogDAO = new AuditLogDAO();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'dia';

try {
    if ($accion === 'crear') {
        $fechaSeleccionada = actividad_fecha_param($_POST['fecha'] ?? actividad_fecha_hoy());
        $tipo = $_POST['tipo'] ?? 'nota';
        $tipoNormalizado = $tipo === 'recordatorio' ? 'recordatorio' : 'nota';
        $fecha = $fechaSeleccionada;
        if ($tipoNormalizado === 'nota' && $fechaSeleccionada < actividad_fecha_hoy()) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'No se pueden crear notas en dias pasados.']);
            exit();
        }

        if ($tipoNormalizado === 'recordatorio') {
            $fechaRecordatorio = actividad_fecha_valida($_POST['fecha_recordatorio'] ?? null);
            if ($fechaRecordatorio === null) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Selecciona una fecha valida para el recordatorio.']);
                exit();
            }

            if ($fechaRecordatorio < actividad_fecha_hoy()) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'El recordatorio no puede tener una fecha pasada.']);
                exit();
            }

            $fecha = $fechaRecordatorio;
        }
        $titulo = $_POST['titulo'] ?? '';
        $detalle = $_POST['detalle'] ?? '';
        $administradorId = (int) $_SESSION['usuario']->getId();
        $result = $actividadDAO->crear($administradorId, $fecha, $tipo, $titulo, $detalle, 'administrador');

        if (!$result['ok']) {
            http_response_code(422);
            echo json_encode($result);
            exit();
        }

        $auditLogDAO->registrar(
            $administradorId,
            'crear',
            'actividad',
            (int) $result['id'],
            $tipoNormalizado === 'recordatorio'
                ? 'Se creo un recordatorio: ' . trim($titulo)
                : 'Se creo una nota: ' . trim($titulo)
        );

        echo json_encode([
            'ok' => true,
            'message' => $tipoNormalizado === 'recordatorio' ? 'Recordatorio creado.' : 'Nota creada.',
            'data' => [
                'fecha' => $fechaSeleccionada,
                'items' => $actividadDAO->obtenerPorDia($fechaSeleccionada),
                'logs' => $auditLogDAO->obtenerPorDia($fechaSeleccionada),
                'recordatorios' => $actividadDAO->obtenerRecordatoriosProximos(),
            ],
        ]);
        exit();
    }

    if ($accion === 'eliminar_nota') {
        $administradorId = (int) $_SESSION['usuario']->getId();
        $notaId = (int) ($_POST['id'] ?? 0);
        $result = $actividadDAO->eliminarNota($notaId, $administradorId);

        if (!$result['ok']) {
            http_response_code(422);
            echo json_encode($result);
            exit();
        }

        $fechaNota = $result['actividad']['fecha'];
        $auditLogDAO->registrar(
            $administradorId,
            'eliminar',
            'actividad',
            $notaId,
            'Se elimino una nota: ' . trim($result['actividad']['titulo'])
        );

        echo json_encode([
            'ok' => true,
            'message' => 'Nota eliminada.',
            'data' => [
                'fecha' => $fechaNota,
                'items' => $actividadDAO->obtenerPorDia($fechaNota),
                'logs' => $auditLogDAO->obtenerPorDia($fechaNota),
                'recordatorios' => $actividadDAO->obtenerRecordatoriosProximos(),
            ],
        ]);
        exit();
    }

    if ($accion === 'calendario_mes') {
        $year = max(2020, min(2100, (int) ($_GET['year'] ?? date('Y'))));
        $month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
        echo json_encode(['ok' => true, 'data' => actividad_calendario_mes($actividadDAO, $year, $month)]);
        exit();
    }

    $fecha = actividad_fecha_param($_GET['fecha'] ?? actividad_fecha_hoy());
    echo json_encode([
        'ok' => true,
        'data' => [
            'fecha' => $fecha,
            'items' => $actividadDAO->obtenerPorDia($fecha),
            'logs' => $auditLogDAO->obtenerPorDia($fecha),
            'recordatorios' => $actividadDAO->obtenerRecordatoriosProximos(),
        ],
    ]);
} catch (Throwable $exception) {
    error_log('Actividad administrativa: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'No se pudo procesar la actividad.']);
}
