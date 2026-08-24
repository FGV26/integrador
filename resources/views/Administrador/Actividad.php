<?php
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
date_default_timezone_set('America/Lima');
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/app/Repositories/ActividadDAO.php';
require_once dirname(__DIR__, 3) . '/app/Repositories/AuditLogDAO.php';
require_once dirname(__DIR__, 3) . '/config/app.php';
require_once __DIR__ . '/config/layout.php';

function actividad_admin_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function actividad_admin_mes(int $mes): string
{
    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $meses[$mes] ?? '';
}

function actividad_admin_calendario(ActividadDAO $actividadDAO, int $year, int $month): array
{
    $primerDia = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $inicio = $primerDia->modify('-' . ((int) $primerDia->format('N') - 1) . ' days');
    $fin = $inicio->modify('+41 days');
    $conteos = $actividadDAO->obtenerConteoNotasPorRango($inicio->format('Y-m-d'), $fin->format('Y-m-d'));
    $recordatorios = $actividadDAO->obtenerConteoRecordatoriosPorRango($inicio->format('Y-m-d'), $fin->format('Y-m-d'));
    $hoy = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d');
    $dias = [];

    for ($i = 0; $i < 42; $i++) {
        $dia = $inicio->modify('+' . $i . ' days');
        $fecha = $dia->format('Y-m-d');
        $dias[] = [
            'fecha' => $fecha,
            'numero' => (int) $dia->format('j'),
            'actual' => $dia->format('m') === sprintf('%02d', $month),
            'hoy' => $fecha === $hoy,
            'notas' => $conteos[$fecha] ?? 0,
            'recordatorios' => $recordatorios[$fecha] ?? 0,
        ];
    }

    return $dias;
}

$base_url = app_base_url();
$hoyLima = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d');
$year = max(2020, min(2100, (int) ($_GET['year'] ?? substr($hoyLima, 0, 4))));
$month = max(1, min(12, (int) ($_GET['month'] ?? (int) substr($hoyLima, 5, 2))));
$selectedDate = $_GET['fecha'] ?? $hoyLima;
$fechaValida = DateTimeImmutable::createFromFormat('Y-m-d', $selectedDate);
$selectedDate = $fechaValida ? $fechaValida->format('Y-m-d') : $hoyLima;

