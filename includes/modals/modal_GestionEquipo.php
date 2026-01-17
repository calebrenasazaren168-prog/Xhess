<?php if ($sistema->isLoggedIn() && $sistema->isAdmin()): ?>
        <div class="modal fade" id="gestion-equipos" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestión de Equipos - Panel Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#crear-equipo">Crear Equipo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#asignar-jugadores">Asignar Jugadores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ver-equipos">Ver Equipos</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="crear-equipo">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    Solo los administradores pueden crear equipos. Los jugadores pueden unirse por sí mismos.
                                </div>
                                <form method="post" id="form-crear-equipo-admin">
                                    <input type="hidden" name="action" value="crear_equipo">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre del Equipo *</label>
                                            <input type="text" name="nombre" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Club *</label>
                                            <select name="id_club" class="form-control" required>
                                                <?php foreach ($sistema->getClubes() as $club): ?>
                                                    <option value="<?= $club['id_club'] ?>"><?= htmlspecialchars($club['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="confirmarEnvio('form-crear-equipo-admin', '¿Crear este equipo?')">
                                        Crear Equipo
                                    </button>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="asignar-jugadores">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>Función administrativa:</strong> Asignación forzada de jugadores a equipos.
                                </div>
                                
                                <form method="post" id="form-asignar-jugador">
                                    <input type="hidden" name="action" value="admin_asignar_jugador">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Jugador *</label>
                                            <select name="id_jugador" class="form-control" required>
                                                <option value="">Seleccionar jugador...</option>
                                                <?php foreach ($sistema->getJugadoresRegistrados() as $jugador): ?>
                                                    <option value="<?= $jugador['id_jugador'] ?>">
                                                        <?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                        (<?= htmlspecialchars($jugador['usuario']) ?>)
                                                        <?php if ($jugador['equipo_actual']): ?>
                                                            - Actual: <?= htmlspecialchars($jugador['equipo_actual']) ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Asignar a Equipo *</label>
                                            <select name="id_equipo" class="form-control" required>
                                                <option value="">Seleccionar equipo...</option>
                                                <?php foreach ($sistema->getEquiposDisponibles() as $equipo): ?>
                                                    <option value="<?= $equipo['id_equipo'] ?>">
                                                        <?= htmlspecialchars($equipo['nombre']) ?> 
                                                        (<?= htmlspecialchars($equipo['club_nombre']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-warning" 
                                            onclick="confirmarAccion('¿Asignar jugador a este equipo? Esta acción sobrescribirá cualquier equipo actual.', 'form-asignar-jugador')">
                                        <i class="fas fa-user-cog"></i> Asignar Forzadamente
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <h6>Quitar Jugador de Equipo (Admin)</h6>
                                <form method="post" id="form-quitar-jugador">
                                    <input type="hidden" name="action" value="admin_quitar_jugador">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Jugador con Equipo *</label>
                                            <select name="id_jugador" class="form-control" required>
                                                <option value="">Seleccionar jugador...</option>
                                                <?php foreach ($sistema->getJugadoresRegistrados() as $jugador): ?>
                                                    <?php if ($jugador['equipo_actual']): ?>
                                                        <option value="<?= $jugador['id_jugador'] ?>">
                                                            <?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                            - <?= htmlspecialchars($jugador['equipo_actual']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger w-100" 
                                                    onclick="confirmarAccion('¿Quitar a este jugador de su equipo actual?', 'form-quitar-jugador')">
                                                <i class="fas fa-user-times"></i> Quitar Forzadamente
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="ver-equipos">
                                <h6>Equipos por Club</h6>
                                <?php foreach ($sistema->getClubes() as $club): ?>
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?= htmlspecialchars($club['nombre']) ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $equiposClub = $sistema->getEquiposPorClub($club['id_club']);
                                            if (empty($equiposClub)): ?>
                                                <p class="text-muted mb-0">No hay equipos en este club.</p>
                                            <?php else: ?>
                                                <div class="row">
                                                    <?php foreach ($equiposClub as $equipo): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                                                                <div>
                                                                    <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?= $equipo['num_jugadores'] ?> jugador(es)
                                                                    </small>
                                                                </div>
                                                                <div>
                                                                    <button class="btn btn-sm btn-outline-danger" 
                                                                            onclick="eliminarEquipo(<?= $equipo['id_equipo'] ?>, '<?= htmlspecialchars($equipo['nombre']) ?>')"
                                                                            title="Eliminar equipo">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>