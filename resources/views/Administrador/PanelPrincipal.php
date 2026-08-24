<?php
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__, 3) . '/app/Repositories/DashboardDAO.php';
require_once dirname(__DIR__, 3) . '/config/app.php';
require_once __DIR__ . '/config/layout.php';

function admin_panel_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_mes(int $mes): string
{
    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $meses[$mes] ?? '';
}

function admin_fecha_espanol(DateTimeInterface $fecha): string
{
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    return $dias[(int) $fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . strtolower(admin_mes((int) $fecha->format('n'))) . ' de ' . $fecha->format('Y');
}

function admin_estado_citas_payload(array $estados, int $year, int $month): array
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
        'values' => array_column($items, 'value'),
        'colors' => array_column($items, 'color'),
        'hoverColors' => array_column($items, 'hoverColor'),
        'total' => $total,
        'showAllStates' => $isCurrentMonth,
    ];
}

$base_url = app_base_url();
$year = max(2020, min(2100, (int) ($_GET['year'] ?? date('Y'))));
$month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
$usuarioDAO = new UsuarioDAO();
$dashboardDAO = new DashboardDAO();

$totalClientes = $usuarioDAO->contarPorRol('cliente');
$clientesActivos = $usuarioDAO->contarActivosPorRol('cliente');
$totalAbogados = $usuarioDAO->contarPorRol('abogado');
$totalAdministradores = $usuarioDAO->contarPorRol('administrador');
$totalCitas = $dashboardDAO->obtenerTotalCitas();
$totalIngresos = $dashboardDAO->obtenerTotalIngresosPorMes($year, $month);
$totalPerdidas = $dashboardDAO->obtenerTotalPerdidasPorMes($year, $month);
$citasPorMes = $dashboardDAO->obtenerCantidadCitasPorAnio($year);
$totalCitasAnio = array_sum($citasPorMes);
$ingresosPorMes = $dashboardDAO->obtenerMontoIngresosPorAnio($year);
$perdidasPorMes = $dashboardDAO->obtenerMontoPerdidasPorAnio($year);
$balanceDiario = $dashboardDAO->obtenerBalanceDiarioPorMes($year, $month);
$citasPorEstado = $dashboardDAO->obtenerCitasPorEstadoPorMes($year, $month);
$estadoCitasPayload = admin_estado_citas_payload($citasPorEstado, $year, $month);
$totalCitasEstadoMes = $estadoCitasPayload['total'];

