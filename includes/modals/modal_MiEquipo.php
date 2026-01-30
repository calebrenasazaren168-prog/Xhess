<?php if ($sistema->isLoggedIn() && $sistema->isJugador()): ?>
        <div class="modal fade" id="mi-equipo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mi Equipo - Auto Gestión</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $equipo_actual = $sistema->getEquipoActualJugador($_SESSION['jugador_id']);
                        ?>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Mi Equipo Actual</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($equipo_actual && !empty($equipo_actual['id_equipo'])): ?>  <!-- Cambiado: Verifica si hay id_equipo -->
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?= htmlspecialchars($equipo_actual['equipo_nombre']) ?></h5>  <!-- Cambiado: Usa 'equipo_nombre' en lugar de 'nombre' -->
                                            <p class="mb-1"><strong>Club:</strong> <?= htmlspecialchars($equipo_actual['club_nombre']) ?></p>
                                            <p class="mb-0 text-muted">Eres miembro de este equipo</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <form method="post" id="form-salir-equipo">
                                                <input type="hidden" name="action" value="salirse_equipo">
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmarAccion('¿Estás seguro de que quieres salirte de este equipo?', 'form-salir-equipo')">
                                                    <i class="fas fa-sign-out-alt"></i> Salir del Equipo
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <p class="text-muted mb-3">Actualmente no estás en ningún equipo.</p>
                                        <p>Puedes crear tu propio equipo o unirte a uno existente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#crear-equipo-jugador">Crear Mi Equipo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#unirse-equipo">Unirse a Equipo Existente</a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="crear-equipo-jugador">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Crear Mi Propio Equipo</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Crea tu propio equipo y conviértete automáticamente en su capitán. Podrás gestionar tu equipo y participar en torneos.
                                        </p>
                                        
                                        <form method="post" id="form-crear-equipo">
                                            <input type="hidden" name="action" value="crear_equipo">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nombre del Equipo *</label>
                                                    <input type="text" name="nombre" class="form-control" required 
                                                           placeholder="Ej: Los Ajedrecistas">
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
                                            <button type="button" class="btn btn-success" onclick="confirmarEnvio('form-crear-equipo', '¿Crear este equipo?')">
                                                <i class="fas fa-plus-circle"></i> Crear Mi Equipo
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="unirse-equipo">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Unirse a un Equipo Existente</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Selecciona un equipo de la lista para unirte. Puedes cambiarte de equipo en cualquier momento.
                                        </p>
                                        
                                        <div class="row">
                                            <?php 
                                            $equipos_disponibles = $sistema->getEquiposDisponiblesParaUnirse();
                                            foreach ($equipos_disponibles as $equipo): 
                                            ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card h-100 equipo-card">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?= htmlspecialchars($equipo['nombre']) ?></h6>
                                                            <p class="card-text mb-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-users"></i> 
                                                                    <?= $equipo['num_jugadores'] ?> jugador(es)
                                                                </small>
                                                            </p>
                                                            <p class="card-text">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-chess-board"></i> 
                                                                    <?= htmlspecialchars($equipo['club_nombre']) ?>
                                                                </small>
                                                            </p>
                                                            
                                                            <?php if ($equipo_actual && $equipo_actual['id_equipo'] == $equipo['id_equipo']): ?>
                                                                <button class="btn btn-success btn-sm w-100" disabled>
                                                                    <i class="fas fa-check"></i> Actual
                                                                </button>
                                                            <?php else: ?>
                                                                <form method="post" class="form-unirse-equipo">
                                                                    <input type="hidden" name="action" value="unirse_equipo">
                                                                    <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                                                                    <button type="button" class="btn btn-primary btn-sm w-100"
                                                                            onclick="confirmarAccion('¿Unirte al equipo <?= htmlspecialchars($equipo['nombre']) ?>?', this.closest('form'))">
                                                                        <i class="fas fa-user-plus"></i> Unirse
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <?php if (empty($equipos_disponibles)): ?>
                                            <div class="text-center">
                                                <p class="text-muted">No hay equipos disponibles en este momento.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>