<?php
require_once 'model/Usuario.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'abogado') {
    header('Location: /IniciarSesion.php');
    exit();
}

$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();
require_once __DIR__ . '/config/layout.php';

$citaDAO = new CitaDAO();
$citas = $citaDAO->obtenerCitasActivasPorAbogado($usuario->getId());
$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();

usort($citas, static function ($a, $b) {
    return strcmp($a->getFecha() . ' ' . $a->getHora(), $b->getFecha() . ' ' . $b->getHora());
});

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas del abogado - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/abogado-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="abogado-dashboard-page">
    <?php render_abogado_dashboard_header($base_url, 'citas'); ?>

    <main class="abogado-dashboard-main">
        <div class="abogado-dashboard-shell abogado-grid">
            <section class="abogado-card abogado-card--hero">
                <span class="abogado-eyebrow">Agenda profesional</span>
                <h1 class="abogado-title">Citas asignadas</h1>
                <p class="abogado-subtitle">
                    Revisa tus atenciones activas, prioriza por fecha y entra al detalle para aceptar,
                    terminar o cancelar una cita segun el flujo del estudio.
                </p>
            </section>

            <section class="abogado-card abogado-card--content">
                <div class="abogado-toolbar">
                    <div>
                        <span class="abogado-eyebrow">Bandeja de trabajo</span>
                        <h2 class="abogado-toolbar__title">Atenciones del abogado</h2>
                        <p class="abogado-toolbar__text">La tabla resume cliente, fecha, hora, tipo de caso y estado actual de cada atencion.</p>
                    </div>
                </div>

                <?php if (empty($citas)) : ?>
                    <div class="abogado-empty">
                        <p class="mb-0">No tienes citas activas asignadas por el momento.</p>
                    </div>
                <?php else : ?>
                    <div class="abogado-table-wrap">
                        <table class="abogado-table">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Cliente</th>
                                    <th>Tipo de caso</th>
                                    <th>Estado</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas as $cita) : ?>
                                    <?php
                                    $cliente = $usuarioDAO->obtenerPorId($cita->getClienteId());
                                    $tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
                                    $estado = strtolower($cita->getEstado());
                                    $mensaje = trim((string) $cita->getMensaje());
                                    $mensajeCorto = strlen($mensaje) > 80 ? substr($mensaje, 0, 80) . '...' : $mensaje;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="abogado-table__main"><?php echo h($cita->getFecha()); ?></span>
                                            <span class="abogado-table__sub"><?php echo h(substr($cita->getHora(), 0, 5)); ?></span>
                                        </td>
                                        <td>
                                            <span class="abogado-table__main"><?php echo h(trim($cliente->getNombre() . ' ' . $cliente->getApellidoPaterno() . ' ' . $cliente->getApellidoMaterno())); ?></span>
                                            <span class="abogado-table__sub"><?php echo h($cliente->getCorreo()); ?></span>
                                        </td>
                                        <td>
                                            <span class="abogado-table__main"><?php echo h($tipoDeCaso ? $tipoDeCaso->getTipo() : 'No disponible'); ?></span>
                                            <span class="abogado-table__sub"><?php echo h($mensajeCorto !== '' ? $mensajeCorto : 'Sin detalle adicional'); ?></span>
                                        </td>
                                        <td>
                                            <span class="abogado-status abogado-status--<?php echo h($estado); ?>"><?php echo h($estado); ?></span>
                                        </td>
                                        <td>
                                            <div class="abogado-table__actions">
                                                <a href="<?php echo $base_url; ?>Abogado/DetalleCita.php?id=<?php echo (int) $cita->getId(); ?>" class="abogado-button abogado-button--ghost">
                                                    <i class="bi bi-journal-text"></i>
                                                    <span>Revisar</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php render_abogado_dashboard_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
