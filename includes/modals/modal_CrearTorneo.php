<div class="modal fade" id="crear-torneo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Torneo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" id="form-crear-torneo">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="crear_torneo">
                            <input type="hidden" name="id_cuenta" value="<?= $_SESSION['user_id'] ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del Torneo *</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sistema de Juego *</label>
                                    <select name="sistema" class="form-control" required>
                                        <option value="round_robin">Round Robin Clásico</option>
                                        <option value="round_robin_ponderado" selected>Round Robin Ponderado</option>
                                        <option value="suizo">Sistema Suizo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número de Tableros *</label>
                                    <input type="number" name="nro_tableros" class="form-control" value="4" min="1" max="10" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ritmo de Tiempo *</label>
                                    <select name="ritmo_tiempo" class="form-control" required>
                                        <option value="3min">3 minutos</option>
                                        <option value="5min">5 minutos</option>
                                        <option value="10min">10 minutos</option>
                                        <option value="15min+10s">15min + 10s</option>
                                        <option value="60+30" selected>60min + 30s</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe tu torneo..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Inicio *</label>
                                    <input type="date" name="fecha_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin *</label>
                                    <input type="date" name="fecha_fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ubicación</label>
                                    <input type="text" name="ubicacion" class="form-control" placeholder="Ej: CBTIS 168 - Gimnasio">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Club *</label>
                                    <select name="id_club" class="form-control" required>
                                        <?php foreach ($sistema->getClubes() as $club): ?>
                                            <option value="<?= $club['id_club'] ?>" <?= $club['id_club'] == 5 ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($club['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('¿Crear este torneo?')">
                                <i class="fas fa-trophy"></i> Crear Torneo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>