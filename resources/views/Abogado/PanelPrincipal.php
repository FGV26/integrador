<?php
require_once 'model/Usuario.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'abogado') {
    header('Location: /IniciarSesion.php');
    exit();
}

$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();
require_once __DIR__ . '/config/layout.php';

$citaDAO = new CitaDAO();
$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
$citasActivas = $citaDAO->obtenerCitasActivasPorAbogado($usuario->getId());
$todasLasCitas = $citaDAO->obtenerCitasPorAbogado($usuario->getId());

$hoy = date('Y-m-d');
$pendientes = 0;
$confirmadas = 0;
$citasHoy = [];
$clientesHoy = [];
$fechasConCitas = [];

foreach ($citasActivas as $cita) {
    $estado = strtolower((string) $cita->getEstado());

    if ($estado === 'pendiente') {
        $pendientes++;
    }

    if ($estado === 'confirmada') {
        $confirmadas++;
    }

    $fechasConCitas[$cita->getFecha()] = true;

    if ($cita->getFecha() === $hoy) {
        $citasHoy[] = $cita;
        $clientesHoy[$cita->getClienteId()] = true;
    }
}

usort($citasActivas, static function ($a, $b) {
    return strcmp($a->getFecha() . ' ' . $a->getHora(), $b->getFecha() . ' ' . $b->getHora());
});

$proximaCita = $citasActivas[0] ?? null;

usort($todasLasCitas, static function ($a, $b) {
    return strcmp($b->getFecha() . ' ' . $b->getHora(), $a->getFecha() . ' ' . $a->getHora());
});

$actividadReciente = array_slice($todasLasCitas, 0, 5);
$pendientesLista = array_values(array_filter($citasActivas, static function ($cita) {
    return strtolower((string) $cita->getEstado()) === 'pendiente';
}));
$pendientesLista = array_slice($pendientesLista, 0, 4);

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function abogado_nombre_completo($usuario)
{
    if (!$usuario) {
        return 'No disponible';
    }

    return trim($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno());
}

function abogado_nombre_dia($fecha)
{
    $dias = [
        'Sunday' => 'Domingo',
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miercoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sabado',
    ];

    $nombre = date('l', strtotime($fecha));

    return $dias[$nombre] ?? $nombre;
}

function abogado_nombre_mes($numeroMes)
{
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    return $meses[(int) $numeroMes] ?? '';
}

$fechaActualTexto = abogado_nombre_dia($hoy) . ', ' . date('d/m/Y');

$primerDiaMes = new DateTime(date('Y-m-01'));
$inicioCalendario = (clone $primerDiaMes);
$diaSemanaInicio = (int) $inicioCalendario->format('N');
$inicioCalendario->modify('-' . ($diaSemanaInicio - 1) . ' days');

