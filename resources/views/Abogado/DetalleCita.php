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

if (!isset($_GET['id'])) {
    header('Location: /Abogado/Citas.php');
    exit();
}

$citaDAO = new CitaDAO();
$cita = $citaDAO->obtenerPorId($_GET['id']);

if (!$cita) {
    header('Location: /Abogado/Citas.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
$cliente = $usuarioDAO->obtenerPorId($cita->getClienteId());
$tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
$mensaje = $_SESSION['mensaje'] ?? '';
$mensajeTipo = $_SESSION['mensaje_tipo'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);

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
    <title>Detalle de cita del abogado - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/abogado-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="abogado-dashboard-page">
    <?php render_abogado_dashboard_header($base_url, 'citas', $base_url . 'Abogado/Citas.php', 'Volver a citas'); ?>

    <main class="abogado-dashboard-main">
        <div class="abogado-dashboard-shell abogado-grid">
            <section class="abogado-card abogado-card--hero">
                <span class="abogado-eyebrow">Ficha de atencion</span>
                <h1 class="abogado-title">Detalle profesional de la cita</h1>
                <p class="abogado-subtitle">
                    Revisa la informacion del cliente, el motivo de la consulta y ejecuta la siguiente
                    accion operativa segun el estado actual de la cita.
                </p>
            </section>

            <?php if ($mensaje !== '') : ?>
                <div class="abogado-alert<?php echo $mensajeTipo === 'danger' ? ' abogado-alert--danger' : ' abogado-alert--success'; ?>" role="alert">
                    <i class="bi <?php echo $mensajeTipo === 'danger' ? 'bi-shield-exclamation' : 'bi-check-circle'; ?>"></i>
                    <span><?php echo h($mensaje); ?></span>
                </div>
            <?php endif; ?>

            <section class="abogado-detail-layout">
                <div class="abogado-card abogado-card--detail">
                    <div class="abogado-detail-header">
                        <div>
                            <span class="abogado-eyebrow">Expediente de reunion</span>
                            <h2 class="abogado-toolbar__title">Datos de la atencion</h2>
                            <p class="abogado-toolbar__text">Esta ficha resume la informacion necesaria para atender al cliente y decidir la siguiente accion del flujo.</p>
                        </div>
                        <span class="abogado-detail-header__code">CITA <?php echo (int) $cita->getId(); ?></span>
                    </div>

                    <div class="abogado-detail-grid">
                        <article class="abogado-detail-box">
                            <span>Cliente</span>
                            <strong><?php echo h(trim($cliente->getNombre() . ' ' . $cliente->getApellidoPaterno() . ' ' . $cliente->getApellidoMaterno())); ?></strong>
                        </article>
                        <article class="abogado-detail-box">
                            <span>Correo del cliente</span>
                            <strong><?php echo h($cliente->getCorreo()); ?></strong>
                        </article>
                        <article class="abogado-detail-box">
                            <span>Fecha</span>
                            <strong><?php echo h($cita->getFecha()); ?></strong>
                        </article>
                        <article class="abogado-detail-box">
                            <span>Hora</span>
                            <strong><?php echo h(substr($cita->getHora(), 0, 5)); ?></strong>
                        </article>
                        <article class="abogado-detail-box">
                            <span>Tipo de caso</span>
                            <strong><?php echo h($tipoDeCaso ? $tipoDeCaso->getTipo() : 'No disponible'); ?></strong>
                        </article>
                        <article class="abogado-detail-box">
                            <span>Estado actual</span>
                            <strong><span class="abogado-status abogado-status--<?php echo h(strtolower($cita->getEstado())); ?>"><?php echo h($cita->getEstado()); ?></span></strong>
                        </article>
                    </div>

                    <div class="abogado-notes">
                        <span class="abogado-eyebrow">Motivo de consulta</span>
                        <p><?php echo nl2br(h($cita->getMensaje())); ?></p>
                    </div>
                </div>

                <aside class="abogado-briefing">
                    <h3 class="abogado-briefing__title">Panel de decision</h3>
                    <div class="abogado-briefing__list">
                        <div class="abogado-briefing__item">
                            <span>Fase del flujo</span>
                            <strong><?php echo h(ucfirst($cita->getEstado())); ?></strong>
                        </div>
                        <div class="abogado-briefing__item">
                            <span>Cliente a atender</span>
                            <strong><?php echo h(trim($cliente->getNombre() . ' ' . $cliente->getApellidoPaterno())); ?></strong>
                        </div>
                        <div class="abogado-briefing__item">
                            <span>Prioridad operativa</span>
                            <strong><?php echo $cita->getEstado() === 'pendiente' ? 'Requiere confirmacion del abogado' : ($cita->getEstado() === 'confirmada' ? 'Lista para cierre o cancelacion' : 'Sin accion pendiente'); ?></strong>
                        </div>
                        <div class="abogado-briefing__item">
                            <span>Canal de control</span>
                            <strong>Seguimiento interno desde el panel legal</strong>
                        </div>
                    </div>

                    <div class="abogado-briefing__note">
                        Antes de cambiar el estado, revisa si el caso ya fue coordinado con el cliente y si la informacion del motivo de consulta es suficiente para la atencion.
                    </div>

                    <div class="abogado-briefing__actions">
                        <a href="<?php echo $base_url; ?>Abogado/Citas.php" class="abogado-button abogado-button--ghost">
                            <i class="bi bi-arrow-left"></i>
                            <span>Volver a citas</span>
                        </a>

                        <?php if ($cita->getEstado() == 'pendiente') : ?>
                            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                                <button type="submit" name="aceptar" class="abogado-button abogado-button--success w-100">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Aceptar cita</span>
                                </button>
                            </form>
                        <?php elseif ($cita->getEstado() == 'confirmada') : ?>
                            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                                <button type="submit" name="terminar" class="abogado-button abogado-button--primary w-100">
                                    <i class="bi bi-check2-square"></i>
                                    <span>Marcar como terminada</span>
                                </button>
                            </form>
                            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                                <button type="submit" name="cancelar" class="abogado-button abogado-button--danger w-100">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Cancelar cita</span>
                                </button>
                            </form>
                        <?php else : ?>
                            <div class="abogado-alert" role="status">
                                <i class="bi bi-info-circle"></i>
                                <span>Esta cita ya no tiene acciones operativas disponibles desde el panel.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>
            </section>
        </div>
    </main>

    <?php render_abogado_dashboard_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
