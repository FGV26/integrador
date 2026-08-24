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

if (!isset($_GET['id'])) {
    header('Location: /Cliente/Citas.php');
    exit();
}

$citaDAO = new CitaDAO();
$cita = $citaDAO->obtenerPorId($_GET['id']);

if (!$cita) {
    header('Location: /Cliente/Citas.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$abogado = $usuarioDAO->obtenerPorId($cita->getAbogadoId());
$tipoDeCasoDAO = new TipoDeCasoDAO();
$tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    if ($cita->getEstado() == 'pendiente') {
        $contrasenaConfirmacion = trim((string) ($_POST['contrasena_confirmacion'] ?? ''));

        if ($contrasenaConfirmacion === '') {
            $mensaje = 'Ingresa tu contrasena para cancelar la cita.';
        } elseif (!password_verify($contrasenaConfirmacion, $usuario->getContrasena())) {
            $mensaje = 'La contrasena de validacion es incorrecta.';
        } elseif ($citaDAO->cancelarCita($cita->getId())) {
            $_SESSION['mensaje'] = 'La cita fue cancelada correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /Cliente/Citas.php');
            exit();
        } else {
            $mensaje = 'No se pudo cancelar la cita. Intenta nuevamente.';
        }
    }
}

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
    <title>Detalle de cita - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/cliente-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="cliente-dashboard-page">
    <?php render_cliente_dashboard_header($base_url, 'citas', $base_url . 'Cliente/Citas.php', 'Volver a mis citas'); ?>

    <main class="cliente-dashboard-main">
        <div class="cliente-dashboard-shell cliente-dashboard-grid">
            <?php if ($mensaje !== '') : ?>
                <div class="cliente-dashboard-alert cliente-dashboard-alert--danger" role="alert">
                    <i class="bi bi-shield-exclamation"></i>
                    <span><?php echo h($mensaje); ?></span>
                </div>
            <?php endif; ?>

            <section class="cliente-dashboard-card cliente-dashboard-card--ticket">
                <div class="ticket-layout">
                    <div class="ticket-card">
                        <div class="ticket-card__header">
                            <div>
                                <h1 class="cliente-dashboard-title cliente-dashboard-title--section">Datos de tu cita</h1>
                            </div>
                        </div>

                        <div class="ticket-meta">
                            <article class="ticket-meta__item">
                                <span>Cliente</span>
                                <strong><?php echo h($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno()); ?></strong>
                            </article>
                            <article class="ticket-meta__item">
                                <span>Abogado</span>
                                <strong><?php echo h($abogado->getNombre() . ' ' . $abogado->getApellidoPaterno() . ' ' . $abogado->getApellidoMaterno()); ?></strong>
                            </article>
                            <article class="ticket-meta__item">
                                <span>Fecha</span>
                                <strong><?php echo h($cita->getFecha()); ?></strong>
                            </article>
                            <article class="ticket-meta__item">
                                <span>Hora</span>
                                <strong><?php echo h(substr($cita->getHora(), 0, 5)); ?></strong>
                            </article>
                            <article class="ticket-meta__item">
                                <span>Tipo de caso</span>
                                <strong><?php echo h($tipoDeCaso ? $tipoDeCaso->getTipo() : 'No disponible'); ?></strong>
                            </article>
                            <article class="ticket-meta__item">
                                <span>Estado</span>
                                <strong><span class="status-pill status-pill--<?php echo h(strtolower($cita->getEstado())); ?>"><?php echo h($cita->getEstado()); ?></span></strong>
                            </article>
                        </div>

                        <div class="ticket-message">
                            <span class="cliente-dashboard-eyebrow">Motivo</span>
                            <strong><?php echo nl2br(h($cita->getMensaje())); ?></strong>
                        </div>
                    </div>

                    <aside class="ticket-side">
                        <h3 class="ticket-side__title">Comprobante rapido</h3>
                        <div class="ticket-side__list">
                            <div class="ticket-side__item">
                                <span>Ubicacion del flujo</span>
                                <strong>Portal del cliente</strong>
                            </div>
                            <div class="ticket-side__item">
                                <span>Forma de gestion</span>
                                <strong>Reserva digital con validacion de identidad</strong>
                            </div>
                            <div class="ticket-side__item">
                                <span>Accion disponible</span>
                                <strong><?php echo $cita->getEstado() === 'pendiente' ? 'Cancelacion permitida' : 'Solo lectura'; ?></strong>
                            </div>
                        </div>

                        <div class="ticket-side__actions">
                            <a href="<?php echo $base_url; ?>Cliente/Citas.php" class="cliente-dashboard-button cliente-dashboard-button--ghost">
                                <i class="bi bi-arrow-left"></i>
                                <span>Volver a mis citas</span>
                            </a>
                            <?php if ($cita->getEstado() == 'pendiente'): ?>
                                <button type="button" class="cliente-dashboard-button cliente-dashboard-button--danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Cancelar cita</span>
                                </button>
                            <?php else: ?>
                                <div class="cliente-dashboard-alert" role="status">
                                    <i class="bi bi-check-circle"></i>
                                    <span>La cita ya no se puede cancelar desde este panel.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content cliente-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirmar cancelacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Estas seguro de que deseas cancelar esta cita?</p>
                    <div class="cliente-modal__summary">
                        <p class="mb-2"><strong>Fecha:</strong> <?php echo h($cita->getFecha()); ?></p>
                        <p class="mb-2"><strong>Hora:</strong> <?php echo h(substr($cita->getHora(), 0, 5)); ?></p>
                        <p class="mb-0"><strong>Abogado:</strong> <?php echo h($abogado->getNombre() . ' ' . $abogado->getApellidoPaterno()); ?></p>
                    </div>
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="confirm_cancel" value="1">
                        <div class="cliente-modal__password">
                            <label for="contrasena_confirmacion" class="form-label">Ingresa tu contrasena</label>
                            <input type="password" id="contrasena_confirmacion" name="contrasena_confirmacion" class="form-control" placeholder="Contrasena de validacion" required>
                            <small>Esta validacion confirma que la cancelacion fue solicitada por el titular de la cuenta.</small>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="cliente-dashboard-button cliente-dashboard-button--ghost" data-bs-dismiss="modal">Mantener cita</button>
                            <button type="submit" class="cliente-dashboard-button cliente-dashboard-button--danger">Confirmar cancelacion</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
