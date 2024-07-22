<?php
require_once 'dao/DashboardDAO.php';
require_once 'conexiones/conexion.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'administrador') {
    header('Location: login.php');
    exit();
}

$conexion = new Conexion();
$dashboardDAO = new DashboardDAO($conexion->getConnection());

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

// Obtener los valores de ingresos y pérdidas
$totalIngresos = $dashboardDAO->obtenerTotalIngresosPorMes($year, $month);
$totalPerdidas = $dashboardDAO->obtenerTotalPerdidasPorMes($year, $month);
$cantidadIngresos = $dashboardDAO->obtenerCantidadIngresosPorMes($year, $month);
$cantidadPerdidas = $dashboardDAO->obtenerCantidadPerdidasPorMes($year, $month);
$cantidadCitas = $dashboardDAO->obtenerCantidadCitasPorMes($year, $month);

// Obtener datos para los gráficos
$ingresosPorMes = $dashboardDAO->obtenerCantidadIngresosPorAnio($year);
$perdidasPorMes = $dashboardDAO->obtenerCantidadPerdidasPorAnio($year);
$montoIngresosPorMes = $dashboardDAO->obtenerMontoIngresosPorAnio($year);
$montoPerdidasPorMes = $dashboardDAO->obtenerMontoPerdidasPorAnio($year);

// Calcular los porcentajes de ingresos y pérdidas
$totalRegistros = $cantidadIngresos + $cantidadPerdidas;
$porcentajeIngresos = $totalRegistros ? round(($cantidadIngresos / $totalRegistros) * 100, 1) : 0;
$porcentajePerdidas = $totalRegistros ? round(($cantidadPerdidas / $totalRegistros) * 100, 1) : 0;