$adminNombre = trim($_SESSION['usuario']->getNombre() . ' ' . $_SESSION['usuario']->getApellidoPaterno());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard administrativo - Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo admin_panel_h($base_url); ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo admin_panel_h($base_url); ?>assets/css/administrador-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="admin-page">
<?php render_administrador_header($base_url, 'inicio'); ?>
<main class="admin-main">
    <div class="admin-shell">
        <section class="admin-panel-top">
            <article class="admin-hero-card admin-clock">
                <p class="admin-eyebrow">Panel administrativo</p>
                <div class="admin-clock__time" id="adminClock"><?php echo date('H:i:s'); ?></div>
                <p class="admin-clock__date"><?php echo admin_panel_h(admin_fecha_espanol(new DateTimeImmutable())); ?></p>
            </article>
            <article class="admin-clock-metric">
                <div class="admin-metric__icon"><i class="bi bi-people"></i></div>
                <div><span>Clientes activos</span><strong><?php echo $clientesActivos; ?></strong></div>
            </article>
            <article class="admin-clock-metric">
                <div class="admin-metric__icon"><i class="bi bi-briefcase"></i></div>
                <div><span>Abogados</span><strong><?php echo $totalAbogados; ?></strong></div>
            </article>
            <article class="admin-clock-metric">
                <div class="admin-metric__icon"><i class="bi bi-shield-lock"></i></div>
                <div><span>Administradores</span><strong><?php echo $totalAdministradores; ?></strong></div>
            </article>
            <article class="admin-clock-metric">
                <div class="admin-metric__icon"><i class="bi bi-calendar-check"></i></div>
                <div><span>Citas</span><strong><?php echo $totalCitas; ?></strong></div>
            </article>
        </section>

        <section class="admin-dashboard-grid">
            <article class="admin-widget">
                <div class="admin-widget__header">
                    <div>
                        <p class="admin-eyebrow">Actividad anual</p>
                        <h2>Citas registradas</h2>
                        <span class="admin-chart-total" id="citasAnioMeta"><strong><?php echo (int) $totalCitasAnio; ?></strong> citas en <?php echo (int) $year; ?></span>
                    </div>
                    <div class="admin-filter" data-chart-filter>
                        <select id="citasYear" aria-label="Anio de citas">
                            <?php for ($anio = (int) date('Y') - 4; $anio <= (int) date('Y') + 2; $anio++) : ?><option value="<?php echo $anio; ?>" <?php echo $anio === $year ? 'selected' : ''; ?>><?php echo $anio; ?></option><?php endfor; ?>
                        </select>
                        <button class="admin-button admin-button--gold admin-button--small" type="button" id="aplicarCitasYear">Aplicar</button>
                    </div>
                </div>
                <div class="admin-chart"><canvas id="chartCitas"></canvas></div>
            </article>
            <article class="admin-widget">
                <div class="admin-widget__header">
                    <div><p class="admin-eyebrow">Distribucion</p><h2>Estado de citas</h2></div>
                    <span class="admin-state-total" id="estadosTotal"><strong><?php echo (int) $totalCitasEstadoMes; ?></strong> citas del mes</span>
                </div>
                <div class="admin-state-month">
                    <button type="button" class="admin-icon-button" id="estadoPrev" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                    <h3 id="estadoMesTitle"><?php echo admin_panel_h(admin_mes($month) . ' ' . $year); ?></h3>
                    <button type="button" class="admin-icon-button" id="estadoNext" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                </div>
                <p class="admin-state-note" id="estadoMesNote"><?php echo (int) $totalCitasEstadoMes; ?> citas del mes equivalen al 100%.</p>
                <div class="admin-chart admin-state-chart" id="estadoChartWrap"><canvas id="chartEstados"></canvas></div>
                <div class="admin-state-legend" id="estadoLegend"></div>
            </article>
        </section>

        <section class="admin-finance-grid">
            <article class="admin-widget admin-finance-summary">
                <div class="admin-widget__header">
                    <div><p class="admin-eyebrow">Balance financiero</p><h2>Ingresos y perdidas</h2></div>
                </div>
                <div class="admin-metrics admin-metrics--finance">
                    <article class="admin-metric"><div class="admin-metric__icon"><i class="bi bi-arrow-up-right"></i></div><div><span id="balanceIngresosLabel">Ingresos de <?php echo admin_panel_h(admin_mes($month)); ?></span><strong id="balanceTotalIngresos">S/ <?php echo number_format($totalIngresos, 2); ?></strong></div></article>
                    <article class="admin-metric"><div class="admin-metric__icon"><i class="bi bi-arrow-down-right"></i></div><div><span id="balancePerdidasLabel">Perdidas de <?php echo admin_panel_h(admin_mes($month)); ?></span><strong id="balanceTotalPerdidas">S/ <?php echo number_format($totalPerdidas, 2); ?></strong></div></article>
                </div>
                <p class="admin-chart-note<?php echo $totalIngresos <= 0 && $totalPerdidas <= 0 ? '' : ' d-none'; ?>" id="balanceEmptyNote"><i class="bi bi-info-circle"></i> No hay ingresos ni perdidas registradas para este mes.</p>
            </article>
            <article class="admin-widget admin-finance-chart">
                <div class="admin-chart-toolbar">
                    <p class="admin-chart-title">Movimiento por mes</p>
                    <div class="admin-filter admin-filter--compact">
                        <select id="balanceYear" aria-label="Anio de balance">
                            <?php for ($anio = (int) date('Y') - 4; $anio <= (int) date('Y') + 2; $anio++) : ?><option value="<?php echo $anio; ?>" <?php echo $anio === $year ? 'selected' : ''; ?>><?php echo $anio; ?></option><?php endfor; ?>
                        </select>
                        <button class="admin-button admin-button--gold admin-button--small" type="button" id="aplicarBalanceYear">Aplicar</button>
                    </div>
                </div>
                <div class="admin-chart admin-chart--compact"><canvas id="chartBalance"></canvas></div>
            </article>
            <article class="admin-widget admin-finance-chart">
                <div class="admin-chart-toolbar">
                    <p class="admin-chart-title">Movimiento diario</p>
                    <div class="admin-month-jump">
                        <span>Saltar a</span>
                        <select id="balanceMonthJump" aria-label="Saltar a mes">
                            <?php for ($mesItem = 1; $mesItem <= 12; $mesItem++) : ?><option value="<?php echo $mesItem; ?>" <?php echo $mesItem === $month ? 'selected' : ''; ?>><?php echo admin_panel_h(admin_mes($mesItem)); ?></option><?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="admin-daily-nav">
                    <button type="button" class="admin-icon-button" id="balanceDayPrev" aria-label="Dia anterior"><i class="bi bi-chevron-left"></i></button>
                    <h3 id="balanceDayHeading">Dia seleccionado</h3>
                    <button type="button" class="admin-icon-button" id="balanceDayNext" aria-label="Dia siguiente"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="admin-daily-insight">
                    <div class="admin-daily-cards">
                        <article class="admin-daily-card admin-daily-card--income">
                            <span id="balanceDayLabel">Ingresos diarios</span>
                            <strong id="balanceDayIngresos">S/ 0.00</strong>
                            <small>Ingresos</small>
                        </article>
                        <article class="admin-daily-card admin-daily-card--loss">
                            <span>Perdida diaria</span>
                            <strong id="balanceDayPerdidas">S/ 0.00</strong>
                            <small>Perdidas</small>
                        </article>
                    </div>
                </div>
            </article>
        </section>

    </div>