$actividadDAO = new ActividadDAO();
$auditLogDAO = new AuditLogDAO();
$actividadDAO->eliminarRecordatoriosVencidos();
$diasCalendario = actividad_admin_calendario($actividadDAO, $year, $month);
$itemsDia = $actividadDAO->obtenerPorDia($selectedDate);
$logsDia = $auditLogDAO->obtenerPorDia($selectedDate);
$recordatorios = $actividadDAO->obtenerRecordatoriosProximos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad administrativa - Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo actividad_admin_h($base_url); ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo actividad_admin_h($base_url); ?>assets/css/administrador-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="admin-page">
<?php render_administrador_header($base_url, 'actividad'); ?>
<main class="admin-main">
    <div class="admin-shell">
        <section class="admin-page-heading admin-activity-heading">
            <div>
                <p class="admin-eyebrow">Actividad</p>
                <h1 class="admin-title">Calendario operativo</h1>
            </div>
            <button class="admin-button admin-button--gold admin-activity-create-button" type="button" id="activityDrawerOpen">
                <i class="bi bi-journal-plus"></i>
                <span>Agregar actividad</span>
            </button>
        </section>

        <section class="admin-activity-grid">
            <article class="admin-widget admin-activity-calendar-panel">
                <div class="admin-widget__header">
                    <div>
                        <p class="admin-eyebrow">Calendario principal</p>
                        <h2 id="activityCalendarTitle"><?php echo actividad_admin_h(actividad_admin_mes($month) . ' ' . $year); ?></h2>
                    </div>
                    <div class="admin-calendar-nav">
                        <button type="button" class="admin-icon-button" id="activityPrev" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="admin-button admin-button--outline admin-button--small" id="activityToday">Hoy</button>
                        <button type="button" class="admin-icon-button" id="activityNext" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="admin-activity-calendar">
                    <div class="admin-activity-calendar__week"><span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span></div>
                    <div class="admin-activity-calendar__days" id="activityCalendarDays" data-year="<?php echo (int) $year; ?>" data-month="<?php echo (int) $month; ?>">
                        <?php foreach ($diasCalendario as $dia) : ?>
                            <button
                                type="button"
                                class="admin-activity-day<?php echo !$dia['actual'] ? ' is-muted' : ''; ?><?php echo $dia['hoy'] ? ' is-today' : ''; ?><?php echo $dia['recordatorios'] > 0 ? ' has-reminder' : ''; ?><?php echo $dia['fecha'] === $selectedDate ? ' is-selected' : ''; ?><?php echo $dia['notas'] > 0 ? ' has-note' : ''; ?>"
                                data-date="<?php echo actividad_admin_h($dia['fecha']); ?>"
                            >
                                <span><?php echo (int) $dia['numero']; ?></span>
                                <?php if ($dia['notas'] > 0) : ?><strong><?php echo $dia['notas'] > 1 ? (int) $dia['notas'] : ''; ?></strong><?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>

            <aside class="admin-activity-side">
                <article class="admin-widget admin-activity-reminders-panel">
                    <div class="admin-widget__header">
                        <div><p class="admin-eyebrow">Recordatorios</p><h2>Proximos dias</h2></div>
                        <div class="admin-inline-arrows">
                            <button type="button" class="admin-icon-button" id="activityRemindersPrev" aria-label="Recordatorios anteriores"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="admin-icon-button" id="activityRemindersNext" aria-label="Recordatorios siguientes"><i class="bi bi-chevron-down"></i></button>
                        </div>
                    </div>
                    <div class="admin-reminder-window">
                        <div class="admin-reminder-list" id="activityReminders"></div>
                    </div>
                </article>
            </aside>
        </section>

        <section class="admin-activity-detail">
            <article class="admin-widget">
                <div class="admin-widget__header">
                    <div><p class="admin-eyebrow">Notas del dia</p><h2>Notas del dia</h2></div>
                </div>
                <div class="admin-activity-list" id="activityItems"></div>
            </article>
            <article class="admin-widget">
                <div class="admin-widget__header">
                    <div><p class="admin-eyebrow">Auditoria interna</p><h2>Logs del dia</h2></div>
                    <div class="admin-inline-arrows">
                        <button type="button" class="admin-icon-button" id="activityLogsPrev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="admin-icon-button" id="activityLogsNext" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="admin-activity-list" id="activityLogs"></div>
            </article>
        </section>
    </div>
