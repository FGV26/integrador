<?php
require_once 'model/Usuario.php';
require_once 'dao/UsuarioDAO.php';
require_once 'dao/CitaDAO.php';
require_once 'dao/TipoDeCasoDAO.php';
session_start();

require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();
require_once __DIR__ . '/config/layout.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() !== 'cliente') {
    header('Location: /IniciarSesion.php');
    exit();
}

$usuario = $_SESSION['usuario'];

$citaDAO = new CitaDAO();
$tipoDeCasoDAO = new TipoDeCasoDAO();

$abogados = $citaDAO->obtenerAbogados();
$tiposDeCasos = $tipoDeCasoDAO->obtenerTodos();
$mensaje = $_SESSION['mensaje'] ?? '';
$mensajeTipo = $_SESSION['mensaje_tipo'] ?? 'warning';
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
    <title>Agendar Cita - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/cliente-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/citas.css?v=<?php echo time(); ?>">
</head>
<body class="cliente-dashboard-page citas-body">
    <?php render_cliente_dashboard_header($base_url, 'solicitar'); ?>

    <main class="cliente-dashboard-main">
        <div class="cliente-dashboard-shell">
            <div class="cita-shell mx-auto cliente-dashboard-card cliente-dashboard-card--content">
                <div class="cita-shell__header">
                    <div>
                        <p class="cita-shell__eyebrow mb-2">Solicitud guiada</p>
                        <h1 class="cita-shell__title mb-2">Agenda tu cita paso a paso</h1>
                        <p class="cita-shell__subtitle mb-0">Primero revisamos tus datos, luego eliges dia, horario y forma de pago referencial.</p>
                    </div>
                </div>

                <?php if (!empty($mensaje)) : ?>
                    <div id="wizardNotice" class="cliente-dashboard-alert wizard-notice wizard-notice--<?php echo h($mensajeTipo); ?> mb-4" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <span><?php echo h($mensaje); ?></span>
                    </div>
                <?php endif; ?>

                <div class="wizard-steps mb-4">
                    <button type="button" class="wizard-step is-active" data-step-indicator="1">
                        <span class="wizard-step__number">1</span>
                        <span class="wizard-step__label">Tus datos</span>
                    </button>
                    <button type="button" class="wizard-step" data-step-indicator="2">
                        <span class="wizard-step__number">2</span>
                        <span class="wizard-step__label">Dia y hora</span>
                    </button>
                    <button type="button" class="wizard-step" data-step-indicator="3">
                        <span class="wizard-step__number">3</span>
                        <span class="wizard-step__label">Resumen</span>
                    </button>
                    <button type="button" class="wizard-step" data-step-indicator="4">
                        <span class="wizard-step__number">4</span>
                        <span class="wizard-step__label">Pago</span>
                    </button>
                </div>

                <form id="citaForm" action="<?php echo $base_url; ?>Controladores/ControladorCita.php" method="POST" novalidate>
                    <input type="hidden" name="cliente_id" value="<?php echo h($usuario->getId()); ?>">
                    <input type="hidden" name="fecha" id="fechaSeleccionada" required>
                    <input type="hidden" name="hora" id="horaSeleccionada" required>
                    <input type="hidden" name="estado" value="pendiente">
                    <input type="hidden" name="metodo_pago" id="metodoPagoSeleccionado">
                    <input type="hidden" name="contrasena_confirmacion" id="contrasenaConfirmacionHidden">

                    <section class="wizard-panel is-active" data-step="1">
                        <div class="panel-card">
                            <div class="panel-card__header">
                                <h2 class="panel-card__title">Confirmacion de datos</h2>
                                <p class="panel-card__text">Tu informacion aparece como lectura rapida y debajo completas los datos de la cita.</p>
                            </div>

                            <div class="client-inline">
                                <p class="client-inline__row">
                                    <span class="client-inline__label">Cliente:</span>
                                    <span class="client-inline__value"><?php echo h($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno()); ?></span>
                                    <span class="client-inline__label client-inline__label--offset">Telefono:</span>
                                    <span class="client-inline__value"><?php echo h($usuario->getTelefono()); ?></span>
                                </p>
                                <p class="client-inline__row">
                                    <span class="client-inline__label">Correo:</span>
                                    <span class="client-inline__value"><?php echo h($usuario->getCorreo()); ?></span>
                                </p>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-lg-6">
                                    <label for="abogado_id" class="form-label">Abogado</label>
                                    <select name="abogado_id" id="abogado_id" class="form-select" required>
                                        <option value="">Seleccione un abogado</option>
                                        <?php foreach ($abogados as $abogado) : ?>
                                            <option value="<?php echo h($abogado->getId()); ?>">
                                                <?php echo h($abogado->getNombre() . ' ' . $abogado->getApellidoPaterno()); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="tipo_de_caso_id" class="form-label">Tipo de caso</label>
                                    <select name="tipo_de_caso_id" id="tipo_de_caso_id" class="form-select" required>
                                        <option value="">Seleccione un tipo de caso</option>
                                        <?php foreach ($tiposDeCasos as $tipoDeCaso) : ?>
                                            <option value="<?php echo h($tipoDeCaso->getId()); ?>">
                                                <?php echo h($tipoDeCaso->getTipo()); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Breve descripcion</label>
                                    <textarea name="mensaje" id="mensaje" class="form-control" rows="5" placeholder="Cuentanos brevemente el motivo de la cita" required></textarea>
                                </div>

                            </div>
                        </div>
                    </section>

                    <section class="wizard-panel" data-step="2">
                        <div class="panel-card">
                            <div class="panel-card__header">
                                <h2 class="panel-card__title">Seleccion de dia y horario</h2>
                                <p class="panel-card__text">Primero elige uno de los proximos tres dias. Luego se cargan los horarios disponibles para ese dia.</p>
                            </div>

                            <div class="selection-block">
                                <h3 class="selection-block__title">Dias disponibles</h3>
                                <div id="dayOptions" class="selection-grid selection-grid--days"></div>
                            </div>

                            <div class="selection-block mt-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <h3 class="selection-block__title mb-0">Horarios disponibles</h3>
                                    <span id="selectedDayLabel" class="selection-hint">Selecciona un dia para ver horarios.</span>
                                </div>
                                <div id="hourOptions" class="selection-grid selection-grid--hours mt-3"></div>
                            </div>
                        </div>
                    </section>

                    <section class="wizard-panel" data-step="3">
                        <div class="panel-card">
                            <div class="panel-card__header">
                                <h2 class="panel-card__title">Resumen de tu reserva</h2>
                                <p class="panel-card__text">Revisa el dia y horario elegidos antes de continuar al cierre.</p>
                            </div>

                            <div class="summary-inline">
                                <p class="summary-inline__row">
                                    <span class="summary-inline__label">Cliente:</span>
                                    <span class="summary-inline__value"><?php echo h($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno()); ?></span>
                                    <span class="summary-inline__label summary-inline__label--offset">Abogado:</span>
                                    <span class="summary-inline__value" id="summaryAbogado">Pendiente</span>
                                </p>
                                <p class="summary-inline__row">
                                    <span class="summary-inline__label">Tipo de caso:</span>
                                    <span class="summary-inline__value" id="summaryTipoCaso">Pendiente</span>
                                </p>
                                <p class="summary-inline__row">
                                    <span class="summary-inline__label">Fecha elegida:</span>
                                    <span class="summary-inline__value" id="summaryFecha">Pendiente</span>
                                    <span class="summary-inline__label summary-inline__label--offset">Horario:</span>
                                    <span class="summary-inline__value" id="summaryHora">Pendiente</span>
                                </p>
                                <p class="summary-inline__row">
                                    <span class="summary-inline__label">Detalle:</span>
                                    <span class="summary-inline__value" id="summaryMensaje">Pendiente</span>
                                </p>

                            </div>
                        </div>
                    </section>

                    <section class="wizard-panel" data-step="4">
                        <div class="panel-card">
                            <div class="panel-card__header">
                                <h2 class="panel-card__title">Metodo de pago referencial</h2>
                                <p class="panel-card__text">Solo es una seleccion visual para completar el flujo. No se procesara ningun pago real.</p>
                            </div>

                            <div class="payment-grid">
                                <button type="button" class="payment-card" data-payment="efectivo">
                                    <span class="payment-card__icon"><i class="bi bi-cash-stack"></i></span>
                                    <span class="payment-card__title">Efectivo</span>
                                    <span class="payment-card__text">Pago presencial referencial</span>
                                </button>
                                <button type="button" class="payment-card" data-payment="tarjeta">
                                    <span class="payment-card__icon"><i class="bi bi-credit-card-2-front"></i></span>
                                    <span class="payment-card__title">Tarjeta</span>
                                    <span class="payment-card__text">Pago digital referencial</span>
                                </button>
                            </div>

                            <div class="final-note mt-4">
                                <p class="mb-1">Resumen rapido:</p>
                                <strong id="finalSummaryText">Completa los pasos previos para confirmar tu cita.</strong>
                            </div>
                        </div>
                    </section>

                    <div id="wizardError" class="alert alert-danger d-none mt-4 mb-0" role="alert"></div>

                    <div class="wizard-actions">
                        <button type="button" id="prevStepBtn" class="btn btn-outline-secondary" disabled>Regresar</button>
                        <button type="button" id="nextStepBtn" class="btn btn-success">Continuar</button>
                        <button type="button" id="securityCheckBtn" class="btn btn-primary d-none">Revisar seguridad</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="modal fade" id="confirmacionCitaModal" tabindex="-1" aria-labelledby="confirmacionCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content cliente-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmacionCitaModalLabel">Confirmar solicitud de cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Estas seguro de realizar esta cita?</p>
                    <div class="cliente-modal__summary modal-summary">
                        <p class="mb-2"><strong>Fecha:</strong> <span id="modalResumenFecha">Pendiente</span></p>
                        <p class="mb-0"><strong>Hora:</strong> <span id="modalResumenHora">Pendiente</span></p>
                    </div>
                    <div id="modalPasswordBox" class="cliente-modal__password password-validation d-none mt-4">
                        <label for="contrasenaConfirmacionInput" class="form-label">Ingresa tu contrasena</label>
                        <input type="password" id="contrasenaConfirmacionInput" class="form-control" placeholder="Contrasena de validacion">
                        <small class="password-validation__hint">Valida tu identidad para completar la cita.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmarModalBtn" class="btn btn-success">Continuar</button>
                    <button type="button" id="validarModalBtn" class="btn btn-primary d-none" style="display:none;">Validar</button>
                </div>
            </div>
        </div>
    </div>

    <?php render_cliente_dashboard_footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.appBaseUrl = <?php echo json_encode($base_url); ?>;
        window.citasRedirectUrl = <?php echo json_encode($base_url . 'Cliente/Citas.php'); ?>;
    </script>
    <script src="<?php echo $base_url; ?>assets/js/citas.js?v=<?php echo time(); ?>"></script>
</body>
</html>