</main>
<?php render_administrador_footer(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const gold = '#d9b84f';
const ink = '#111820';
const muted = '#9d9487';
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6d6559';
const dashboardDataUrl = '<?php echo admin_panel_h($base_url); ?>Administrador/DashboardDatos.php';
const monthLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const moneyFormatter = new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const initialEstadoItems = <?php echo json_encode($estadoCitasPayload['items']); ?>;
const chartCitas = new Chart(document.getElementById('chartCitas'), {type:'line',data:{labels:monthLabels,datasets:[{label:'Citas registradas',data:<?php echo json_encode($citasPorMes); ?>,borderColor:gold,backgroundColor:'rgba(217,184,79,.16)',fill:true,tension:.35,pointBackgroundColor:ink,pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:4}]},options:{maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true}}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});
const chartEstados = new Chart(document.getElementById('chartEstados'), {type:'doughnut',data:{labels:initialEstadoItems.map((item) => item.label),datasets:[{data:initialEstadoItems.map((item) => item.value),backgroundColor:initialEstadoItems.map((item) => item.color),hoverBackgroundColor:initialEstadoItems.map((item) => item.hoverColor),borderColor:'#ffffff',borderWidth:3}]},options:{maintainAspectRatio:false,cutout:'68%',plugins:{legend:{display:false},tooltip:{callbacks:{label:(context)=>`${context.label}: ${context.raw} citas`}}}}});
const chartBalance = new Chart(document.getElementById('chartBalance'), {type:'bar',data:{labels:monthLabels,datasets:[{label:'Ingresos',data:<?php echo json_encode($ingresosPorMes); ?>,backgroundColor:gold,borderRadius:4},{label:'Perdidas',data:<?php echo json_encode($perdidasPorMes); ?>,backgroundColor:muted,borderRadius:4}]},options:{maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true}}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}});
const clock = document.getElementById('adminClock');
setInterval(() => { clock.textContent = new Intl.DateTimeFormat('es-PE',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date()); }, 1000);
const emptyNote = document.getElementById('balanceEmptyNote');
const citasAnioMeta = document.getElementById('citasAnioMeta');
const balanceTotalIngresos = document.getElementById('balanceTotalIngresos');
const balanceTotalPerdidas = document.getElementById('balanceTotalPerdidas');
const balanceIngresosLabel = document.getElementById('balanceIngresosLabel');
const balancePerdidasLabel = document.getElementById('balancePerdidasLabel');
const balanceDayHeading = document.getElementById('balanceDayHeading');
const balanceDayLabel = document.getElementById('balanceDayLabel');
const balanceDayIngresos = document.getElementById('balanceDayIngresos');
const balanceDayPerdidas = document.getElementById('balanceDayPerdidas');
const balanceMonthJump = document.getElementById('balanceMonthJump');
const estadoMesTitle = document.getElementById('estadoMesTitle');
const estadosTotal = document.getElementById('estadosTotal');
const estadoMesNote = document.getElementById('estadoMesNote');
const estadoLegend = document.getElementById('estadoLegend');
const estadoChartWrap = document.getElementById('estadoChartWrap');
let estadoYear = <?php echo (int) $year; ?>;
let estadoMonth = <?php echo (int) $month; ?>;
let balanceDailyData = {
    labels: <?php echo json_encode($balanceDiario['labels']); ?>,
    ingresos: <?php echo json_encode($balanceDiario['ingresos']); ?>,
    perdidas: <?php echo json_encode($balanceDiario['perdidas']); ?>,
    monthName: '<?php echo admin_panel_h(admin_mes($month)); ?>',
    month: <?php echo (int) $month; ?>,
    year: <?php echo (int) $year; ?>
};
let selectedBalanceDay = Math.min(balanceDailyData.labels.length, <?php echo (int) date('j'); ?>);