</main>
<div class="admin-activity-drawer" id="activityDrawer" aria-hidden="true">
    <button class="admin-activity-drawer__backdrop" type="button" id="activityDrawerBackdrop" aria-label="Cerrar panel"></button>
    <aside class="admin-activity-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="activityDrawerTitle">
        <div class="admin-activity-drawer__header">
            <div>
                <p class="admin-eyebrow" id="activityDrawerEyebrow">Actividad del dia</p>
                <h2 id="activityDrawerTitle">Agregar actividad</h2>
            </div>
            <button type="button" class="admin-icon-button" id="activityDrawerClose" aria-label="Cerrar panel"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="activityForm" class="admin-activity-form">
            <input type="hidden" name="accion" value="crear">
            <input type="hidden" name="fecha" id="activityFormDate" value="<?php echo actividad_admin_h($selectedDate); ?>">
            <p class="admin-activity-limit" id="activitySelectedLabel">Dia seleccionado: <?php echo actividad_admin_h($selectedDate); ?></p>
            <p class="admin-date-warning d-none" id="activityDateWarning"><i class="bi bi-calendar-event"></i> Estas creando una nota para un dia diferente a hoy.</p>
            <label class="admin-form-field">
                <span>Tipo</span>
                <select class="admin-input" name="tipo" id="activityType">
                    <option value="nota">Nota</option>
                    <option value="recordatorio">Recordatorio</option>
                </select>
            </label>
            <label class="admin-form-field admin-form-field--reminder-date d-none" id="activityReminderDateField">
                <span>Fecha del recordatorio</span>
                <input class="admin-input" type="date" name="fecha_recordatorio" id="activityReminderDate" value="<?php echo actividad_admin_h($selectedDate); ?>" min="<?php echo actividad_admin_h($hoyLima); ?>">
            </label>
            <label class="admin-form-field">
                <span>Titulo</span>
                <input class="admin-input" name="titulo" maxlength="120" required placeholder="Ej. Revisar cambios de cliente">
            </label>
            <label class="admin-form-field" id="activityDetailField">
                <span>Detalle</span>
                <textarea class="admin-input admin-textarea" name="detalle" rows="3" placeholder="Escribe una descripcion breve"></textarea>
            </label>
            <p class="admin-activity-limit" id="activityLimitText">Puedes crear hasta 3 notas por dia.</p>
            <div class="admin-activity-drawer__footer">
                <button type="button" class="admin-button admin-button--outline" id="activityDrawerCancel">Cancelar</button>
                <button class="admin-button admin-button--gold" type="submit"><i class="bi bi-plus-circle"></i> Guardar actividad</button>
            </div>
        </form>
        <section class="admin-note-view d-none" id="activityNoteView">
            <p class="admin-activity-limit" id="activityNoteDate"></p>
            <article class="admin-note-view__content">
                <span>Nota</span>
                <h3 id="activityNoteTitle"></h3>
                <p id="activityNoteDetail"></p>
            </article>
            <div class="admin-activity-drawer__footer">
                <button type="button" class="admin-button admin-button--outline" id="activityNoteClose"><i class="bi bi-arrow-left"></i> Volver</button>
                <button type="button" class="admin-button admin-button--danger" id="activityNoteDelete"><i class="bi bi-trash"></i> Eliminar nota</button>
            </div>
        </section>
    </aside>
</div>
<?php render_administrador_footer(); ?>
<script>
const activityDataUrl = '<?php echo actividad_admin_h($base_url); ?>Administrador/ActividadDatos.php';
const activityCalendarDays = document.getElementById('activityCalendarDays');
const activityCalendarTitle = document.getElementById('activityCalendarTitle');
const activitySelectedLabel = document.getElementById('activitySelectedLabel');
const activityForm = document.getElementById('activityForm');
const activityFormDate = document.getElementById('activityFormDate');
const activityDateWarning = document.getElementById('activityDateWarning');
const activityType = document.getElementById('activityType');
const activityReminderDateField = document.getElementById('activityReminderDateField');
const activityReminderDate = document.getElementById('activityReminderDate');
const activityDetailField = document.getElementById('activityDetailField');
const activityItems = document.getElementById('activityItems');
const activityLogs = document.getElementById('activityLogs');
const activityReminders = document.getElementById('activityReminders');
const activityRemindersPrev = document.getElementById('activityRemindersPrev');
const activityRemindersNext = document.getElementById('activityRemindersNext');
const activityLimitText = document.getElementById('activityLimitText');
const activityDrawer = document.getElementById('activityDrawer');
const activityDrawerEyebrow = document.getElementById('activityDrawerEyebrow');
const activityDrawerTitle = document.getElementById('activityDrawerTitle');
const activityDrawerOpen = document.getElementById('activityDrawerOpen');
const activityDrawerClose = document.getElementById('activityDrawerClose');
const activityDrawerBackdrop = document.getElementById('activityDrawerBackdrop');
const activityDrawerCancel = document.getElementById('activityDrawerCancel');
const activitySubmitButton = activityForm.querySelector('button[type="submit"]');
const activityNoteView = document.getElementById('activityNoteView');
const activityNoteDate = document.getElementById('activityNoteDate');
const activityNoteTitle = document.getElementById('activityNoteTitle');
const activityNoteDetail = document.getElementById('activityNoteDetail');
const activityNoteClose = document.getElementById('activityNoteClose');
const activityNoteDelete = document.getElementById('activityNoteDelete');
let activityYear = Number(activityCalendarDays.dataset.year);
let activityMonth = Number(activityCalendarDays.dataset.month);
let activitySelectedDate = '<?php echo actividad_admin_h($selectedDate); ?>';
const initialItems = <?php echo json_encode($itemsDia); ?>;
const initialLogs = <?php echo json_encode($logsDia); ?>;
const initialReminders = <?php echo json_encode($recordatorios); ?>;
const serverToday = '<?php echo actividad_admin_h($hoyLima); ?>';
const activityPageSize = 3;
const activityReminderPageSize = 5;
let activityAllItems = [];
let activityAllLogs = [];
let activityAllReminders = [];
let activityLogPage = 0;
let activityReminderStart = 0;
let activityReminderDirection = 'down';
let activityIsSubmitting = false;
let activitySelectedNote = null;
let activityIsDeletingNote = false;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function truncateText(value, limit = 180) {
    const text = String(value ?? '').trim();
    return text.length > limit ? `${text.slice(0, limit).trim()}...` : text;
}

