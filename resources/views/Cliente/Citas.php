<?php
require_once 'model/Usuario.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'cliente') {
    header('Location: /IniciarSesion.php');
    exit();
}

$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();
require_once __DIR__ . '/config/layout.php';

$citaDAO = new CitaDAO();
$citas = $citaDAO->obtenerCitasActivasPorCliente($usuario->getId());

$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
$mensaje = $_SESSION['mensaje'] ?? '';
$mensajeTipo = $_SESSION['mensaje_tipo'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pendientes = 0;
$confirmadas = 0;
foreach ($citas as $cita) {
    if ($cita->getEstado() === 'pendiente') {
        $pendientes++;
    }
    if ($cita->getEstado() === 'confirmada') {
        $confirmadas++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis citas - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/cliente-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="cliente-dashboard-page">
    <?php render_cliente_dashboard_header($base_url, 'citas'); ?>

    <main class="cliente-dashboard-main">
        <div class="cliente-dashboard-shell cliente-dashboard-grid">
            <?php if (!empty($mensaje)) : ?>
                <div class="cliente-dashboard-alert<?php echo $mensajeTipo === 'danger' ? ' cliente-dashboard-alert--danger' : ''; ?>" role="alert">
                    <i class="bi bi-info-circle"></i>
                    <span><?php echo h($mensaje); ?></span>
                </div>
            <?php endif; ?>

            <section class="citas-summary-grid">
                <article class="citas-summary-card">
                    <span>Total activas</span>
                    <strong><?php echo count($citas); ?></strong>
                </article>
                <article class="citas-summary-card">
                    <span>Pendientes</span>
                    <strong><?php echo $pendientes; ?></strong>
                </article>
                <article class="citas-summary-card">
                    <span>Confirmadas</span>
                    <strong><?php echo $confirmadas; ?></strong>
                </article>
            </section>

            <section class="cliente-dashboard-card cliente-dashboard-card--content">
                <div class="cliente-dashboard-card__head">
                    <div>
                        <h1 class="cliente-dashboard-title cliente-dashboard-title--section">Mis citas</h1>
                    </div>
                    <a href="<?php echo $base_url; ?>Cliente/SolicitarCita.php" class="cliente-dashboard-button cliente-dashboard-button--primary">
                        <i class="bi bi-calendar-plus"></i>
                        <span>Agendar cita</span>
                    </a>
                </div>

                <?php if (empty($citas)) : ?>
                    <div class="empty-state">
                        <p class="mb-2">Aun no tienes citas activas registradas.</p>
                        <a href="<?php echo $base_url; ?>Cliente/SolicitarCita.php" class="cliente-dashboard-button cliente-dashboard-button--primary">Agendar mi primera cita</a>
                    </div>
                <?php else : ?>
                    <div class="citas-table-wrap">
                        <table class="citas-table">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Abogado</th>
                                    <th>Tipo de caso</th>
                                    <th>Estado</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas as $cita): ?>
                                    <?php
                                        $abogado = $usuarioDAO->obtenerPorId($cita->getAbogadoId());
                                        $tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
                                        $estado = strtolower($cita->getEstado());
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="citas-table__main"><?php echo h($cita->getFecha()); ?></span>
                                            <span class="citas-table__sub"><?php echo h(substr($cita->getHora(), 0, 5)); ?></span>
                                        </td>
                                        <td>
                                            <span class="citas-table__main"><?php echo h($abogado->getNombre() . ' ' . $abogado->getApellidoPaterno()); ?></span>
                                            <span class="citas-table__sub"><?php echo h($abogado->getCorreo() ?: 'Contacto disponible desde el perfil del abogado'); ?></span>
                                        </td>
                                        <td>
                                            <span class="citas-table__main"><?php echo h($tipoDeCaso->getTipo()); ?></span>
                                            <span class="citas-table__sub"><?php echo h(mb_strimwidth($cita->getMensaje(), 0, 60, '...')); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-pill status-pill--<?php echo h($estado); ?>"><?php echo h($estado); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $base_url; ?>Cliente/DetalleCita.php?id=<?php echo (int) $cita->getId(); ?>" class="cliente-dashboard-button cliente-dashboard-button--ghost">
                                                <i class="bi bi-receipt"></i>
                                                <span>Ver detalle</span>
                                            </a>
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

    <?php render_cliente_dashboard_footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function () {
            var alerts = document.querySelectorAll('.cliente-dashboard-alert');
            alerts.forEach(function (alert) {
                alert.classList.add('d-none');
            });
        }, 4000);
    </script>
</body>
</html>
