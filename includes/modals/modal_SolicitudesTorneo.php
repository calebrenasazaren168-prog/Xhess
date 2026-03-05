<?php
// Obtener todos los torneos del organizador y sus solicitudes
$misTorneosConSol = $sistema->getMisTorneos($_SESSION['user_id']);
$totalSolTorneo = $sistema->contarSolicitudesTorneoPendientes($_SESSION['user_id']);
?>

<div class="modal fade" id="modal-solicitudes-torneo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-envelope-open-text"></i> Solicitudes de Equipos para mis Torneos
                    <?php if ($totalSolTorneo > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $totalSolTorneo ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($misTorneosConSol)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No has creado ningún torneo todavía.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($misTorneosConSol as $t): ?>
                        <?php
                        $solicitudesPend = $sistema->getSolicitudesTorneoPendientes($t['id_torneo']);
                        $solicitudesHist = $sistema->getSolicitudesTorneoTodas($t['id_torneo']);
                        $numPend = count($solicitudesPend);
                        ?>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <i class="fas fa-trophy text-warning me-1"></i>
                                        <?= htmlspecialchars($t['nombre']) ?>
                                        <small class="text-muted ms-2">
                                            — Min: <?= $t['min_jugadores'] ?? '?' ?> / Max: <?= $t['max_jugadores'] ?? '?' ?> jugadores
                                        </small>
                                    </h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($t['fecha_inicio'])) ?> -
                                        <?= date('d/m/Y', strtotime($t['fecha_fin'])) ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-<?= $t['condicion'] === 'en_progreso' ? 'success' : ($t['condicion'] === 'finalizado' ? 'secondary' : 'warning') ?>">
                                        <?= $t['condicion'] === 'en_progreso' ? 'En Progreso' : ($t['condicion'] === 'finalizado' ? 'Finalizado' : 'Pendiente') ?>
                                    </span>
                                    <?php if ($numPend > 0): ?>
                                        <span class="badge bg-danger"><?= $numPend ?> pendiente<?= $numPend > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Solicitudes pendientes -->
                                <?php if (empty($solicitudesPend)): ?>
                                    <p class="text-muted mb-0"><i class="fas fa-inbox me-1"></i>No hay solicitudes pendientes.</p>
                                <?php else: ?>
                                    <h6 class="text-danger"><i class="fas fa-clock me-1"></i>Pendientes</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Equipo</th>
                                                    <th>Club</th>
                                                    <th>Jugadores</th>
                                                    <th>Líder</th>
                                                    <th>Mensaje</th>
                                                    <th>Fecha</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($solicitudesPend as $sol): ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($sol['equipo_nombre']) ?></strong></td>
                                                        <td><?= htmlspecialchars($sol['club_nombre']) ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?= $sol['num_jugadores'] ?></span>
                                                        </td>
                                                        <td><?= htmlspecialchars($sol['lider_usuario']) ?></td>
                                                        <td>
                                                            <?php if ($sol['mensaje']): ?>
                                                                <small class="text-muted"><?= htmlspecialchars($sol['mensaje']) ?></small>
                                                            <?php else: ?>
                                                                <small class="text-muted fst-italic">Sin mensaje</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <small><?= date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if ($t['condicion'] !== 'finalizado'): ?>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="action" value="aceptar_solicitud_torneo">
                                                                    <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                                    <button type="button" class="btn btn-sm btn-success"
                                                                            onclick="confirmarAccion('¿Aceptar al equipo <?= htmlspecialchars($sol['equipo_nombre']) ?> en el torneo?', this.closest(\'form\'))">
                                                                        <i class="fas fa-check"></i> Aceptar
                                                                    </button>
                                                                </form>
                                                                <form method="post" class="d-inline ms-1">
                                                                    <input type="hidden" name="action" value="rechazar_solicitud_torneo">
                                                                    <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                                    <button type="button" class="btn btn-sm btn-danger"
                                                                            onclick="confirmarAccion('¿Rechazar la solicitud del equipo <?= htmlspecialchars($sol['equipo_nombre']) ?>?', this.closest(\'form\'))">
                                                                        <i class="fas fa-times"></i> Rechazar
                                                                    </button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="text-muted">Torneo finalizado</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                                <!-- Historial de solicitudes procesadas -->
                                <?php 
                                $historial = array_filter($solicitudesHist, fn($s) => $s['estado'] !== 'pendiente');
                                if (!empty($historial)): ?>
                                    <details>
                                        <summary class="text-muted" style="cursor:pointer;">
                                            <i class="fas fa-history me-1"></i>Historial (<?= count($historial) ?> solicitudes procesadas)
                                        </summary>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Equipo</th>
                                                        <th>Jugadores</th>
                                                        <th>Estado</th>
                                                        <th>Fecha solicitud</th>
                                                        <th>Fecha respuesta</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($historial as $h): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($h['equipo_nombre']) ?></td>
                                                            <td><?= $h['num_jugadores'] ?></td>
                                                            <td>
                                                                <span class="badge bg-<?= $h['estado'] === 'aceptada' ? 'success' : 'danger' ?>">
                                                                    <?= ucfirst($h['estado']) ?>
                                                                </span>
                                                            </td>
                                                            <td><small><?= date('d/m/Y', strtotime($h['fecha_solicitud'])) ?></small></td>
                                                            <td><small><?= $h['fecha_respuesta'] ? date('d/m/Y', strtotime($h['fecha_respuesta'])) : '—' ?></small></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>