function formatDate(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function formatDisplayDate(dateText) {
    const [year, month, day] = dateText.split('-').map(Number);
    return new Intl.DateTimeFormat('es-PE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'America/Lima'
    }).format(new Date(Date.UTC(year, month - 1, day, 12)));
}

function parseActivityDate(dateText) {
    const [year, month, day] = String(dateText || '').split('-').map(Number);
    if (!year || !month || !day) return null;
    return new Date(year, month - 1, day);
}

function isBeforeToday(dateText) {
    const target = parseActivityDate(dateText);
    const today = parseActivityDate(serverToday);
    return Boolean(target && today && target < today);
}

function getReminderCountdown(dateText) {
    const [year, month, day] = dateText.split('-').map(Number);
    const now = new Date();
    const today = new Date(Number(serverToday.slice(0, 4)), Number(serverToday.slice(5, 7)) - 1, Number(serverToday.slice(8, 10)));
    const reminderDate = new Date(year, month - 1, day);
    const diffDays = Math.round((reminderDate - today) / 86400000);

    if (diffDays <= 0) {
        const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
        const remainingMs = Math.max(0, endOfDay - now);
        const hours = Math.floor(remainingMs / 3600000);
        const minutes = Math.floor((remainingMs % 3600000) / 60000);
        return `Quedan ${hours}h ${minutes}m para que termine el dia.`;
    }

    return diffDays === 1 ? 'Falta 1 dia.' : `Faltan ${diffDays} dias.`;
}

function slicePage(items, page, size = activityPageSize) {
    return items.slice(page * size, page * size + size);
}

function clampPage(page, total) {
    return Math.max(0, Math.min(page, Math.max(0, Math.ceil(total / activityPageSize) - 1)));
}

function setArrowState(id, enabled) {
    const button = document.getElementById(id);
    if (button) button.disabled = !enabled;
}

