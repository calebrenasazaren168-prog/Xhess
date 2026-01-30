<?php 
$proximaRonda = $sistema->getProximaRonda($torneo['id_torneo']);
$equiposTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
$num_equipos = count($equiposTorneo);
$max_rondas = $num_equipos - 1;
if ($num_equipos % 2 != 0) {
    $max_rondas = $num_equipos;
}

if ($proximaRonda <= $max_rondas): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Generar Nueva Ronda</h6>
        </div>
        <div class="card-body">
            <form method="post" id="form-generar-ronda-<?= $torneo['id_torneo'] ?>">
                <input type="hidden" name="action" value="generar_ronda">
                <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Número de Ronda</label>
                        <input type="number" name="numero_ronda" class="form-control" value="<?= $proximaRonda ?>" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de la Ronda</label>
                        <input type="date" name="fecha_ronda" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Máximo de Rondas</label>
                        <input type="text" class="form-control" value="<?= $max_rondas ?>" readonly>
                        <small class="form-text text-muted">Para <?= $num_equipos ?> equipos</small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="confirmarEnvio('form-generar-ronda-<?= $torneo['id_torneo'] ?>', '¿Generar ronda <?= $proximaRonda ?>?')">
                            Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Se ha alcanzado el máximo de <?= $max_rondas ?> rondas para <?= $num_equipos ?> equipos.
    </div>
<?php endif; ?>

<?php 
$rondasTorneo = $sistema->getRondasTorneo($torneo['id_torneo']);
if (empty($rondasTorneo)): ?>
    <p class="text-muted">No hay rondas generadas para este torneo.</p>
<?php else: ?>
    <?php foreach ($rondasTorneo as $ronda): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Ronda <?= $ronda['numero_ronda'] ?> - <?= date('d/m/Y', strtotime($ronda['fecha_ronda'])) ?></h6>
                <span class="badge bg-info"><?= $ronda['partidas_jugadas'] ?> partidas</span>
            </div>
            <div class="card-body">
                <?php 
                $partidasRonda = $sistema->getPartidasRonda($ronda['id_ronda']);
                if (empty($partidasRonda)): ?>
                    <p class="text-muted">No hay partidas en esta ronda.</p>
                <?php else: ?>
                    <form method="post" class="form-resultados" data-ronda="<?= $ronda['id_ronda'] ?>">
                        <input type="hidden" name="action" value="actualizar_multiples_resultados">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tablero</th>
                                        <th>Blancas</th>
                                        <th>Negras</th>
                                        <th>Resultado Actual</th>
                                        <th>Nuevo Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($partidasRonda as $partida): ?>
                                        <tr>
                                            <td><strong><?= $partida['tablero'] ?></strong></td>
                                            <td>
                                                <?= htmlspecialchars($partida['jugador_blancas_nombre'] . ' ' . $partida['jugador_blancas_apellido']) ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($partida['equipo_blancas_nombre']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($partida['jugador_negras_nombre'] . ' ' . $partida['jugador_negras_apellido']) ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($partida['equipo_negras_nombre']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $partida['resultado'] == '*' ? 'secondary' : ($partida['resultado'] == '1-0' ? 'success' : ($partida['resultado'] == '0-1' ? 'danger' : 'warning')) ?>">
                                                    <?= $partida['resultado'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <input type="hidden" name="resultados[<?= $partida['id_partida'] ?>]" id="resultado-<?= $partida['id_partida'] ?>" value="<?= $partida['resultado'] ?>">
                                                <div class="btn-group-vertical btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-success result-btn <?= $partida['resultado'] == '1-0' ? 'resultado-seleccionado' : '' ?>" 
                                                            onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '1-0', this)">1-0</button>
                                                    <button type="button" class="btn btn-outline-warning result-btn <?= $partida['resultado'] == '1/2' ? 'resultado-seleccionado' : '' ?>" 
                                                            onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '1/2', this)">½-½</button>
                                                    <button type="button" class="btn btn-outline-danger result-btn <?= $partida['resultado'] == '0-1' ? 'resultado-seleccionado' : '' ?>" 
                                                            onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '0-1', this)">0-1</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-primary" onclick="confirmarEnvioFormResultados(this.closest('form'))">
                                <i class="fas fa-save"></i> Confirmar Cambios
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>