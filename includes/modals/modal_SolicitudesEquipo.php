<?php 
// Modal de Solicitudes de Equipo (visible para jugadores que son capitanes/creadores de un equipo)
if (!$sistema->isLoggedIn() || !$sistema->isJugador()) return;

$equipos_capitan = $sistema->getEquiposCapitan($_SESSION['user_id']);
if (empty($equipos_capitan)) return;

$total_pendientes = array_sum(array_column($equipos_capitan, 'solicitudes_pendientes'));
?>

<div class="modal fade" id="modal-solicitudes-equipo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog"></i> Solicitudes de Mi Equipo
                    <?php if ($total_pendientes > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $total_pendientes ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <?php foreach ($equipos_capitan as $equipo): ?>
                    <?php $solicitudes = $sistema->getSolicitudesEquipo($equipo['id_equipo']); ?>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                <small class="text-muted ms-2"><?= htmlspecialchars($equipo['club_nombre']) ?></small>
                            </div>
                            <div>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-users"></i> <?= $equipo['num_jugadores'] ?> jugadores
                                </span>
                                <?php if ($equipo['solicitudes_pendientes'] > 0): ?>
                                    <span class="badge bg-warning text-dark ms-1">
                                        <?= $equipo['solicitudes_pendientes'] ?> pendiente(s)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            $pendientes = array_filter($solicitudes, fn($s) => $s['estado'] === 'pendiente');
                            $historial  = array_filter($solicitudes, fn($s) => $s['estado'] !== 'pendiente');
                            ?>

                            <!-- Solicitudes pendientes -->
                            <?php if (!empty($pendientes)): ?>
                                <div class="p-3 border-bottom">
                                    <h6 class="text-warning mb-3"><i class="fas fa-clock"></i> Pendientes de revisión</h6>
                                    <?php foreach ($pendientes as $sol): ?>
                                        <div class="d-flex justify-content-between align-items-start mb-3 p-3 bg-light rounded">
                                            <div>
                                                <strong><?= htmlspecialchars($sol['jugador_nombre'] . ' ' . $sol['jugador_apellido']) ?></strong>
                                                <small class="text-muted ms-2">@<?= htmlspecialchars($sol['usuario']) ?></small>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-star"></i> Rating: <?= $sol['rating'] ?> &nbsp;·&nbsp;
                                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) ?>
                                                    </small>
                                                </div>
                                                <?php if (!empty($sol['mensaje'])): ?>
                                                    <div class="mt-2">
                                                        <small class="fst-italic text-secondary">
                                                            "<?= htmlspecialchars($sol['mensaje']) ?>"
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-2 ms-3 flex-shrink-0">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="aceptar_solicitud">
                                                    <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                    <button type="button" class="btn btn-success btn-sm"
                                                            onclick="confirmarAccion('¿Aceptar a <?= htmlspecialchars($sol['jugador_nombre'], ENT_QUOTES) ?> en el equipo?', this.closest('form'))">
                                                        <i class="fas fa-check"></i> Aceptar
                                                    </button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="rechazar_solicitud">
                                                    <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="confirmarAccion('¿Rechazar la solicitud de <?= htmlspecialchars($sol['jugador_nombre'], ENT_QUOTES) ?>?', this.closest('form'))">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-3 text-center text-muted border-bottom">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay solicitudes pendientes
                                </div>
                            <?php endif; ?>

                            <!-- Historial -->
                            <?php if (!empty($historial)): ?>
                                <div class="p-3">
                                    <h6 class="text-muted mb-3"><i class="fas fa-history"></i> Historial reciente</h6>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice(array_values($historial), 0, 5) as $sol): ?>
                                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small>
                                                        <strong><?= htmlspecialchars($sol['jugador_nombre'] . ' ' . $sol['jugador_apellido']) ?></strong>
                                                        <span class="text-muted ms-1">· <?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></span>
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

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