function updateDateWarning() {
    const isReminder = activityType.value === 'recordatorio';
    const isDifferentDay = activitySelectedDate !== serverToday;
    const isNote = activityType.value === 'nota';
    const reminderDateValue = activityReminderDate.value || activitySelectedDate;
    const isPastNoteDay = isNote && isBeforeToday(activitySelectedDate);
    const isPastReminderDay = isReminder && isBeforeToday(reminderDateValue);
    const showWarning = (isNote && (isPastNoteDay || isDifferentDay)) || isPastReminderDay;
    activitySelectedLabel.classList.toggle('d-none', showWarning);
    activityDateWarning.classList.toggle('d-none', !showWarning);
    activityDateWarning.classList.toggle('is-danger', isPastNoteDay || isPastReminderDay);

    if (isPastNoteDay) {
        activityDateWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> No se pueden crear notas en dias pasados. Selecciona hoy o una fecha futura.`;
    } else if (isPastReminderDay) {
        activityDateWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> No se pueden crear recordatorios en dias pasados. Selecciona hoy o una fecha futura.`;
    } else if (showWarning) {
        activityDateWarning.innerHTML = `<i class="bi bi-calendar-event"></i> Estas creando una nota para ${formatDisplayDate(activitySelectedDate)}. Hoy es ${formatDisplayDate(serverToday)}.`;
    }

    if (!activityIsSubmitting) {
        setSubmitDisabled(isPastNoteDay || isPastReminderDay);
    }
}

function shouldDisableActivitySubmit() {
    return (activityType.value === 'nota' && isBeforeToday(activitySelectedDate))
        || (activityType.value === 'recordatorio' && isBeforeToday(activityReminderDate.value || activitySelectedDate));
}

function setSubmitDisabled(disabled) {
    activitySubmitButton.disabled = disabled;
    activitySubmitButton.classList.toggle('is-disabled', disabled);
}

function updateCreateButtonState() {
    const disabled = isBeforeToday(activitySelectedDate);
    activityDrawerOpen.disabled = disabled;
    activityDrawerOpen.classList.toggle('is-disabled', disabled);
    activityDrawerOpen.title = disabled ? 'No se pueden crear actividades en dias pasados.' : '';
}

function setDrawerModeCreate() {
    activitySelectedNote = null;
    activityDrawerEyebrow.textContent = 'Actividad del dia';
    activityDrawerTitle.textContent = 'Agregar actividad';
    activityForm.classList.remove('d-none');
    activityNoteView.classList.add('d-none');
    activityFormDate.value = activitySelectedDate;
    activityReminderDate.value = activitySelectedDate;
    activitySelectedLabel.textContent = `Dia seleccionado: ${formatDisplayDate(activitySelectedDate)}`;
    toggleReminderDateField();
}

function openActivityDrawer() {
    updateCreateButtonState();
    if (activityDrawerOpen.disabled) return;
    setDrawerModeCreate();
    activityDrawer.classList.add('is-open');
    activityDrawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('is-drawer-open');
}

function openNoteDrawer(note) {
    activitySelectedNote = note;
    activityDrawerEyebrow.textContent = 'Lectura completa';
    activityDrawerTitle.textContent = 'Detalle de nota';
    activityForm.classList.add('d-none');
    activityNoteView.classList.remove('d-none');
    activityNoteDate.textContent = `Dia seleccionado: ${formatDisplayDate(activitySelectedDate)}`;
    activityNoteTitle.textContent = note.titulo || 'Nota sin titulo';
    activityNoteDetail.textContent = note.detalle || 'Sin detalle registrado.';
    activityDrawer.classList.add('is-open');
    activityDrawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('is-drawer-open');
}

function closeActivityDrawer() {
    activityDrawer.classList.remove('is-open');
    activityDrawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('is-drawer-open');
}

async function fetchActivity(params, options = {}) {
    const isPost = options.method === 'POST';
    const response = await fetch(isPost ? activityDataUrl : activityDataUrl + '?' + new URLSearchParams(params).toString(), {
        method: options.method || 'GET',
        headers: isPost ? { 'Accept': 'application/json' } : { 'Accept': 'application/json' },
        body: options.body || null
    });
    const payload = await response.json();
    if (!payload.ok) {
        throw new Error(payload.message || 'No se pudo procesar la actividad');
    }
    return payload.data || payload;
}

function setActivityLimitMessage(message, isAlert = false) {
    activityLimitText.textContent = message;
    activityLimitText.classList.toggle('is-alert', isAlert);
    activityLimitText.classList.toggle('d-none', !message);
    activityLimitText.classList.remove('is-ringing');

    if (isAlert) {
        void activityLimitText.offsetWidth;
        activityLimitText.classList.add('is-ringing');
    }
}

