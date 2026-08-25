const form = document.getElementById('citaForm');
const panels = Array.from(document.querySelectorAll('.wizard-panel'));
const stepIndicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
const prevStepBtn = document.getElementById('prevStepBtn');
const nextStepBtn = document.getElementById('nextStepBtn');
const securityCheckBtn = document.getElementById('securityCheckBtn');
const errorBox = document.getElementById('wizardError');
const wizardNotice = document.getElementById('wizardNotice');

const abogadoSelect = document.getElementById('abogado_id');
const tipoCasoSelect = document.getElementById('tipo_de_caso_id');
const mensajeInput = document.getElementById('mensaje');
const fechaInput = document.getElementById('fechaSeleccionada');
const horaInput = document.getElementById('horaSeleccionada');
const metodoPagoInput = document.getElementById('metodoPagoSeleccionado');

const dayOptions = document.getElementById('dayOptions');
const hourOptions = document.getElementById('hourOptions');
const selectedDayLabel = document.getElementById('selectedDayLabel');
const paymentCards = Array.from(document.querySelectorAll('.payment-card'));
const modalPasswordBox = document.getElementById('modalPasswordBox');
const passwordInput = document.getElementById('contrasenaConfirmacionInput');
const passwordHiddenInput = document.getElementById('contrasenaConfirmacionHidden');
const modalResumenFecha = document.getElementById('modalResumenFecha');
const modalResumenHora = document.getElementById('modalResumenHora');
const confirmarModalBtn = document.getElementById('confirmarModalBtn');
const validarModalBtn = document.getElementById('validarModalBtn');
const confirmacionModalElement = document.getElementById('confirmacionCitaModal');
const confirmacionModal = confirmacionModalElement ? new bootstrap.Modal(confirmacionModalElement) : null;

let currentStep = 1;
let selectedDay = '';
let selectedHour = '';

renderDayCards();
renderHourPlaceholder('Selecciona un dia para ver horarios.');
bindEvents();
updateWizard();
setupAutoDismissNotice();

function bindEvents() {
    nextStepBtn.addEventListener('click', () => {
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < 4) {
            currentStep += 1;
            updateWizard();
        }
    });

    prevStepBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            hideError();
            currentStep -= 1;
            updateWizard();
        }
    });

    form.addEventListener('submit', event => {
        if (!validateStep(4)) {
            event.preventDefault();
        }
    });

    securityCheckBtn.addEventListener('click', () => {
        if (!validateStep(4, { requirePassword: false })) {
            return;
        }

        resetModalValidationState();
        modalResumenFecha.textContent = selectedDay ? formatDateLong(selectedDay) : 'Pendiente';
        modalResumenHora.textContent = selectedHour ? formatHour(selectedHour) : 'Pendiente';
        confirmacionModal.show();
    });

    confirmarModalBtn.addEventListener('click', () => {
        modalPasswordBox.classList.remove('d-none');
        modalPasswordBox.style.display = 'block';
        confirmarModalBtn.classList.add('d-none');
        validarModalBtn.classList.remove('d-none');
        validarModalBtn.style.display = 'inline-block';
        setTimeout(() => passwordInput.focus(), 150);
    });

    validarModalBtn.addEventListener('click', () => {
        if (!validateStep(4)) {
            return;
        }

        passwordHiddenInput.value = passwordInput.value;
        form.submit();
    });

    abogadoSelect.addEventListener('change', updateSummary);
    tipoCasoSelect.addEventListener('change', updateSummary);
    mensajeInput.addEventListener('input', updateSummary);

    paymentCards.forEach(card => {
        card.addEventListener('click', () => {
            paymentCards.forEach(item => item.classList.remove('is-selected'));
            card.classList.add('is-selected');
            metodoPagoInput.value = card.dataset.payment;
            hideError();
            updateSummary();
        });
    });

    if (confirmacionModalElement) {
        confirmacionModalElement.addEventListener('hidden.bs.modal', resetModalValidationState);
    }
}

function updateWizard() {
    panels.forEach(panel => {
        const step = Number(panel.dataset.step);
        panel.classList.toggle('is-active', step === currentStep);
    });

    stepIndicators.forEach(indicator => {
        const step = Number(indicator.dataset.stepIndicator);
        const number = indicator.querySelector('.wizard-step__number');
        indicator.classList.toggle('is-active', step === currentStep);
        indicator.classList.toggle('is-complete', step < currentStep);
        number.textContent = step < currentStep ? '✓' : String(step);
    });

    prevStepBtn.disabled = currentStep === 1;
    nextStepBtn.classList.toggle('d-none', currentStep === 4);
    securityCheckBtn.classList.toggle('d-none', currentStep !== 4);

    if (currentStep >= 3) {
        updateSummary();
    }

    hideError();
}

function validateStep(step, options = {}) {
    const requirePassword = options.requirePassword !== false;

    if (step === 1) {
        if (!abogadoSelect.value) {
            return showError('Selecciona un abogado para continuar.');
        }

        if (!tipoCasoSelect.value) {
            return showError('Selecciona el tipo de caso.');
        }

        if (!mensajeInput.value.trim()) {
            return showError('Escribe una breve descripcion de tu cita.');
        }

    }

    if (step === 2) {
        if (!selectedDay) {
            return showError('Selecciona uno de los dias disponibles.');
        }

        if (!selectedHour) {
            return showError('Selecciona un horario disponible para continuar.');
        }
    }

    if (step === 4) {
        if (!metodoPagoInput.value) {
            return showError('Selecciona un metodo de pago referencial antes de confirmar.');
        }

        if (requirePassword) {
            if (!passwordInput.value.trim()) {
                return showError('Ingresa tu contrasena de validacion para agendar la cita.');
            }
        }
    }

    hideError();
    return true;
}