async function fetchDashboardData(params) {
    const response = await fetch(dashboardDataUrl + '?' + new URLSearchParams(params).toString(), {
        headers: { 'Accept': 'application/json' }
    });
    const payload = await response.json();
    if (!payload.ok) {
        throw new Error(payload.message || 'No se pudo consultar la informacion');
    }
    return payload.data;
}

function updateBalanceNote(totalIngresos, totalPerdidas) {
    emptyNote.classList.toggle('d-none', Number(totalIngresos) > 0 || Number(totalPerdidas) > 0);
}

function formatBalanceDate(day) {
    const weekdays = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    const date = new Date(balanceDailyData.year, balanceDailyData.month - 1, day);
    return `${weekdays[date.getDay()]} ${day} de ${balanceDailyData.monthName}`;
}

function setSelectedBalanceDay(day) {
    const maxDay = balanceDailyData.labels.length || 1;
    selectedBalanceDay = Math.max(1, Math.min(Number(day) || 1, maxDay));
    const index = selectedBalanceDay - 1;
    const ingresos = Number(balanceDailyData.ingresos[index] || 0);
    const perdidas = Number(balanceDailyData.perdidas[index] || 0);
    const dateLabel = formatBalanceDate(selectedBalanceDay);

    balanceDayHeading.textContent = dateLabel;
    balanceDayLabel.textContent = 'Ingresos diarios';
    balanceDayIngresos.textContent = `S/ ${moneyFormatter.format(ingresos)}`;
    balanceDayPerdidas.textContent = `S/ ${moneyFormatter.format(perdidas)}`;
}

async function moveSelectedBalanceDay(offset) {
    const targetDay = selectedBalanceDay + offset;
    const maxDay = balanceDailyData.labels.length || 1;

    if (targetDay < 1) {
        const previous = moveMonth(balanceDailyData.year, balanceDailyData.month, -1);
        await loadBalanceMonth(previous.month, previous.year, 'last');
        return;
    }

    if (targetDay > maxDay) {
        const next = moveMonth(balanceDailyData.year, balanceDailyData.month, 1);
        await loadBalanceMonth(next.month, next.year, 1);
        return;
    }

    setSelectedBalanceDay(targetDay);
}

async function loadBalanceMonth(month, year = balanceDailyData.year, selectedDay = null) {
    const data = await fetchDashboardData({ accion: 'balance_mes', year, month });
    balanceDailyData = {
        labels: data.labels,
        ingresos: data.ingresos,
        perdidas: data.perdidas,
        monthName: data.monthName,
        month: Number(data.month),
        year: Number(data.year)
    };
    balanceMonthJump.value = String(balanceDailyData.month);
    balanceIngresosLabel.textContent = `Ingresos de ${data.monthName}`;
    balancePerdidasLabel.textContent = `Perdidas de ${data.monthName}`;
    balanceTotalIngresos.textContent = `S/ ${moneyFormatter.format(data.totalIngresos)}`;
    balanceTotalPerdidas.textContent = `S/ ${moneyFormatter.format(data.totalPerdidas)}`;
    updateBalanceNote(data.totalIngresos, data.totalPerdidas);

    const now = new Date();
    let defaultDay = balanceDailyData.year === now.getFullYear() && balanceDailyData.month === now.getMonth() + 1
        ? now.getDate()
        : 1;
    if (selectedDay === 'last') {
        defaultDay = balanceDailyData.labels.length || 1;
    } else if (selectedDay !== null) {
        defaultDay = selectedDay;
    }
    setSelectedBalanceDay(defaultDay);

    const params = new URLSearchParams(window.location.search);
    params.set('year', String(balanceDailyData.year));
    params.set('month', String(balanceDailyData.month));
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
}

