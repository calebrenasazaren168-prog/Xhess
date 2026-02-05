<?php 
$rondasTorneo = $sistema->getRondasTorneo($torneo['id_torneo']);
if (!empty($rondasTorneo)): ?>
<div class="card mb-4 border-success shadow-sm">
    <div class="card-header bg-success bg-gradient text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-chess-board me-2"></i>Navegación de Rondas
            </h6>
            <span class="badge bg-white text-success">
                <?= count($rondasTorneo) ?> Ronda<?= count($rondasTorneo) != 1 ? 's' : '' ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-10">
                <label class="form-label fw-bold mb-2">
                    <i class="fas fa-list-ol text-success"></i> Seleccionar Ronda:
                </label>
                <select class="form-select form-select-lg shadow-sm" 
                        id="selector-rondas-<?= $torneo['id_torneo'] ?>" 
                        onchange="seleccionarRonda(this, <?= $torneo['id_torneo'] ?>)"
                        style="border-left: 4px solid #28a745;">
                    <option value="">🔍 Ver todas las rondas</option>
                    <?php foreach ($rondasTorneo as $index => $ronda): ?>
                        <option value="ronda-detalle-<?= $ronda['id_ronda'] ?>" <?= $index === 0 ? 'selected' : '' ?>>
                            🎯 Ronda <?= $ronda['numero_ronda'] ?> 
                            • 📅 <?= date('d/m/Y', strtotime($ronda['fecha_ronda'])) ?>
                            • ♟️ <?= $ronda['partidas_jugadas'] ?> partida<?= $ronda['partidas_jugadas'] != 1 ? 's' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> 
                    Selecciona una ronda para ver sus partidas y resultados
                </small>
            </div>
            <div class="col-md-2 text-center">
                <div class="d-flex flex-column align-items-center">
                    <small class="text-muted mb-2">Estado:</small>
                    <?php 
                    $ultima_ronda = end($rondasTorneo);
                    $todas_jugadas = true;
                    foreach ($rondasTorneo as $r) {
                        $partidas_ronda = $sistema->getPartidasRonda($r['id_ronda']);
                        foreach ($partidas_ronda as $p) {
                            if ($p['resultado'] == '*') {
                                $todas_jugadas = false;
                                break 2;
                            }
                        }
                    }
                    ?>
                    <span class="badge <?= $todas_jugadas ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2">
                        <?= $todas_jugadas ? '✅ Completo' : '⏳ En curso' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-seleccionar la primera ronda al cargar
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('selector-rondas-<?= $torneo['id_torneo'] ?>');
    if (selector && selector.options.length > 1) {
        // Seleccionar la primera ronda (índice 1, ya que 0 es "Ver todas")
        selector.selectedIndex = 1;
        seleccionarRonda(selector, <?= $torneo['id_torneo'] ?>);
    }
});
</script>
<?php else: ?>
<div class="alert alert-info border-info shadow-sm">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
        <div>
            <h6 class="mb-1">No hay rondas generadas</h6>
            <small>Aún no se han creado rondas para este torneo. Ve a la pestaña "Rondas" para generar la primera ronda.</small>
        </div>
    </div>
</div>
<?php endif; ?>
