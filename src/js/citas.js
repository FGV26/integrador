document.getElementById('fecha').setAttribute('min', new Date().toISOString().split('T')[0]);

document.getElementById('fecha').addEventListener('change', function () {
    const fecha = this.value;
    const horaSelect = document.getElementById('hora');
    
    if (fecha) {
        fetch('obtener_horas.php?fecha=' + fecha)
            .then(response => response.json())
            .then(data => {
                horaSelect.innerHTML = '<option value="">Seleccione un horario</option>';
                data.forEach(hora => {
                    const option = document.createElement('option');
                    option.value = hora;
                    option.textContent = hora;
                    horaSelect.appendChild(option);
                });
                horaSelect.disabled = false;
            });
    } else {
        horaSelect.innerHTML = '<option value="">Seleccione un horario</option>';
        horaSelect.disabled = true;
    }
});

document.getElementById('citaForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const abogadoNombre = document.querySelector('#abogado_id option:checked').textContent;
    const fechaCita = document.getElementById('fecha').value;
    const horaCita = document.getElementById('hora').value;
    const tipoCaso = document.querySelector('#tipo_de_caso_id option:checked').textContent;
    const mensajeCita = document.getElementById('mensaje').value;

    document.getElementById('abogadoNombre').textContent = abogadoNombre;
    document.getElementById('fechaCita').textContent = fechaCita;
    document.getElementById('horaCita').textContent = horaCita;
    document.getElementById('tipoCaso').textContent = tipoCaso;
    document.getElementById('mensajeCita').textContent = mensajeCita;

    new bootstrap.Modal(document.getElementById('citaConfirmadaModal')).show();
});

function submitForm() {
    const form = document.getElementById('citaForm');

    // Crear una solicitud AJAX para enviar el formulario
    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            // Mostrar la respuesta del servidor en la consola del navegador
            console.log(xhr.responseText);

            // Redirigir a index.php después de aceptar
            window.location.href = 'http://localhost/INTEGRADOR/index.php'; 
        }
    };

    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();

    xhr.send(queryString);
}