function renderEstadoLegend(items) {
    if (!items.length) {
        estadoLegend.innerHTML = '<p class="admin-state-empty">No hay citas registradas en este mes.</p>';
        return;
    }

    estadoLegend.innerHTML = items.map((item) => `
        <div class="admin-state-legend__item">
            <span class="admin-state-legend__dot" style="background:${item.color}"></span>
            <span class="admin-state-legend__name">${item.label}</span>
            <strong>${item.value}</strong>
            <small>${item.percent}%</small>
        </div>
    `).join('');
}

function updateEstadoChart(data) {
    chartEstados.data.labels = data.items.map((item) => item.label);
    chartEstados.data.datasets[0].data = data.items.map((item) => item.value);
    chartEstados.data.datasets[0].backgroundColor = data.items.map((item) => item.color);
    chartEstados.data.datasets[0].hoverBackgroundColor = data.items.map((item) => item.hoverColor);
    chartEstados.update();
    estadoMesTitle.textContent = data.titulo;
    estadosTotal.innerHTML = `<strong>${data.total}</strong> citas del mes`;
    estadoMesNote.textContent = `${data.total} citas del mes equivalen al 100%.`;
    renderEstadoLegend(data.items);
}

renderEstadoLegend(initialEstadoItems);
setSelectedBalanceDay(selectedBalanceDay);

document.getElementById('aplicarCitasYear').addEventListener('click', async () => {
    const selectedYear = document.getElementById('citasYear').value;
    const data = await fetchDashboardData({ accion: 'citas_anio', year: selectedYear });
    chartCitas.data.labels = data.labels;
    chartCitas.data.datasets[0].data = data.citas;
    chartCitas.update();
    citasAnioMeta.innerHTML = `<strong>${data.total}</strong> citas en ${data.year}`;
});

document.getElementById('aplicarBalanceYear').addEventListener('click', async () => {
    const selectedYear = document.getElementById('balanceYear').value;
    const data = await fetchDashboardData({ accion: 'balance_anio', year: selectedYear });
    chartBalance.data.labels = data.labels;
    chartBalance.data.datasets[0].data = data.ingresos;
    chartBalance.data.datasets[1].data = data.perdidas;
    chartBalance.update();
});

document.getElementById('balanceDayPrev').addEventListener('click', () => moveSelectedBalanceDay(-1));
document.getElementById('balanceDayNext').addEventListener('click', () => moveSelectedBalanceDay(1));
balanceMonthJump.addEventListener('change', () => loadBalanceMonth(balanceMonthJump.value, balanceDailyData.year, selectedBalanceDay));

function moveMonth(year, month, offset) {
    const next = new Date(year, month - 1 + offset, 1);
    return { year: next.getFullYear(), month: next.getMonth() + 1 };
}

async function loadEstadoMonth(offset) {
    const nextMonth = moveMonth(estadoYear, estadoMonth, offset);
    const directionClass = offset < 0 ? 'is-shifting-left' : 'is-shifting-right';
    estadoChartWrap.classList.add(directionClass);

    try {
        const data = await fetchDashboardData({ accion: 'estados_mes', year: nextMonth.year, month: nextMonth.month });
        estadoYear = Number(data.year);
        estadoMonth = Number(data.month);
        updateEstadoChart(data);
    } finally {
        window.setTimeout(() => {
            estadoChartWrap.classList.remove('is-shifting-left', 'is-shifting-right');
        }, 220);
    }
}

document.getElementById('estadoPrev').addEventListener('click', () => loadEstadoMonth(-1));
document.getElementById('estadoNext').addEventListener('click', () => loadEstadoMonth(1));
</script>
</body>
</html>