function renderItems(items = activityAllItems) {
    activityAllItems = items;
    const notes = activityAllItems.filter((item) => item.tipo === 'nota');
    setActivityLimitMessage(
        notes.length >= 3 ? 'Solo se pueden crear hasta 3 notas por dia.' : `Notas creadas: ${notes.length}/3.`,
        notes.length >= 3
    );

    if (!notes.length) {
        activityItems.innerHTML = '<div class="admin-empty"><i class="bi bi-journal-text"></i><p>No hay notas en este dia.</p></div>';
        return;
    }

    activityItems.innerHTML = notes.map((item, index) => `
        <article class="admin-activity-entry admin-activity-entry--note">
            <i class="bi bi-journal-text"></i>
            <div>
                <span>Nota</span>
                <strong>${escapeHtml(item.titulo)}</strong>
                ${item.detalle ? `<p>${escapeHtml(truncateText(item.detalle))}</p>` : ''}
            </div>
            <button type="button" class="admin-entry-arrow" data-note-index="${index}" aria-label="Leer nota completa">
                <i class="bi bi-chevron-right"></i>
            </button>
        </article>
    `).join('');

    activityItems.querySelectorAll('[data-note-index]').forEach((button) => {
        button.addEventListener('click', () => openNoteDrawer(notes[Number(button.dataset.noteIndex)]));
    });
}

function renderLogs(logs = activityAllLogs) {
    activityAllLogs = logs;
    activityLogPage = clampPage(activityLogPage, activityAllLogs.length);
    setArrowState('activityLogsPrev', activityLogPage > 0);
    setArrowState('activityLogsNext', activityLogPage < Math.ceil(activityAllLogs.length / activityPageSize) - 1);

    if (!activityAllLogs.length) {
        activityLogs.innerHTML = '<div class="admin-empty"><i class="bi bi-clock-history"></i><p>No hay movimientos administrativos en este dia.</p></div>';
        return;
    }

    activityLogs.innerHTML = slicePage(activityAllLogs, activityLogPage).map((log) => `
        <article class="admin-activity-entry">
            <i class="bi bi-clock-history"></i>
            <div>
                <span>${escapeHtml(log.creado_en)}</span>
                <strong>${escapeHtml(log.accion)} ${escapeHtml(log.entidad)}</strong>
                <p>${escapeHtml(log.detalle)} - ${escapeHtml(log.administrador || 'Cuenta eliminada')}</p>
            </div>
        </article>
    `).join('');
}

function renderReminders(reminders = activityAllReminders) {
    activityAllReminders = [...reminders].sort((a, b) => {
        const dateCompare = String(a.fecha).localeCompare(String(b.fecha));
        if (dateCompare !== 0) return dateCompare;
        return String(a.titulo).localeCompare(String(b.titulo));
    });
    const maxStart = Math.max(0, activityAllReminders.length - activityReminderPageSize);
    activityReminderStart = Math.max(0, Math.min(activityReminderStart, maxStart));
    setArrowState('activityRemindersPrev', activityReminderStart > 0);
    setArrowState('activityRemindersNext', activityReminderStart < maxStart);

    if (!activityAllReminders.length) {
        activityReminders.innerHTML = '<div class="admin-empty"><i class="bi bi-bell"></i><p>No hay recordatorios registrados.</p></div>';
        return;
    }

    activityReminders.classList.remove('is-moving-up', 'is-moving-down');
    void activityReminders.offsetWidth;
    activityReminders.classList.add(activityReminderDirection === 'up' ? 'is-moving-up' : 'is-moving-down');

    activityReminders.innerHTML = activityAllReminders.slice(activityReminderStart, activityReminderStart + activityReminderPageSize).map((item) => `
        <button type="button" class="admin-reminder" data-date="${escapeHtml(item.fecha)}">
            <span>${escapeHtml(item.fecha)}</span>
            <strong>${escapeHtml(item.titulo)}</strong>
            <small>${escapeHtml(getReminderCountdown(item.fecha))}</small>
        </button>
    `).join('');

    activityReminders.querySelectorAll('[data-date]').forEach((button) => {
        button.addEventListener('click', () => selectActivityDate(button.dataset.date));
    });
}

