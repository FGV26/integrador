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

if ((int) $cita->getAbogadoId() !== (int) $usuario->getId()) {
    header('Location: /Abogado/Citas.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
$cliente = $usuarioDAO->obtenerPorId($cita->getClienteId());
$tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
$documentos = $citaDAO->obtenerDocumentosCita($cita->getId());
$mensaje = $_SESSION['mensaje'] ?? '';
$mensajeTipo = $_SESSION['mensaje_tipo'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$motivoCompleto = trim((string) $cita->getMensaje());
$motivoLimite = 145;
$motivoCorto = strlen($motivoCompleto) > $motivoLimite ? substr($motivoCompleto, 0, $motivoLimite) . '...' : $motivoCompleto;
$tieneDocumentos = count($documentos) > 0;
$horariosDisponibles = ['09:30', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'];
$fechaMinimaReajuste = date('Y-m-d');
$diasReajuste = [];
$nombresDiasReajuste = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miercoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sabado',
    'Sunday' => 'Domingo',
];
$mesesReajuste = [
    '01' => 'enero',
    '02' => 'febrero',
    '03' => 'marzo',
    '04' => 'abril',
    '05' => 'mayo',
    '06' => 'junio',
    '07' => 'julio',
    '08' => 'agosto',
    '09' => 'septiembre',
    '10' => 'octubre',
    '11' => 'noviembre',
    '12' => 'diciembre',
];
for ($i = 1; $i <= 3; $i++) {
    $dia = new DateTimeImmutable('+' . $i . ' day');
    $diasReajuste[] = [
        'value' => $dia->format('Y-m-d'),
        'label' => ($nombresDiasReajuste[$dia->format('l')] ?? $dia->format('l')) . ', ' . $dia->format('d') . ' de ' . ($mesesReajuste[$dia->format('m')] ?? $dia->format('m')),
    ];
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
                            <h2 class="abogado-toolbar__title">Datos de la atencion</h2>
                        </div>
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
                            <span>Telefono de emergencia</span>
                            <strong><?php echo h($cliente->getTelefono() ? '+51 ' . $cliente->getTelefono() : 'Sin telefono'); ?></strong>
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
                        <article class="abogado-detail-box abogado-detail-box--document <?php echo $tieneDocumentos ? 'is-attached' : 'is-missing'; ?>">
                            <span>Documento PDF</span>
                            <strong>
                                <i class="bi <?php echo $tieneDocumentos ? 'bi-file-earmark-check-fill' : 'bi-file-earmark-x-fill'; ?>"></i>
                                <?php echo $tieneDocumentos ? 'Adjunto' : 'No adjunto'; ?>
                            </strong>
                        </article>

                    </div>

                    <div class="abogado-notes abogado-notes--compact">
                        <div>
                            <span class="abogado-eyebrow">Motivo de consulta</span>
                            <p><?php echo nl2br(h($motivoCorto !== '' ? $motivoCorto : 'Sin motivo registrado.')); ?></p>
                        </div>
                        <button
                            type="button"
                            class="abogado-note-arrow"
                            id="openMotivoDrawer"
                            data-motivo="<?php echo h($motivoCompleto !== '' ? $motivoCompleto : 'Sin motivo registrado.'); ?>"
                            aria-label="Leer motivo completo">
                            <i class="bi bi-chevron-right"></i>
                        </button>
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
                            <span>Siguiente paso</span>
                            <strong><?php echo $cita->getEstado() === 'pendiente' ? 'Aceptar, rechazar o reajustar la cita' : ($cita->getEstado() === 'confirmada' ? 'Iniciar la atencion de 40 minutos' : ($cita->getEstado() === 'en_atencion' ? 'Continuar atencion abierta' : 'Sin accion pendiente')); ?></strong>
                        </div>
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
                            <button type="button" class="abogado-button abogado-button--danger w-100 js-open-drawer" data-target="rechazoDrawer">
                                <i class="bi bi-x-circle"></i>
                                <span>Rechazar con motivo</span>
                            </button>
                            <button type="button" class="abogado-button abogado-button--ghost w-100 js-open-drawer" data-target="reajusteDrawer">
                                <i class="bi bi-calendar2-week"></i>
                                <span>Reajustar cita</span>
                            </button>
                        <?php elseif ($cita->getEstado() == 'confirmada') : ?>
                            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                                <button type="submit" name="iniciar" class="abogado-button abogado-button--primary w-100">
                                    <i class="bi bi-play-circle"></i>
                                    <span>Iniciar atencion</span>
                                </button>
                            </form>
                            <button type="button" class="abogado-button abogado-button--danger w-100 js-open-drawer" data-target="rechazoDrawer">
                                <i class="bi bi-x-circle"></i>
                                <span>Cancelar con motivo</span>
                            </button>
                            <button type="button" class="abogado-button abogado-button--ghost w-100 js-open-drawer" data-target="reajusteDrawer">
                                <i class="bi bi-calendar2-week"></i>
                                <span>Reajustar cita</span>
                            </button>
                        <?php elseif ($cita->getEstado() == 'en_atencion') : ?>
                            <a href="<?php echo $base_url; ?>Abogado/AtencionCita.php?id=<?php echo (int) $cita->getId(); ?>" class="abogado-button abogado-button--primary w-100">
                                <i class="bi bi-folder2-open"></i>
                                <span>Continuar atencion</span>
                            </a>
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

    <div class="abogado-side-drawer" id="motivoDrawer" aria-hidden="true">
        <button class="abogado-side-drawer__backdrop" type="button" id="closeMotivoBackdrop" aria-label="Cerrar panel"></button>
        <aside class="abogado-side-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="motivoDrawerTitle">
            <div class="abogado-side-drawer__header">
                <div>
                    <span class="abogado-eyebrow">Lectura completa</span>
                    <h2 id="motivoDrawerTitle">Motivo de consulta</h2>
                </div>
                <button type="button" class="abogado-drawer-close" id="closeMotivoDrawer" aria-label="Cerrar panel">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <article class="abogado-drawer-note">
                <span>Motivo registrado por el cliente</span>
                <p id="motivoDrawerText"></p>
            </article>
        </aside>
    </div>

    <div class="abogado-side-drawer" id="rechazoDrawer" aria-hidden="true">
        <button class="abogado-side-drawer__backdrop js-close-drawer" type="button" aria-label="Cerrar panel"></button>
        <aside class="abogado-side-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="rechazoDrawerTitle">
            <div class="abogado-side-drawer__header">
                <div>
                    <span class="abogado-eyebrow">Confirmacion segura</span>
                    <h2 id="rechazoDrawerTitle"><?php echo $cita->getEstado() === 'pendiente' ? 'Rechazar cita' : 'Cancelar cita'; ?></h2>
                </div>
                <button type="button" class="abogado-drawer-close js-close-drawer" aria-label="Cerrar panel">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php" class="abogado-drawer-form">
                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                <label>
                    <span>Motivo</span>
                    <textarea name="motivo_cancelacion" rows="5" placeholder="Explica brevemente el motivo" required></textarea>
                </label>
                <label>
                    <span>Contrasena de validacion</span>
                    <input type="password" name="contrasena_confirmacion" placeholder="Ingresa tu contrasena" required>
                </label>
                <button type="submit" name="cancelar" class="abogado-button abogado-button--danger w-100">
                    <i class="bi bi-shield-check"></i>
                    <span>Confirmar rechazo</span>
                </button>
            </form>
        </aside>
    </div>

    <div class="abogado-side-drawer" id="reajusteDrawer" aria-hidden="true">
        <button class="abogado-side-drawer__backdrop js-close-drawer" type="button" aria-label="Cerrar panel"></button>
        <aside class="abogado-side-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="reajusteDrawerTitle">
            <div class="abogado-side-drawer__header">
                <div>
                    <span class="abogado-eyebrow">Nueva agenda</span>
                    <h2 id="reajusteDrawerTitle">Reajustar cita</h2>
                </div>
                <button type="button" class="abogado-drawer-close js-close-drawer" aria-label="Cerrar panel">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php" class="abogado-drawer-form">
                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                <label>
                    <span>Nueva fecha</span>
                    <select name="nueva_fecha" required>
                        <?php foreach ($diasReajuste as $diaReajuste) : ?>
                            <option value="<?php echo h($diaReajuste['value']); ?>">
                                <?php echo h($diaReajuste['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Nuevo horario</span>
                    <select name="nueva_hora" required>
                        <?php foreach ($horariosDisponibles as $horario) : ?>
                            <option value="<?php echo h($horario); ?>" <?php echo substr($cita->getHora(), 0, 5) === $horario ? 'selected' : ''; ?>>
                                <?php echo h($horario); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Motivo del reajuste</span>
                    <textarea name="motivo_reajuste" rows="4" placeholder="Explica por que se reajusta la cita" required></textarea>
                </label>
                <label>
                    <span>Contrasena de validacion</span>
                    <input type="password" name="contrasena_confirmacion" placeholder="Ingresa tu contrasena" required>
                </label>
                <button type="submit" name="reajustar" class="abogado-button abogado-button--primary w-100">
                    <i class="bi bi-calendar-check"></i>
                    <span>Guardar reajuste</span>
                </button>
            </form>
        </aside>
    </div>

    <?php render_abogado_dashboard_footer(); ?>
    <script>
    (function () {
        const drawer = document.getElementById('motivoDrawer');
        const openButton = document.getElementById('openMotivoDrawer');
        const closeButtons = [
            document.getElementById('closeMotivoDrawer'),
            document.getElementById('closeMotivoBackdrop')
        ];
        const text = document.getElementById('motivoDrawerText');

        if (!drawer || !openButton || !text) {
            return;
        }

        function openDrawer() {
            text.textContent = openButton.dataset.motivo || 'Sin motivo registrado.';
            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.classList.add('is-drawer-open');
        }

        function closeDrawer() {
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('is-drawer-open');
        }

        openButton.addEventListener('click', openDrawer);
        closeButtons.forEach((button) => {
            if (button) {
                button.addEventListener('click', closeDrawer);
            }
        });

        document.querySelectorAll('.js-open-drawer').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target || '');
                if (!target) {
                    return;
                }
                target.classList.add('is-open');
                target.setAttribute('aria-hidden', 'false');
                document.body.classList.add('is-drawer-open');
            });
        });
        document.querySelectorAll('.js-close-drawer').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.closest('.abogado-side-drawer');
                if (!target) {
                    return;
                }
                target.classList.remove('is-open');
                target.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('is-drawer-open');
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDrawer();
                document.querySelectorAll('.abogado-side-drawer.is-open').forEach((target) => {
                    target.classList.remove('is-open');
                    target.setAttribute('aria-hidden', 'true');
                });
                document.body.classList.remove('is-drawer-open');
            }
        });
    }());
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
