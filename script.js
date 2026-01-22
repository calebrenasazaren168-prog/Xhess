function verClasificacion(idTorneo, nombreTorneo) {
    document.getElementById('clasificacion-contenido').innerHTML = `
    <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Cargando clasificación...</p>
    </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('modal-clasificacion'));
    modal.show();

    document.getElementById('clasificacion-titulo').textContent =
        'Clasificación - ' + nombreTorneo;

    fetch(`ajax_clasificacion.php?id_torneo=${idTorneo}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('clasificacion-contenido').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('clasificacion-contenido').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar la clasificación.
                <br><small>${error.message}</small>
            </div>
            `;
        });
}

function eliminarEquipo(idEquipo, nombreEquipo) {
    if (confirm(`¿Estás seguro de que quieres eliminar el equipo "${nombreEquipo}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('input-eliminar-equipo').value = idEquipo;
        document.getElementById('form-eliminar-equipo').submit();
    }
}

function eliminarTorneo(idTorneo, nombreTorneo) {
    if (confirm(`¿Estás seguro de que quieres eliminar el torneo "${nombreTorneo}"?\n\nEsta acción eliminará todas las rondas, partidas y estadísticas asociadas.\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('input-eliminar-torneo').value = idTorneo;
        document.getElementById('form-eliminar-torneo').submit();
    }
}

function eliminarCuenta(idCuenta, usuario) {
    if (confirm(`¿Estás seguro de que quieres eliminar la cuenta de "${usuario}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('input-eliminar-cuenta').value = idCuenta;
        document.getElementById('form-eliminar-cuenta').submit();
    }
}

function confirmarAccion(mensaje, formElement) {
    if (confirm(mensaje)) {
        if (typeof formElement === 'string') {
            document.getElementById(formElement).submit();
        } else {
            formElement.submit();
        }
    }
}

function confirmarEnvio(formId, mensaje) {
    if (confirm(mensaje)) {
        document.getElementById(formId).submit();
    }
}

function seleccionarResultado(idPartida, resultado, boton) {
    const grupo = boton.closest('.btn-group-vertical');
    grupo.querySelectorAll('.result-btn').forEach(btn => {
        btn.classList.remove('resultado-seleccionado');
    });

    boton.classList.add('resultado-seleccionado');
    document.getElementById('resultado-' + idPartida).value = resultado;
}

function confirmarEnvioFormResultados(form) {
    if (confirm('¿Estás seguro de que quieres guardar todos los cambios de resultados?')) {
        form.submit();
    }
}
