<!-- Modal solicitar unirse a torneo -->
<?php if ($sistema->isLoggedIn() && $sistema->isJugador()): ?>
<div class="modal fade" id="modal-solicitud-torneo" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="fas fa-trophy"></i>
                    Solicitar ingreso a <span id="nombre-torneo-solicitud"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="form-solicitud-torneo">
                    <input type="hidden" name="action" value="solicitar_torneo">
                    <input type="hidden" name="id_torneo" id="id-torneo-solicitud">
                    <input type="hidden" name="id_equipo" id="id-equipo-solicitud-torneo">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Equipo participante:</label>
                        <div id="info-equipo-solicitud" class="alert alert-info py-2 mb-0"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensaje al organizador <small class="text-muted">(opcional)</small></label>
                        <textarea name="mensaje" class="form-control" rows="3"
                                  placeholder="Preséntate o añade información relevante..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitud
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>