$base_url = 'http://localhost/INTEGRADOR/';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Estudio Jurídico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.0.7/countUp.min.js"></script>
    <style>
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        .chart-container canvas {
            display: block;
            margin: 0 auto;
        }

        .chart-text {
            font-family: 'Arial', sans-serif;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
    </style>
</head>

<body>
<header class="bg-dark text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Estudio Jurídico Ortiz y Asociados</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="InterfazAdministrador.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="PerfilAdmin.php">Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_abogados.php">Gestión de Abogados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_administradores.php">Gestión de Administradores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestionCitas.php">Gestión de Citas</a>
                        </li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container-fluid mt-5 pt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>Gestión de Citas</h1>
                <form method="GET" action="gestionCitas.php" class="form-inline justify-content-center">
                    <div class="form-group mx-2">
                        <label for="year">Selecciona Año: </label>
                        <select name="year" id="year" class="form-control">
                            <?php for ($i = 2020; $i <= 2030; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php echo ($i == $year) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group mx-2">
                        <label for="month">Selecciona Mes: </label>
                        <select name="month" id="month" class="form-control">
                            <?php for ($m = 1; $m <= 12; $m++) : ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == $month) ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6 text-center">
                <h4>Cantidad de Ganancias: <span id="totalIngresos"><?php echo $totalIngresos . " soles"; ?></span></h4>
            </div>
            <div class="col-md-6 text-center">
                <h4>Cantidad de Pérdidas: <span id="totalPerdidas"><?php echo $totalPerdidas . " soles"; ?></span></h4>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4 text-center">
                <div class="chart-container">
                    <canvas id="ingresosPerdidasChart"></canvas>
                </div>
                <p>Ingresos y Pérdidas</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="chart-container">
                    <canvas id="citasChart"></canvas>
                </div>
                <p>Cantidad de Citas</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <div class="chart-container">
                    <canvas id="lineChartCantidad"></canvas>
                </div>
                <p>Gráfico Lineal de Cantidad de Ingresos y Pérdidas</p>
            </div>
            <div class="col-md-12 text-center">
                <div class="chart-container">
                    <canvas id="lineChartMonto"></canvas>
                </div>
                <p>Gráfico Lineal de Monto de Ingresos y Pérdidas</p>
            </div>
        </div>
    </div>

    <!-- Modales de alerta -->
    <?php if ($cantidadCitas == 0) : ?>
        <div class="modal fade" id="modalCitas" tabindex="-1" aria-labelledby="modalCitasLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCitasLabel">Alerta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        No se encontraron citas en el mes seleccionado.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($totalIngresos == 0 && $totalPerdidas == 0) : ?>
        <div class="modal fade" id="modalIngresosPerdidas" tabindex="-1" aria-labelledby="modalIngresosPerdidasLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalIngresosPerdidasLabel">Alerta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        No se encontraron ingresos ni pérdidas en el mes seleccionado.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="footer bg-dark text-white text-center py-3 mt-3">
        <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ingresosData = <?php echo $porcentajeIngresos; ?>;
            const perdidasData = <?php echo $porcentajePerdidas; ?>;
            const citasData = <?php echo $cantidadCitas; ?>;

            const totalIngresos = <?php echo $totalIngresos; ?>;
            const totalPerdidas = <?php echo $totalPerdidas; ?>;

            const ctxIngresosPerdidas = document.getElementById('ingresosPerdidasChart').getContext('2d');
            const ctxCitas = document.getElementById('citasChart').getContext('2d');
            const ctxLineChartCantidad = document.getElementById('lineChartCantidad').getContext('2d');
            const ctxLineChartMonto = document.getElementById('lineChartMonto').getContext('2d');

            new Chart(ctxIngresosPerdidas, {
                type: 'pie',
                data: {
                    labels: ['Ingresos', 'Pérdidas'],
                    datasets: [{
                        data: [ingresosData, perdidasData],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: '#fff',
                            formatter: (value, ctx) => {
                                return value.toFixed(1) + '%'; // Redondear a un decimal
                            },
                            font: {
                                weight: 'bold',
                                size: 24,
                            }
                        }
                    }
                }
            });

            new Chart(ctxCitas, {
                type: 'pie',
                data: {
                    labels: ['Citas'],
                    datasets: [{
                        data: [citasData, 100 - citasData],
                        backgroundColor: ['#007bff', '#007bff']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            color: '#fff',
                            formatter: (value, ctx) => {
                                return ctx.chart.data.labels[ctx.dataIndex] === 'Citas' ? value.toFixed(0) + ' citas' : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 24,
                            }
                        }
                    }
                }
            });

            new Chart(ctxLineChartCantidad, {
                type: 'line',
                data: {
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    datasets: [{
                            label: 'Ingresos',
                            data: [<?php echo implode(',', $ingresosPorMes); ?>],
                            borderColor: '#28a745',
                            fill: false,
                            lineTension: 0.1
                        },
                        {
                            label: 'Pérdidas',
                            data: [<?php echo implode(',', $perdidasPorMes); ?>],
                            borderColor: '#dc3545',
                            fill: false,
                            lineTension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            new Chart(ctxLineChartMonto, {
                type: 'line',
                data: {
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    datasets: [{
                            label: 'Ingresos',
                            data: [<?php echo implode(',', $montoIngresosPorMes); ?>],
                            borderColor: '#28a745',
                            fill: false,
                            lineTension: 0.1
                        },
                        {
                            label: 'Pérdidas',
                            data: [<?php echo implode(',', $montoPerdidasPorMes); ?>],
                            borderColor: '#dc3545',
                            fill: false,
                            lineTension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            <?php if ($cantidadCitas == 0) : ?>
                var modalCitas = new bootstrap.Modal(document.getElementById('modalCitas'));
                modalCitas.show();
            <?php endif; ?>

            <?php if ($totalIngresos == 0 && $totalPerdidas == 0) : ?>
                var modalIngresosPerdidas = new bootstrap.Modal(document.getElementById('modalIngresosPerdidas'));
                modalIngresosPerdidas.show();
            <?php endif; ?>
        });
    </script>
</body>

</html>
