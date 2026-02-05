<div class="card mb-4 border-primary">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-9">
                <label class="form-label fw-bold mb-2">
                    <i class="fas fa-trophy text-primary"></i> Seleccionar Torneo:
                </label>
                <select class="form-select form-select-lg" id="selector-torneos" onchange="seleccionarTorneo(this)">
                    <?php foreach ($misTorneos as $index => $torneo): ?>
                        <option value="torneo-detalle-<?= $torneo['id_torneo'] ?>" <?= $index === 0 ? 'selected' : '' ?>>
                            <?= htmlspecialchars($torneo['nombre']) ?> 
                            - (<?= $torneo['condicion'] == 'en_progreso' ? 'En Progreso' : ($torneo['condicion'] == 'finalizado' ? 'Finalizado' : 'Pendiente') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 text-center">
                <small class="text-muted d-block mb-1">Total de torneos:</small>
                <span class="badge bg-primary fs-5 px-3 py-2"><?= count($misTorneos) ?></span>
            </div>
        </div>
    </div>
</div>