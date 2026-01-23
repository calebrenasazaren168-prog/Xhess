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