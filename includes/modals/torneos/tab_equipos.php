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