function showError(message) {
    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
    setTimeout(() => {
        if (errorBox.textContent === message) {
            hideError();
        }
    }, 4000);
    return false;
}

function hideError() {
    errorBox.textContent = '';
    errorBox.classList.add('d-none');
}

function setupAutoDismissNotice() {
    if (!wizardNotice) {
        return;
    }

    setTimeout(() => {
        wizardNotice.classList.add('d-none');
    }, 4000);
}

function renderDayCards() {
    const days = buildNextThreeDays();

    dayOptions.innerHTML = '';
    days.forEach(day => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'selection-card';
        button.dataset.date = day.iso;
        button.innerHTML = `
            <span class="selection-card__eyebrow">${day.weekday}</span>
            <div class="selection-card__title">${day.dayLabel}</div>
            <p class="selection-card__text">${day.monthLabel}</p>
        `;

        button.addEventListener('click', () => {
            document.querySelectorAll('#dayOptions .selection-card').forEach(card => {
                card.classList.remove('is-selected');
            });

            button.classList.add('is-selected');
            selectedDay = day.iso;
            fechaInput.value = day.iso;
            selectedHour = '';
            horaInput.value = '';
            selectedDayLabel.textContent = `${day.weekday}, ${day.dayLabel} de ${day.monthLabel}`;
            loadAvailableHours(day.iso);
            updateSummary();
            hideError();
        });

        dayOptions.appendChild(button);
    });
}

function buildNextThreeDays() {
    const formatterWeekday = new Intl.DateTimeFormat('es-PE', { weekday: 'long' });
    const formatterMonth = new Intl.DateTimeFormat('es-PE', { month: 'long' });
    const formatterDay = new Intl.DateTimeFormat('es-PE', { day: 'numeric' });
    const days = [];
    const baseDate = new Date();

    for (let offset = 1; offset <= 3; offset += 1) {
        const date = new Date(baseDate);
        date.setDate(baseDate.getDate() + offset);

        const iso = formatLocalIsoDate(date);
        const weekday = capitalize(formatterWeekday.format(date));
        const monthLabel = capitalize(formatterMonth.format(date));
        const dayLabel = formatterDay.format(date);

        days.push({ iso, weekday, monthLabel, dayLabel });
    }

    return days;
}

function loadAvailableHours(date) {
    const baseUrl = window.appBaseUrl || '/';
    renderHourPlaceholder('Cargando horarios disponibles...');

    fetch(baseUrl + 'Api/ObtenerHoras.php?fecha=' + encodeURIComponent(date))
        .then(response => response.json())
        .then(hours => {
            renderHourCards(Array.isArray(hours) ? hours : []);
        })
        .catch(() => {
            renderHourPlaceholder('No se pudieron cargar los horarios. Intenta nuevamente.');
        });
}

function renderHourCards(hours) {
    hourOptions.innerHTML = '';

    if (hours.length === 0) {
        renderHourPlaceholder('No hay horarios libres para este dia.');
        return;
    }

    hours.forEach(hour => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'selection-card';
        button.dataset.hour = hour;
        button.innerHTML = `
            <div class="selection-card__time">${formatHour(hour)}</div>
            <p class="selection-card__text">Disponible</p>
        `;

        button.addEventListener('click', () => {
            document.querySelectorAll('#hourOptions .selection-card').forEach(card => {
                card.classList.remove('is-selected');
            });

            button.classList.add('is-selected');
            selectedHour = hour;
            horaInput.value = hour;
            updateSummary();
            hideError();
        });

        hourOptions.appendChild(button);
    });
}

function renderHourPlaceholder(message) {
    hourOptions.innerHTML = `<div class="selection-empty">${message}</div>`;
}


function updateSummary() {
    const abogadoText = abogadoSelect.options[abogadoSelect.selectedIndex]?.text || 'Pendiente';
    const tipoCasoText = tipoCasoSelect.options[tipoCasoSelect.selectedIndex]?.text || 'Pendiente';
    const fechaText = selectedDay ? formatDateLong(selectedDay) : 'Pendiente';
    const horaText = selectedHour ? formatHour(selectedHour) : 'Pendiente';
    const mensajeText = mensajeInput.value.trim() || 'Pendiente';
    const metodoPagoText = metodoPagoInput.value ? capitalize(metodoPagoInput.value) : 'Pendiente';
    const pdfText = documentoPdfInput && documentoPdfInput.files.length > 0 ? 'Adjunto' : 'No adjunto';

    document.getElementById('summaryAbogado').textContent = abogadoText;
    document.getElementById('summaryTipoCaso').textContent = tipoCasoText;
    document.getElementById('summaryFecha').textContent = fechaText;
    document.getElementById('summaryHora').textContent = horaText;
    document.getElementById('summaryMensaje').textContent = mensajeText;
    document.getElementById('finalSummaryText').textContent = `${fechaText} a las ${horaText} - Pago: ${metodoPagoText}`;
}

function formatHour(hour) {
    const [hours, minutes] = hour.split(':');
    return `${hours}:${minutes}`;
}

function formatDateLong(isoDate) {
    const date = new Date(isoDate + 'T12:00:00');
    const formatter = new Intl.DateTimeFormat('es-PE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    return capitalize(formatter.format(date));
}

function formatLocalIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function capitalize(value) {
    if (!value) {
        return '';
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
}

function resetModalValidationState() {
    modalPasswordBox.classList.add('d-none');
    modalPasswordBox.style.display = 'none';
    confirmarModalBtn.classList.remove('d-none');
    validarModalBtn.classList.add('d-none');
    validarModalBtn.style.display = 'none';
    passwordInput.value = '';
    passwordHiddenInput.value = '';
}
