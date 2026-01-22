<div class="modal fade" id="mis-torneos" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mis Torneos - Gestión Completa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <?php 
                            $misTorneos = $sistema->getMisTorneos($_SESSION['user_id']);
                            if (empty($misTorneos)): ?>
                                <div class="col-12 text-center">
                                    <p class="text-muted">No has creado ningún torneo todavía.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($misTorneos as $torneo): ?>
                                    <div class="col-12 mb-5">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= htmlspecialchars($torneo['nombre']) ?></h5>
                                                    <small><?= htmlspecialchars($torneo['descripcion'] ?: 'Sin descripción') ?></small>
                                                </div>
                                                <span class="badge bg-<?= $torneo['condicion'] == 'en_progreso' ? 'success' : ($torneo['condicion'] == 'finalizado' ? 'secondary' : 'warning') ?>">
                                                    <?= $torneo['condicion'] == 'en_progreso' ? 'En Progreso' : ($torneo['condicion'] == 'finalizado' ? 'Finalizado' : 'Pendiente') ?>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <ul class="nav nav-tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab" href="#equipos-<?= $torneo['id_torneo'] ?>">Equipos</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#clasificacion-<?= $torneo['id_torneo'] ?>">Clasificación</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#rondas-<?= $torneo['id_torneo'] ?>">Rondas</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#emparejamientos-<?= $torneo['id_torneo'] ?>">Emparejamientos</a>
                                                    </li>
                                                </ul>
                                                
                                                <div class="tab-content mt-3">
                                                    <div class="tab-pane fade show active" id="equipos-<?= $torneo['id_torneo'] ?>">
                                                        <h6>Equipos del Torneo</h6>
                                                        <?php 
                                                        $equiposTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
                                                        $tieneRondas = $sistema->tieneRondasGeneradas($torneo['id_torneo']);
                                                        
                                                        if (empty($equiposTorneo)): ?>
                                                            <p class="text-muted">No hay equipos en este torneo.</p>
                                                        <?php else: ?>
                                                            <div class="row">
                                                                <?php foreach ($equiposTorneo as $equipo): ?>
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card">
                                                                            <div class="card-body">
                                                                                <h6><?= htmlspecialchars($equipo['nombre']) ?></h6>
                                                                                <?php 
                                                                                $jugadoresEquipo = $sistema->getJugadoresPorEquipo($equipo['id_equipo']);
                                                                                if (!empty($jugadoresEquipo)): ?>
                                                                                    <ul class="list-unstyled">
                                                                                        <?php foreach ($jugadoresEquipo as $jugador): ?>
                                                                                            <li>
                                                                                                <small><?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                                                                <span class="badge bg-secondary"><?= $jugador['rating'] ?></span></small>
                                                                                            </li>
                                                                                        <?php endforeach; ?>
                                                                                    </ul>
                                                                                <?php endif; ?>
                                                                                
                                                                                <!-- Mostrar opción de retirar equipo si ya hay rondas generadas -->
                                                                                <?php if ($tieneRondas): ?>
                                                                                    <form method="post" class="form-retirar-equipo mt-2">
                                                                                        <input type="hidden" name="action" value="retirar_equipo">
                                                                                        <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">
                                                                                        <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                                                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                                                onclick="confirmarAccion('¿Retirar el equipo <?= htmlspecialchars($equipo['nombre']) ?> del torneo? El equipo recibirá BYE en las rondas restantes.', this.closest('form'))">
                                                                                            <i class="fas fa-door-open"></i> Retirar Equipo (BYE)
                                                                                        </button>
                                                                                    </form>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Solo permitir agregar equipos si no hay rondas generadas -->
                                                        <?php if (!$tieneRondas): ?>
                                                        <div class="card mt-3">
                                                        <div class="card-header">
                                                        <h6 class="mb-0">Agregar Equipos al Torneo</h6>
                                                        </div>
                                                        <div class="card-body">
                                                        <form method="post" id="form-agregar-equipos-<?= $torneo['id_torneo'] ?>">
                                                        <input type="hidden" name="action" value="agregar_equipos_multiples">
                                                        <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">

                                                        <div class="mb-3">
                                                        <label class="form-label">Seleccionar Equipos (Múltiple)</label>
                                                        <select name="id_equipos[]" class="form-control" multiple size="6" required style="height: 150px;">
                                                        <?php
                                                        $equiposDisponibles = $sistema->getEquiposDisponibles();
                                                        $equiposEnTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
                                                        $idsEquiposEnTorneo = array_column($equiposEnTorneo, 'id_equipo');

                                                        foreach ($equiposDisponibles as $equipo):
                                                            if (!in_array($equipo['id_equipo'], $idsEquiposEnTorneo)):
                                                                ?>
                                                                <option value="<?= $equipo['id_equipo'] ?>">
                                                                <?= htmlspecialchars($equipo['nombre']) ?>
                                                                (<?= htmlspecialchars($equipo['club_nombre']) ?>)
                                                                </option>
                                                                <?php
                                                                endif;
                                                            endforeach;
                                                        ?>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                        Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples equipos
                                                        </small>
                                                        </div>

                                                        <button type="button" class="btn btn-success"
                                                        onclick="confirmarEnvio('form-agregar-equipos-<?= $torneo['id_torneo'] ?>', '¿Agregar los equipos seleccionados al torneo?')">
                                                        <i class="fas fa-plus-circle"></i> Agregar Equipos Seleccionados
                                                        </button>
                                                        </form>
                                                        </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="alert alert-info mt-3">
                                                        <i class="fas fa-info-circle"></i>
                                                        No se pueden agregar más equipos porque ya se han generado rondas para este torneo.
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="clasificacion-<?= $torneo['id_torneo'] ?>">
                                                        <div class="printable-section">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6>Clasificación de Equipos</h6>
                                                            </div>
                                                            
                                                            <?php 
                                                            $clasificacionEquipos = $sistema->getClasificacionEquipos($torneo['id_torneo']);
                                                            if (empty($clasificacionEquipos)): ?>
                                                                <p class="text-muted">No hay datos de clasificación disponibles.</p>
                                                            <?php else: ?>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>Equipo</th>
                                                                                <th>PJ</th>
                                                                                <th>V</th>
                                                                                <th>E</th>
                                                                                <th>D</th>
                                                                                <th>Pts Match</th>
                                                                                <th>Pts Tab</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php $posicion = 1; ?>
                                                                            <?php foreach ($clasificacionEquipos as $equipo): ?>
                                                                                <tr>
                                                                                    <td><strong><?= $posicion++ ?></strong></td>
                                                                                    <td><?= htmlspecialchars($equipo['equipo_nombre']) ?></td>
                                                                                    <td><?= $equipo['partidas_jugadas'] ?></td>
                                                                                    <td><?= $equipo['victorias'] ?></td>
                                                                                    <td><?= $equipo['empates'] ?></td>
                                                                                    <td><?= $equipo['derrotas'] ?></td>
                                                                                    <td><strong><?= number_format($equipo['puntos_match'], 1) ?></strong></td>
                                                                                    <td><?= number_format($equipo['puntos_tableros'], 1) ?></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                                                <h6>Clasificación de Jugadores</h6>
                                                            </div>
                                                            
                                                            <?php 
                                                            $clasificacionJugadores = $sistema->getClasificacionJugadores($torneo['id_torneo']);
                                                            if (empty($clasificacionJugadores)): ?>
                                                                <p class="text-muted">No hay datos de jugadores disponibles.</p>
                                                            <?php else: ?>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>Jugador</th>
                                                                                <th>Equipo</th>
                                                                                <th>Rating</th>
                                                                                <th>PJ</th>
                                                                                <th>V</th>
                                                                                <th>E</th>
                                                                                <th>D</th>
                                                                                <th>Pts</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php $posicion = 1; ?>
                                                                            <?php foreach ($clasificacionJugadores as $jugador): ?>
                                                                                <tr>
                                                                                    <td><strong><?= $posicion++ ?></strong></td>
                                                                                    <td><?= htmlspecialchars($jugador['nombre_jugador']) ?></td>
                                                                                    <td><?= htmlspecialchars($jugador['equipo_nombre']) ?></td>
                                                                                    <td><?= $jugador['rating'] ?></td>
                                                                                    <td><?= $jugador['partidas_jugadas'] ?></td>
                                                                                    <td><?= $jugador['victorias'] ?></td>
                                                                                    <td><?= $jugador['empates'] ?></td>
                                                                                    <td><?= $jugador['derrotas'] ?></td>
                                                                                    <td><strong><?= number_format($jugador['puntos'], 1) ?></strong></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="rondas-<?= $torneo['id_torneo'] ?>">
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
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="emparejamientos-<?= $torneo['id_torneo'] ?>">
                                                        <div class="printable-section">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6>Emparejamientos del Torneo</h6>
                                                            </div>
                                                            
                                                            <?php
                                                            $emparejamientosTorneo = $sistema->getEmparejamientosTorneo($torneo['id_torneo']);
                                                            if (empty($emparejamientosTorneo)): ?>
                                                                <p class="text-muted">No hay emparejamientos para este torneo.</p>
                                                            <?php else: ?>
                                                                <?php
                                                                // 1. Agrupamos primero todas las partidas por número de ronda
                                                                $emparejamientosPorRonda = [];
                                                                foreach ($emparejamientosTorneo as $emp) {
                                                                    $ronda = $emp['numero_ronda'];
                                                                    $emparejamientosPorRonda[$ronda][] = $emp;
                                                                }

                                                                // 2. Ahora imprimimos cada ronda de forma ordenada y segura
                                                                if (empty($emparejamientosPorRonda)) {
                                                                    ?>
                                                                    <p class="text-muted">No hay emparejamientos registrados para este torneo todavía.</p>
                                                                    <?php
                                                                } else {
                                                                    foreach ($emparejamientosPorRonda as $numero_ronda => $partidasDeEstaRonda) {
                                                                        // Tomamos la fecha de la primera partida (todas de la misma ronda deberían tener la misma fecha)
                                                                        $fecha_ronda = date('d/m/Y', strtotime($partidasDeEstaRonda[0]['fecha_ronda']));
                                                                        ?>
                                                                        <h6 class="mt-4">Ronda <?= $numero_ronda ?> - <?= $fecha_ronda ?></h6>
                                                                        
                                                                        <table class="table table-sm table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Tablero</th>
                                                                                    <th>Blancas</th>
                                                                                    <th>Negras</th>
                                                                                    <th>Resultado</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            <?php foreach ($partidasDeEstaRonda as $emparejamiento): ?>
                                                                                <tr>
                                                                                    <td><?= htmlspecialchars($emparejamiento['tablero']) ?></td>
                                                                                    <td>
                                                                                        <?= htmlspecialchars($emparejamiento['jugador_blancas_nombre'] . ' ' . $emparejamiento['jugador_blancas_apellido']) ?>
                                                                                        <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_blancas_nombre'] ?? '—') ?></small>
                                                                                    </td>
                                                                                    <td>
                                                                                        <?= htmlspecialchars($emparejamiento['jugador_negras_nombre'] . ' ' . $emparejamiento['jugador_negras_apellido']) ?>
                                                                                        <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_negras_nombre'] ?? '—') ?></small>
                                                                                    </td>
                                                                                    <td><?= htmlspecialchars($emparejamiento['resultado'] ?: '*') ?></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>