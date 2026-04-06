<?php
/**
 * tab_solicitudes_torneo.php
 * Pestaña "Solicitudes" dentro del modal Mis Torneos.
 * Variable disponible: $torneo  (array con datos del torneo actual)
 */

$solicitudes_torneo = $sistema->getSolicitudesTorneo($torneo['id_torneo']);
$pendientes_tor = array_filter($solicitudes_torneo, fn($s) => $s['estado'] === 'pendiente');
$historial_tor  = array_filter($solicitudes_torneo, fn($s) => $s['estado'] !== 'pendiente');
$es_equipos     = ($torneo['tipo'] === 'equipos');
?>

<div class="solicitudes-torneo-tab">

    <?php if (empty($solicitudes_torneo)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
            <p class="mb-0">Aún no hay solicitudes para este torneo.</p>
            <small>El botón "Solicitar Inscripción" aparece en la tarjeta pública del torneo.</small>
        </div>

    <?php else: ?>

        <!-- ── Pendientes ─────────────────────────────────────────────────────── -->
        <?php if (!empty($pendientes_tor)): ?>
        <div class="mb-4">
            <h6 class="text-warning mb-3">
                <i class="fas fa-clock me-1"></i> Pendientes de revisión
                <span class="badge bg-warning text-dark ms-1"><?= count($pendientes_tor) ?></span>
            </h6>

            <?php foreach ($pendientes_tor as $sol): ?>
            <div class="card mb-3 border-start border-warning border-3 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">

                        <!-- Datos del solicitante -->
                        <div class="col-md-7">
                            <?php if ($es_equipos && $sol['equipo_nombre']): ?>
                                <h6 class="mb-1">
                                    <i class="fas fa-shield-alt text-primary me-1"></i>
                                    <?= htmlspecialchars($sol['equipo_nombre']) ?>
                                    <?php if ($sol['club_nombre']): ?>
                                        <small class="text-muted fw-normal">
                                            — <?= htmlspecialchars($sol['club_nombre']) ?>
                                        </small>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    Capitán: <strong><?= htmlspecialchars($sol['jugador_nombre'] . ' ' . $sol['jugador_apellido']) ?></strong>
                                    (@<?= htmlspecialchars($sol['usuario']) ?>)
                                    <?php if ($sol['rating']): ?>
                                        · <span class="badge bg-secondary"><?= $sol['rating'] ?></span>
                                    <?php endif; ?>
                                </small>
                            <?php else: ?>
                                <h6 class="mb-1">
                                    <i class="fas fa-user-circle text-primary me-1"></i>
                                    <?= htmlspecialchars(($sol['jugador_nombre'] ?? '') . ' ' . ($sol['jugador_apellido'] ?? '')) ?>
                                    <small class="text-muted fw-normal">(@<?= htmlspecialchars($sol['usuario']) ?>)</small>
                                </h6>
                                <?php if ($sol['rating']): ?>
                                    <small class="text-muted">
                                        Rating: <span class="badge bg-secondary"><?= $sol['rating'] ?></span>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Mensaje opcional -->
                            <?php if (!empty($sol['mensaje'])): ?>
                            <div class="mt-2 fst-italic text-secondary small border-start border-2 ps-2">
                                "<?= htmlspecialchars($sol['mensaje']) ?>"
                            </div>
                            <?php endif; ?>

                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) ?>
                                </small>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action"       value="aceptar_solicitud_torneo">
                                <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                <button type="button" class="btn btn-success btn-sm"
                                        onclick="confirmarAccion(
                                            '<?= $es_equipos
                                                ? "¿Aceptar al equipo " . htmlspecialchars($sol['equipo_nombre'] ?? '', ENT_QUOTES) . " en el torneo? El equipo quedará inscrito automáticamente."
                                                : "¿Aceptar la solicitud de " . htmlspecialchars(($sol['jugador_nombre'] ?? '') . ' ' . ($sol['jugador_apellido'] ?? ''), ENT_QUOTES) . "?" ?>',
                                            this.closest('form'))">
                                    <i class="fas fa-check me-1"></i> Aceptar
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action"       value="rechazar_solicitud_torneo">
                                <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="confirmarAccion(
                                            '¿Rechazar esta solicitud?',
                                            this.closest('form'))">
                                    <i class="fas fa-times me-1"></i> Rechazar
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── Historial ──────────────────────────────────────────────────────── -->
        <?php if (!empty($historial_tor)): ?>
        <div>
            <h6 class="text-muted mb-3">
                <i class="fas fa-history me-1"></i> Historial
            </h6>
            <div class="list-group">
                <?php foreach (array_values($historial_tor) as $sol): ?>
                <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <?php if ($es_equipos && $sol['equipo_nombre']): ?>
                            <strong><i class="fas fa-shield-alt text-primary me-1"></i><?= htmlspecialchars($sol['equipo_nombre']) ?></strong>
                            <small class="text-muted ms-2">cap. @<?= htmlspecialchars($sol['usuario']) ?></small>
                        <?php else: ?>
                            <strong><?= htmlspecialchars(($sol['jugador_nombre'] ?? '') . ' ' . ($sol['jugador_apellido'] ?? '')) ?></strong>
                            <small class="text-muted ms-2">@<?= htmlspecialchars($sol['usuario']) ?></small>
                        <?php endif; ?>
                        <small class="text-muted d-block">
                            <?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?>
                        </small>
                    </div>
                    <?php if ($sol['estado'] === 'aceptada'): ?>
                        <span class="badge bg-success">Aceptada</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Rechazada</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
