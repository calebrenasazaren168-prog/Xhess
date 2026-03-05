<div class="modal fade" id="mis-torneos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mis Torneos - Gestión Completa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php 
                $misTorneos = $sistema->getMisTorneos($_SESSION['user_id']);
                if (empty($misTorneos)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No has creado ningún torneo todavía.</p>
                    </div>
                <?php else: ?>
                    <!-- Selector de Torneo -->
                    <?php include 'includes/modals/torneos/selector_torneo.php'; ?>

                    <!-- Contenedor de Torneos -->
                    <?php foreach ($misTorneos as $index => $torneo): ?>
                        <div id="torneo-detalle-<?= $torneo['id_torneo'] ?>" class="torneo-detalle" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
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
                                            <?php $solPendCount = count($sistema->getSolicitudesTorneoPendientes($torneo['id_torneo'])); ?>
                                            <a class="nav-link position-relative" data-bs-toggle="tab" href="#solicitudes-torneo-<?= $torneo['id_torneo'] ?>">
                                                Solicitudes
                                                <?php if ($solPendCount > 0): ?>
                                                    <span class="badge bg-danger ms-1"><?= $solPendCount ?></span>
                                                <?php endif; ?>
                                            </a>
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
                                        <!-- Tab de Equipos -->
                                        <div class="tab-pane fade show active" id="equipos-<?= $torneo['id_torneo'] ?>">
                                            <?php include 'includes/modals/torneos/tab_equipos.php'; ?>
                                        </div>

                                        <!-- Tab de Solicitudes de Torneo -->
                                        <div class="tab-pane fade" id="solicitudes-torneo-<?= $torneo['id_torneo'] ?>">
                                            <?php
                                            $solPend = $sistema->getSolicitudesTorneoPendientes($torneo['id_torneo']);
                                            $solTodas = $sistema->getSolicitudesTorneoTodas($torneo['id_torneo']);
                                            $solHist = array_filter($solTodas, fn($s) => $s['estado'] !== 'pendiente');
                                            ?>
                                            <h6><i class="fas fa-envelope-open-text me-1"></i>Solicitudes de Equipos</h6>
                                            <p class="text-muted">
                                                <small>Rango de jugadores del torneo: 
                                                    <strong><?= $torneo['min_jugadores'] ?? '?' ?></strong> — 
                                                    <strong><?= $torneo['max_jugadores'] ?? '?' ?></strong>
                                                </small>
                                            </p>

                                            <?php if (empty($solPend)): ?>
                                                <p class="text-muted"><i class="fas fa-inbox me-1"></i>No hay solicitudes pendientes.</p>
                                            <?php else: ?>
                                                <div class="table-responsive mb-3">
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-warning">
                                                            <tr>
                                                                <th>Equipo</th><th>Club</th><th>Jugadores</th>
                                                                <th>Líder</th><th>Mensaje</th><th>Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($solPend as $sol): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($sol['equipo_nombre']) ?></strong></td>
                                                                    <td><?= htmlspecialchars($sol['club_nombre']) ?></td>
                                                                    <td><span class="badge bg-info"><?= $sol['num_jugadores'] ?></span></td>
                                                                    <td><?= htmlspecialchars($sol['lider_usuario']) ?></td>
                                                                    <td><small class="text-muted"><?= htmlspecialchars($sol['mensaje'] ?: '—') ?></small></td>
                                                                    <td>
                                                                        <?php if ($torneo['condicion'] !== 'finalizado'): ?>
                                                                        <form method="post" class="d-inline">
                                                                            <input type="hidden" name="action" value="aceptar_solicitud_torneo">
                                                                            <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                                            <button type="button" class="btn btn-xs btn-success"
                                                                                    onclick="confirmarAccion('¿Aceptar al equipo <?= htmlspecialchars($sol['equipo_nombre']) ?>?', this.closest(\'form\'))">
                                                                                <i class="fas fa-check"></i>
                                                                            </button>
                                                                        </form>
                                                                        <form method="post" class="d-inline ms-1">
                                                                            <input type="hidden" name="action" value="rechazar_solicitud_torneo">
                                                                            <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                                            <button type="button" class="btn btn-xs btn-danger"
                                                                                    onclick="confirmarAccion('¿Rechazar solicitud de <?= htmlspecialchars($sol['equipo_nombre']) ?>?', this.closest(\'form\'))">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                        </form>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($solHist)): ?>
                                                <details>
                                                    <summary class="text-muted" style="cursor:pointer;">
                                                        <i class="fas fa-history me-1"></i>Historial (<?= count($solHist) ?>)
                                                    </summary>
                                                    <div class="table-responsive mt-2">
                                                        <table class="table table-sm table-bordered align-middle">
                                                            <thead class="table-light">
                                                                <tr><th>Equipo</th><th>Jugadores</th><th>Estado</th><th>Respuesta</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($solHist as $h): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($h['equipo_nombre']) ?></td>
                                                                        <td><?= $h['num_jugadores'] ?></td>
                                                                        <td>
                                                                            <span class="badge bg-<?= $h['estado'] === 'aceptada' ? 'success' : 'danger' ?>">
                                                                                <?= ucfirst($h['estado']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><small><?= $h['fecha_respuesta'] ? date('d/m/Y', strtotime($h['fecha_respuesta'])) : '—' ?></small></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Tab de Clasificación -->
                                        <div class="tab-pane fade" id="clasificacion-<?= $torneo['id_torneo'] ?>">
                                            <?php include 'includes/modals/torneos/tab_clasificacion.php'; ?>
                                        </div>
                                        
                                        <!-- Tab de Rondas -->
                                        <div class="tab-pane fade" id="rondas-<?= $torneo['id_torneo'] ?>">
                                            <?php include 'includes/modals/torneos/tab_rondas.php'; ?>
                                        </div>
                                        
                                        <!-- Tab de Emparejamientos -->
                                        <div class="tab-pane fade" id="emparejamientos-<?= $torneo['id_torneo'] ?>">
                                            <?php include 'includes/modals/torneos/tab_emparejamientos.php'; ?>
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

<script>
function seleccionarTorneo(select) {
    const torneos = document.querySelectorAll('.torneo-detalle');
    torneos.forEach(t => t.style.display = 'none');
    
    const selectedId = select.value;
    if (selectedId) {
        const selectedDiv = document.getElementById(selectedId);
        if (selectedDiv) {
            selectedDiv.style.display = 'block';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('mis-torneos');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function () {
            const select = document.getElementById('selector-torneos');
            if (select && select.options.length > 0) {
                select.selectedIndex = 0;
                seleccionarTorneo(select);
            }
        });
    }
});
</script>