$calendarioDias = [];
for ($i = 0; $i < 35; $i++) {
    $dia = (clone $inicioCalendario)->modify('+' . $i . ' days');
    $calendarioDias[] = [
        'fecha' => $dia->format('Y-m-d'),
        'numero' => $dia->format('j'),
        'mesActual' => $dia->format('m') === $primerDiaMes->format('m'),
        'esHoy' => $dia->format('Y-m-d') === $hoy,
        'tieneCita' => isset($fechasConCitas[$dia->format('Y-m-d')]),
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del abogado - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/abogado-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="abogado-dashboard-page">
    <?php render_abogado_dashboard_header($base_url, 'inicio'); ?>

    <main class="abogado-dashboard-main">
        <div class="abogado-dashboard-shell abogado-grid">
            <section class="abogado-panel-top">
                <article class="abogado-card abogado-card--hero abogado-panel-clock">
                    <span class="abogado-eyebrow">Panel del abogado</span>
                    <h1 class="abogado-title">Centro operativo</h1>
                    <div class="abogado-panel-clock__time" id="abogado-live-clock"><?php echo h(date('H:i')); ?></div>
                    <div class="abogado-panel-clock__date"><?php echo h($fechaActualTexto); ?></div>
                </article>

                <article class="abogado-card abogado-card--hero abogado-panel-next">
                    <span class="abogado-eyebrow">Siguiente cita</span>
                    <?php if ($proximaCita) : ?>
                        <?php
                        $clienteProximo = $usuarioDAO->obtenerPorId($proximaCita->getClienteId());
                        $tipoProximo = $tipoDeCasoDAO->obtenerPorId($proximaCita->getTipoDeCasoId());
                        ?>
                        <div class="abogado-panel-next__time"><?php echo h(substr($proximaCita->getHora(), 0, 5)); ?></div>
                        <strong class="abogado-panel-next__name"><?php echo h(abogado_nombre_completo($clienteProximo)); ?></strong>
                        <span class="abogado-panel-next__meta"><?php echo h($proximaCita->getFecha()); ?> · <?php echo h($tipoProximo ? $tipoProximo->getTipo() : 'Caso no definido'); ?></span>
                        <a href="<?php echo $base_url; ?>Abogado/DetalleCita.php?id=<?php echo (int) $proximaCita->getId(); ?>" class="abogado-button abogado-button--primary">
                            <i class="bi bi-arrow-up-right-circle"></i>
                            <span>Abrir cita</span>
                        </a>
                    <?php else : ?>
                        <div class="abogado-empty">
                            <p class="mb-0">No tienes citas activas por ahora.</p>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <section class="abogado-panel-metrics">
                <article class="abogado-panel-metric abogado-panel-metric--gold">
                    <div class="abogado-panel-metric__icon"><i class="bi bi-briefcase"></i></div>
                    <span class="abogado-panel-metric__label">Activas</span>
                    <strong class="abogado-panel-metric__value"><?php echo count($citasActivas); ?></strong>
                </article>
                <article class="abogado-panel-metric abogado-panel-metric--dark">
                    <div class="abogado-panel-metric__icon"><i class="bi bi-hourglass-split"></i></div>
                    <span class="abogado-panel-metric__label">Pendientes</span>
                    <strong class="abogado-panel-metric__value"><?php echo $pendientes; ?></strong>
                </article>
                <article class="abogado-panel-metric abogado-panel-metric--green">
                    <div class="abogado-panel-metric__icon"><i class="bi bi-check2-circle"></i></div>
                    <span class="abogado-panel-metric__label">Confirmadas</span>
                    <strong class="abogado-panel-metric__value"><?php echo $confirmadas; ?></strong>
                </article>
                <article class="abogado-panel-metric abogado-panel-metric--light">
                    <div class="abogado-panel-metric__icon"><i class="bi bi-calendar-day"></i></div>
                    <span class="abogado-panel-metric__label">Hoy</span>
                    <strong class="abogado-panel-metric__value"><?php echo count($citasHoy); ?></strong>
                </article>
            </section>

            <section class="abogado-panel-grid">
                <article class="abogado-card abogado-card--content abogado-widget">
                    <div class="abogado-widget__header">
                        <div>
                            <span class="abogado-eyebrow">Calendario</span>
                            <h2 class="abogado-toolbar__title"><?php echo h(abogado_nombre_mes($primerDiaMes->format('n')) . ' ' . $primerDiaMes->format('Y')); ?></h2>
                        </div>
                    </div>
                    <div class="abogado-calendar">
                        <div class="abogado-calendar__weekdays">
                            <span>Lun</span>
                            <span>Mar</span>
                            <span>Mie</span>
                            <span>Jue</span>
                            <span>Vie</span>
                            <span>Sab</span>
                            <span>Dom</span>
                        </div>
                        <div class="abogado-calendar__grid">
                            <?php foreach ($calendarioDias as $dia) : ?>
                                <div class="abogado-calendar__day<?php echo !$dia['mesActual'] ? ' is-out' : ''; ?><?php echo $dia['esHoy'] ? ' is-today' : ''; ?><?php echo $dia['tieneCita'] ? ' has-event' : ''; ?>">
                                    <span><?php echo h($dia['numero']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>

                <article class="abogado-card abogado-card--content abogado-widget">
                    <div class="abogado-widget__header">
                        <div>
                            <span class="abogado-eyebrow">Agenda de hoy</span>
                            <h2 class="abogado-toolbar__title">Bloque horario</h2>
                        </div>
                    </div>
                    <?php if (empty($citasHoy)) : ?>
                        <div class="abogado-empty">
                            <p class="mb-0">No hay atenciones registradas para hoy.</p>
                        </div>
                    <?php else : ?>
                        <div class="abogado-day-plan__list">
                            <?php foreach ($citasHoy as $citaDelDia) : ?>
                                <?php
                                $clienteDelDia = $usuarioDAO->obtenerPorId($citaDelDia->getClienteId());
                                $tipoDelDia = $tipoDeCasoDAO->obtenerPorId($citaDelDia->getTipoDeCasoId());
                                ?>
                                <a href="<?php echo $base_url; ?>Abogado/DetalleCita.php?id=<?php echo (int) $citaDelDia->getId(); ?>" class="abogado-day-plan__item abogado-day-plan__item--link">
                                    <div class="abogado-day-plan__time"><?php echo h(substr($citaDelDia->getHora(), 0, 5)); ?></div>
                                    <div>
                                        <span class="abogado-day-plan__main"><?php echo h(abogado_nombre_completo($clienteDelDia)); ?></span>
                                        <span class="abogado-day-plan__sub"><?php echo h($tipoDelDia ? $tipoDelDia->getTipo() : 'Caso no disponible'); ?> · <?php echo h(ucfirst($citaDelDia->getEstado())); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <section class="abogado-panel-grid abogado-panel-grid--bottom">
                <article class="abogado-card abogado-card--content abogado-widget">
                    <div class="abogado-widget__header">
                        <div>
                            <span class="abogado-eyebrow">Actividad</span>
                            <h2 class="abogado-toolbar__title">Log de citas</h2>
                        </div>
                    </div>
                    <?php if (empty($actividadReciente)) : ?>
                        <div class="abogado-empty">
                            <p class="mb-0">Aun no hay movimientos recientes.</p>
                        </div>
                    <?php else : ?>
                        <div class="abogado-log">
                            <?php foreach ($actividadReciente as $citaLog) : ?>
                                <?php
                                $clienteLog = $usuarioDAO->obtenerPorId($citaLog->getClienteId());
                                $tipoLog = $tipoDeCasoDAO->obtenerPorId($citaLog->getTipoDeCasoId());
                                ?>
                                <div class="abogado-log__item">
                                    <div class="abogado-log__dot"></div>
                                    <div class="abogado-log__content">
                                        <strong><?php echo h(abogado_nombre_completo($clienteLog)); ?></strong>
                                        <span><?php echo h($citaLog->getFecha()); ?> · <?php echo h(substr($citaLog->getHora(), 0, 5)); ?> · <?php echo h($tipoLog ? $tipoLog->getTipo() : 'Caso no definido'); ?></span>
                                    </div>
                                    <span class="abogado-status abogado-status--<?php echo h(strtolower($citaLog->getEstado())); ?>"><?php echo h($citaLog->getEstado()); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="abogado-card abogado-card--content abogado-widget">
                    <div class="abogado-widget__header">
                        <div>
                            <span class="abogado-eyebrow">Cola prioritaria</span>
                            <h2 class="abogado-toolbar__title">Pendientes de confirmacion</h2>
                        </div>
                        <a href="<?php echo $base_url; ?>Abogado/Citas.php" class="abogado-button abogado-button--ghost">
                            <i class="bi bi-list-task"></i>
                            <span>Ver todo</span>
                        </a>
                    </div>
                    <?php if (empty($pendientesLista)) : ?>
                        <div class="abogado-empty">
                            <p class="mb-0">No tienes citas pendientes por confirmar.</p>
                        </div>
                    <?php else : ?>
                        <div class="abogado-queue">
                            <?php foreach ($pendientesLista as $citaPendiente) : ?>
                                <?php
                                $clientePendiente = $usuarioDAO->obtenerPorId($citaPendiente->getClienteId());
                                ?>
                                <div class="abogado-queue__item">
                                    <div>
                                        <strong><?php echo h(abogado_nombre_completo($clientePendiente)); ?></strong>
                                        <span><?php echo h($citaPendiente->getFecha()); ?> · <?php echo h(substr($citaPendiente->getHora(), 0, 5)); ?></span>
                                    </div>
                                    <a href="<?php echo $base_url; ?>Abogado/DetalleCita.php?id=<?php echo (int) $citaPendiente->getId(); ?>" class="abogado-button abogado-button--primary">
                                        <i class="bi bi-arrow-right"></i>
                                        <span>Atender</span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>
        </div>
    </main>

    <?php render_abogado_dashboard_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const clock = document.getElementById('abogado-live-clock');
            if (!clock) {
                return;
            }

            function updateClock() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                clock.textContent = hours + ':' + minutes;
            }

            updateClock();
            setInterval(updateClock, 1000);
        })();
    </script>
</body>
</html>
