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
$citaDAO->cerrarAtencionesVencidasPorAbogado((int) $usuario->getId());
$cita = $citaDAO->obtenerPorId((int) $_GET['id']);

if (!$cita || (int) $cita->getAbogadoId() !== (int) $usuario->getId()) {
    header('Location: /Abogado/Citas.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();
$cliente = $usuarioDAO->obtenerPorId($cita->getClienteId());
$tipoDeCaso = $tipoDeCasoDAO->obtenerPorId($cita->getTipoDeCasoId());
$notas = $citaDAO->obtenerNotasAbogado($cita->getId());
$documentos = $citaDAO->obtenerDocumentosCita($cita->getId());
$historial = $citaDAO->obtenerHistorialCliente($cita->getClienteId(), $cita->getId());
$mensaje = $_SESSION['mensaje'] ?? '';
$mensajeTipo = $_SESSION['mensaje_tipo'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function abogado_fecha_humana($fecha)
{
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $date = new DateTimeImmutable((string) $fecha);
    return $date->format('d') . ' de ' . $meses[((int) $date->format('n')) - 1] . ' de ' . $date->format('Y');
}

$nombreCliente = trim($cliente->getNombre() . ' ' . $cliente->getApellidoPaterno() . ' ' . $cliente->getApellidoMaterno());
$inicioAt = $cita->getHoraInicioAt();
$finEstimado = $inicioAt ? (new DateTimeImmutable($inicioAt))->modify('+40 minutes') : null;
$atencionActiva = $cita->getEstado() === 'en_atencion';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atencion de cita - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/abogado-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="abogado-dashboard-page">
    <?php render_abogado_dashboard_header($base_url, 'citas', $base_url . 'Abogado/DetalleCita.php?id=' . (int) $cita->getId(), 'Volver al detalle'); ?>

    <main class="abogado-dashboard-main">
        <div class="abogado-dashboard-shell abogado-grid">
            <section class="abogado-card abogado-card--hero abogado-attention-hero">
                <div>
                    <span class="abogado-eyebrow">Atencion legal</span>
                    <h1 class="abogado-title"><?php echo h($nombreCliente); ?></h1>
                    <p class="abogado-subtitle">
                        Sesion de 40 minutos para revisar el caso, documentos, historial del cliente y registrar el cierre profesional.
                    </p>
                </div>
                <div class="abogado-attention-timer" data-end-at="<?php echo $finEstimado ? h($finEstimado->format('Y-m-d H:i:s')) : ''; ?>">
                    <span>Tiempo restante</span>
                    <strong id="attentionCountdown"><?php echo $atencionActiva ? 'Calculando...' : 'Finalizada'; ?></strong>
                    <small><?php echo $finEstimado ? 'Cierre estimado: ' . h($finEstimado->format('H:i')) : 'Sin tiempo activo'; ?></small>
                </div>
            </section>

            <?php if ($mensaje !== '') : ?>
                <div class="abogado-alert<?php echo $mensajeTipo === 'danger' ? ' abogado-alert--danger' : ' abogado-alert--success'; ?>" role="alert">
                    <i class="bi <?php echo $mensajeTipo === 'danger' ? 'bi-shield-exclamation' : 'bi-check-circle'; ?>"></i>
                    <span><?php echo h($mensaje); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$atencionActiva) : ?>
                <div class="abogado-alert" role="status">
                    <i class="bi bi-info-circle"></i>
                    <span>Esta cita ya no esta en atencion. Puedes revisar el detalle o volver a tu bandeja de citas.</span>
                </div>
            <?php endif; ?>

            <section class="abogado-attention-layout">
                <div class="abogado-grid">
                    <section class="abogado-card abogado-card--content">
                        <div class="abogado-detail-header">
                            <div>
                                <span class="abogado-eyebrow">Ficha de trabajo</span>
                                <h2 class="abogado-toolbar__title">Datos para atender</h2>
                            </div>
                            <span class="abogado-status abogado-status--<?php echo h(strtolower($cita->getEstado())); ?>"><?php echo h($cita->getEstado()); ?></span>
                        </div>

                        <div class="abogado-detail-grid">
                            <article class="abogado-detail-box"><span>Fecha</span><strong><?php echo h(abogado_fecha_humana($cita->getFecha())); ?></strong></article>
                            <article class="abogado-detail-box"><span>Hora</span><strong><?php echo h(substr($cita->getHora(), 0, 5)); ?></strong></article>
                            <article class="abogado-detail-box"><span>Tipo de caso</span><strong><?php echo h($tipoDeCaso ? $tipoDeCaso->getTipo() : 'No disponible'); ?></strong></article>
                            <article class="abogado-detail-box"><span>Contacto</span><strong><?php echo h($cliente->getCorreo()); ?><br><?php echo h($cliente->getTelefono()); ?></strong></article>
                        </div>

                        <div class="abogado-notes">
                            <span class="abogado-eyebrow">Motivo inicial</span>
                            <p><?php echo nl2br(h($cita->getMensaje())); ?></p>
                        </div>
                    </section>

                    <section class="abogado-card abogado-card--content">
                        <div class="abogado-toolbar">
                            <div>
                                <span class="abogado-eyebrow">Documentos</span>
                                <h2 class="abogado-toolbar__title">Archivos del cliente</h2>
                                <p class="abogado-toolbar__text">El cliente puede traer sus documentos de forma presencial o adjuntarlos en PDF cuando el flujo lo permita.</p>
                            </div>
                        </div>

                        <?php if (empty($documentos)) : ?>
                            <div class="abogado-empty">No hay documentos digitales archivados para esta cita.</div>
                        <?php else : ?>
                            <div class="abogado-document-list">
                                <?php foreach ($documentos as $documento) : ?>
                                    <a class="abogado-document" href="<?php echo $base_url . h($documento['archivo']); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        <span><?php echo h($documento['nombre_original']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="abogado-card abogado-card--content">
                        <div class="abogado-toolbar">
                            <div>
                                <span class="abogado-eyebrow">Notas previas</span>
                                <h2 class="abogado-toolbar__title">Recordatorio interno</h2>
                                <p class="abogado-toolbar__text">Guarda apuntes breves para recordar acuerdos, dudas o puntos importantes durante la atencion.</p>
                            </div>
                        </div>

                        <?php if ($atencionActiva) : ?>
                            <form class="abogado-note-form" method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                                <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                                <textarea name="nota_abogado" rows="4" placeholder="Ej. El cliente presentara documento original en oficina." required></textarea>
                                <button class="abogado-button abogado-button--primary" type="submit" name="guardar_nota">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Guardar nota</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="abogado-note-list">
                            <?php if (empty($notas)) : ?>
                                <div class="abogado-empty">Todavia no hay notas internas para esta atencion.</div>
                            <?php else : ?>
                                <?php foreach ($notas as $nota) : ?>
                                    <article class="abogado-note-item">
                                        <span><?php echo h(date('d/m/Y H:i', strtotime($nota['creado_en']))); ?></span>
                                        <p><?php echo nl2br(h($nota['nota'])); ?></p>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="abogado-card abogado-card--content">
                        <div class="abogado-toolbar">
                            <div>
                                <span class="abogado-eyebrow">Historial del cliente</span>
                                <h2 class="abogado-toolbar__title">Atenciones anteriores</h2>
                            </div>
                        </div>

                        <div class="abogado-history-list">
                            <?php if (empty($historial)) : ?>
                                <div class="abogado-empty">Este cliente no tiene citas anteriores registradas.</div>
                            <?php else : ?>
                                <?php foreach ($historial as $item) : ?>
                                    <article class="abogado-history-item">
                                        <div>
                                            <strong><?php echo h($item['fecha'] . ' ' . substr($item['hora'], 0, 5)); ?></strong>
                                            <span><?php echo h($item['tipo_caso'] ?? 'Tipo no disponible'); ?> con <?php echo h($item['abogado'] ?? 'abogado no disponible'); ?></span>
                                        </div>
                                        <span class="abogado-status abogado-status--<?php echo h(strtolower($item['estado'])); ?>"><?php echo h($item['estado']); ?></span>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <aside class="abogado-briefing abogado-attention-close">
                    <h3 class="abogado-briefing__title">Cierre de atencion</h3>
                    <p class="abogado-toolbar__text">Registra el resultado final cuando la sesion haya concluido.</p>

                    <?php if ($atencionActiva) : ?>
                        <form class="abogado-close-form" method="POST" action="<?php echo $base_url; ?>Controladores/ControladorInformacion.php">
                            <input type="hidden" name="cita_id" value="<?php echo (int) $cita->getId(); ?>">
                            <label>
                                <span>Resultado u observacion</span>
                                <textarea name="observacion_final" rows="6" placeholder="Describe el cierre, acuerdo o resultado principal de la cita."></textarea>
                            </label>
                            <label class="abogado-check">
                                <input type="checkbox" name="requiere_nueva_cita" value="1">
                                <span>Requiere nueva cita</span>
                            </label>
                            <label class="abogado-check">
                                <input type="checkbox" name="requiere_cambio_especialidad" value="1">
                                <span>Requiere cambio de especialidad</span>
                            </label>
                            <button class="abogado-button abogado-button--success w-100" type="submit" name="finalizar_atencion">
                                <i class="bi bi-check2-circle"></i>
                                <span>Finalizar cita</span>
                            </button>
                        </form>
                    <?php else : ?>
                        <div class="abogado-briefing__note">
                            <strong>Observacion final</strong><br>
                            <?php echo h($cita->getObservacionFinal() ?: 'Sin observacion registrada.'); ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo $base_url; ?>Abogado/Citas.php" class="abogado-button abogado-button--ghost w-100 mt-3">
                        <i class="bi bi-arrow-left"></i>
                        <span>Volver a citas</span>
                    </a>
                </aside>
            </section>
        </div>
    </main>

    <?php render_abogado_dashboard_footer(); ?>
    <script>
    (function () {
        const timer = document.querySelector('.abogado-attention-timer');
        const output = document.getElementById('attentionCountdown');
        if (!timer || !output || !timer.dataset.endAt) {
            return;
        }
        const end = new Date(timer.dataset.endAt.replace(' ', 'T')).getTime();
        const tick = function () {
            const remaining = end - Date.now();
            if (remaining <= 0) {
                output.textContent = 'Tiempo cumplido';
                setTimeout(function () { window.location.reload(); }, 1200);
                return;
            }
            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            output.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        };
        tick();
        setInterval(tick, 1000);
    }());
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