function updateDayData(data) {
    activitySelectedDate = data.fecha;
    updateCreateButtonState();
    if (isBeforeToday(activitySelectedDate) && activityForm.classList.contains('d-none') === false) {
        closeActivityDrawer();
    }
    activitySelectedLabel.textContent = `Dia seleccionado: ${formatDisplayDate(data.fecha)}`;
    activityFormDate.value = data.fecha;
    activityReminderDate.value = data.fecha;
    updateDateWarning();
    activityLogPage = 0;
    renderItems(data.items || []);
    renderLogs(data.logs || []);
    renderReminders(data.recordatorios || []);
}

async function loadActivityDay(date) {
    const data = await fetchActivity({ accion: 'dia', fecha: date });
    updateDayData(data);
}

function renderCalendar(days) {
    activityCalendarDays.innerHTML = days.map((day) => {
        const classes = [
            'admin-activity-day',
            day.actual ? '' : 'is-muted',
            day.hoy ? 'is-today' : '',
            day.recordatorios > 0 ? 'has-reminder' : '',
            day.fecha === activitySelectedDate ? 'is-selected' : '',
            day.notas > 0 ? 'has-note' : ''
        ].filter(Boolean).join(' ');
        const badge = day.notas > 0 ? `<strong>${day.notas > 1 ? day.notas : ''}</strong>` : '';
        return `<button type="button" class="${classes}" data-date="${day.fecha}"><span>${day.numero}</span>${badge}</button>`;
    }).join('');

    bindActivityDays();
}

async function loadActivityMonth(year, month, forcedDate = null) {
    const data = await fetchActivity({ accion: 'calendario_mes', year, month });
    activityYear = Number(data.year);
    activityMonth = Number(data.month);
    activityCalendarDays.dataset.year = activityYear;
    activityCalendarDays.dataset.month = activityMonth;
    activityCalendarTitle.textContent = data.titulo;
    if (forcedDate) {
        activitySelectedDate = forcedDate;
    }
    renderCalendar(data.dias);
    await loadActivityDay(activitySelectedDate);
}

async function selectActivityDate(date) {
    const [year, month] = date.split('-').map(Number);
    activitySelectedDate = date;
    if (year !== activityYear || month !== activityMonth) {
        await loadActivityMonth(year, month, date);
        return;
    }
    activityCalendarDays.querySelectorAll('.admin-activity-day').forEach((button) => button.classList.remove('is-selected'));
    const selected = activityCalendarDays.querySelector(`[data-date="${date}"]`);
    if (selected) selected.classList.add('is-selected');
    await loadActivityDay(date);
}

function bindActivityDays() {
    activityCalendarDays.querySelectorAll('.admin-activity-day').forEach((button) => {
        button.addEventListener('click', () => selectActivityDate(button.dataset.date));
    });
}

document.getElementById('activityPrev').addEventListener('click', () => {
    const previous = new Date(activityYear, activityMonth - 2, 1);
    loadActivityMonth(previous.getFullYear(), previous.getMonth() + 1, `${previous.getFullYear()}-${String(previous.getMonth() + 1).padStart(2, '0')}-01`);
});

document.getElementById('activityNext').addEventListener('click', () => {
    const next = new Date(activityYear, activityMonth, 1);
    loadActivityMonth(next.getFullYear(), next.getMonth() + 1, `${next.getFullYear()}-${String(next.getMonth() + 1).padStart(2, '0')}-01`);
});

