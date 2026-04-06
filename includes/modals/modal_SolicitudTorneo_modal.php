<?php
/**
 * modal_SolicitudTorneo_modal.php
 * Mini-modal único que se incluye UNA SOLA VEZ en index.php,
 * fuera del foreach de torneos.
 */
if (!$sistema->isLoggedIn()) return;
?>

<!-- ── Modal: Solicitar inscripción en torneo ───────────────────────── -->
<div class="modal fade" id="modal-solicitud-torneo" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="fas fa-trophy"></i>
                    Solicitar inscripción
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="solt-subtitulo"></p>

                <!-- Aviso equipo (solo torneos de equipos) -->
                <div id="solt-equipo-info" class="alert alert-info py-2 small mb-3" style="display:none">
                    <i class="fas fa-shield-alt me-1"></i>
                    Equipo: <strong id="solt-equipo-nombre"></strong>
                </div>

                <form method="post" id="form-solicitud-torneo">
                    <input type="hidden" name="action"    value="solicitar_torneo">
                    <input type="hidden" name="id_torneo" id="solt-id-torneo">
                    <input type="hidden" name="id_equipo" id="solt-id-equipo">

                    <div class="mb-3">
                        <label class="form-label">Mensaje al organizador
                            <small class="text-muted">(opcional)</small>
                        </label>
                        <textarea name="mensaje" class="form-control" rows="3"
                                  placeholder="Preséntate o añade información relevante..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-1"></i> Enviar Solicitud
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function abrirSolicitudTorneo(idTorneo, nombreTorneo, esEquipos, idEquipo, nombreEquipo) {
    document.getElementById('solt-id-torneo').value   = idTorneo;
    document.getElementById('solt-id-equipo').value   = idEquipo || '';
    document.getElementById('solt-subtitulo').textContent =
        'Torneo: ' + nombreTorneo + (esEquipos ? ' (por equipos)' : ' (individual)');

    const equipoInfo   = document.getElementById('solt-equipo-info');
    const equipoNombre = document.getElementById('solt-equipo-nombre');
    if (esEquipos && nombreEquipo) {
        equipoNombre.textContent = nombreEquipo;
        equipoInfo.style.display = 'block';
    } else {
        equipoInfo.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('modal-solicitud-torneo')).show();
}
</script>