document.getElementById('activityToday').addEventListener('click', () => {
    const [year, month] = serverToday.split('-').map(Number);
    loadActivityMonth(year, month, serverToday);
});

document.getElementById('activityLogsPrev').addEventListener('click', () => {
    activityLogPage = Math.max(0, activityLogPage - 1);
    renderLogs();
});

document.getElementById('activityLogsNext').addEventListener('click', () => {
    const lastPage = Math.max(0, Math.ceil(activityAllLogs.length / activityPageSize) - 1);
    activityLogPage = Math.min(lastPage, activityLogPage + 1);
    renderLogs();
});

activityRemindersPrev.addEventListener('click', () => {
    activityReminderDirection = 'up';
    activityReminderStart = Math.max(0, activityReminderStart - 1);
    renderReminders();
});

activityRemindersNext.addEventListener('click', () => {
    const maxStart = Math.max(0, activityAllReminders.length - activityReminderPageSize);
    activityReminderDirection = 'down';
    activityReminderStart = Math.min(maxStart, activityReminderStart + 1);
    renderReminders();
});

activityForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    updateDateWarning();
    if (shouldDisableActivitySubmit()) return;
    if (activityIsSubmitting) return;
    activityIsSubmitting = true;
    setSubmitDisabled(true);
    const formData = new FormData(activityForm);
    try {
        const data = await fetchActivity({}, { method: 'POST', body: formData });
        activityForm.reset();
        activityFormDate.value = activitySelectedDate;
        activityReminderDate.value = activitySelectedDate;
        toggleReminderDateField();
        updateDayData(data);
        await loadActivityMonth(activityYear, activityMonth, activitySelectedDate);
        closeActivityDrawer();
    } catch (error) {
        setActivityLimitMessage(error.message, true);
    } finally {
        activityIsSubmitting = false;
        setSubmitDisabled(shouldDisableActivitySubmit());
    }
});

activityNoteDelete.addEventListener('click', async () => {
    if (!activitySelectedNote || activityIsDeletingNote) return;
    const confirmed = window.confirm('Estas seguro de eliminar esta nota?');
    if (!confirmed) return;

    activityIsDeletingNote = true;
    activityNoteDelete.disabled = true;
    const formData = new FormData();
    formData.append('accion', 'eliminar_nota');
    formData.append('id', activitySelectedNote.id);

    try {
        const data = await fetchActivity({}, { method: 'POST', body: formData });
        updateDayData(data);
        await loadActivityMonth(activityYear, activityMonth, activitySelectedDate);
        closeActivityDrawer();
    } catch (error) {
        activityNoteDate.textContent = error.message;
        activityNoteDate.classList.add('is-alert', 'is-ringing');
    } finally {
        activityIsDeletingNote = false;
        activityNoteDelete.disabled = false;
    }
});

function toggleReminderDateField() {
    const isReminder = activityType.value === 'recordatorio';
    activityReminderDateField.classList.toggle('d-none', !isReminder);
    activityReminderDate.required = isReminder;
    activityDetailField.classList.toggle('d-none', isReminder);
    updateDateWarning();
    if (isReminder) {
        setActivityLimitMessage('', false);
    } else {
        renderItems();
    }
}

activityType.addEventListener('change', toggleReminderDateField);
activityReminderDate.addEventListener('change', updateDateWarning);
activityDrawerOpen.addEventListener('click', openActivityDrawer);
activityDrawerClose.addEventListener('click', closeActivityDrawer);
activityDrawerBackdrop.addEventListener('click', closeActivityDrawer);
activityDrawerCancel.addEventListener('click', closeActivityDrawer);
activityNoteClose.addEventListener('click', closeActivityDrawer);
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activityDrawer.classList.contains('is-open')) {
        closeActivityDrawer();
    }
});

bindActivityDays();
toggleReminderDateField();
updateDayData({ fecha: activitySelectedDate, items: initialItems, logs: initialLogs, recordatorios: initialReminders });
</script>
</body>
</html>
