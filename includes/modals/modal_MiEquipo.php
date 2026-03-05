<?php if ($sistema->isLoggedIn() && $sistema->isJugador()): ?>
        <div class="modal fade" id="mi-equipo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mi Equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $equipo_actual    = $sistema->getEquipoActualJugador($_SESSION['jugador_id']);
                        $mis_solicitudes  = $sistema->getSolicitudesJugador($_SESSION['jugador_id']);
                        $solicitud_pendiente_ids = array_column(
                            array_filter($mis_solicitudes, fn($s) => $s['estado'] === 'pendiente'),
                            'id_equipo'
                        );

                        // ¿Es capitán de su equipo actual?
                        $es_capitan = $equipo_actual 
                            && !empty($equipo_actual['id_equipo'])
                            && isset($_SESSION['user_id'])
                            && !empty($sistema->getEquiposCapitan($_SESSION['user_id']));

                        $miembros_equipo = [];
                        if ($es_capitan) {
                            $miembros_equipo = $sistema->getJugadoresPorEquipo($equipo_actual['id_equipo']);
                        }
                        ?>

                        <!-- Equipo actual -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Mi Equipo Actual</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($equipo_actual && !empty($equipo_actual['id_equipo'])): ?>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?= htmlspecialchars($equipo_actual['equipo_nombre']) ?></h5>
                                            <p class="mb-1"><strong>Club:</strong> <?= htmlspecialchars($equipo_actual['club_nombre']) ?></p>
                                            <p class="mb-0 text-muted">
                                                Eres miembro de este equipo
                                                <?php if ($es_capitan): ?>
                                                    · <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> Capitán</span>
                                                <?php endif; ?>
                                            </p>
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
                                        <p>Puedes crear tu propio equipo o solicitar unirte a uno existente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Mis solicitudes enviadas -->
                        <?php if (!empty($mis_solicitudes)): ?>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Mis Solicitudes</h6>
                                <?php
                                $pendientes_count = count(array_filter($mis_solicitudes, fn($s) => $s['estado'] === 'pendiente'));
                                if ($pendientes_count > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $pendientes_count ?> pendiente(s)</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($mis_solicitudes as $sol): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($sol['equipo_nombre']) ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($sol['club_nombre']) ?> · <?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($sol['estado'] === 'pendiente'): ?>
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="cancelar_solicitud">
                                                    <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="confirmarAccion('¿Cancelar esta solicitud?', this.closest('form'))">
                                                        <i class="fas fa-times"></i> Cancelar
                                                    </button>
                                                </form>
                                            <?php elseif ($sol['estado'] === 'aceptada'): ?>
                                                <span class="badge bg-success">Aceptada</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rechazada</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-crear-equipo">
                                    <i class="fas fa-plus-circle"></i> Crear Mi Equipo
                                </a>
                            </li>
                            <?php if (empty($equipo_actual['id_equipo'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-unirse-equipo">
                                    <i class="fas fa-paper-plane"></i> Solicitar Unirse
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if ($es_capitan): ?>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-miembros">
                                    <i class="fas fa-users"></i> Miembros
                                    <span class="badge bg-secondary ms-1"><?= count($miembros_equipo) ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- TAB: Crear equipo -->
                            <div class="tab-pane fade show active" id="tab-crear-equipo">
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

                            <!-- TAB: Solicitar unirse -->
                            <?php if (empty($equipo_actual['id_equipo'])): ?>
                            <div class="tab-pane fade" id="tab-unirse-equipo">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Solicitar Unirse a un Equipo</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info py-2">
                                            <i class="fas fa-info-circle"></i>
                                            Tu solicitud será revisada por el capitán del equipo antes de ser aceptada.
                                        </div>
                                        <div class="row">
                                            <?php
                                            $equipos_disponibles = $sistema->getEquiposDisponiblesParaUnirse();
                                            foreach ($equipos_disponibles as $equipo):
                                                $ya_solicitado = in_array($equipo['id_equipo'], $solicitud_pendiente_ids);
                                            ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card h-100 equipo-card">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?= htmlspecialchars($equipo['nombre']) ?></h6>
                                                            <p class="card-text mb-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-users"></i> <?= $equipo['num_jugadores'] ?> jugador(es)
                                                                </small>
                                                            </p>
                                                            <p class="card-text mb-3">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-chess-board"></i> <?= htmlspecialchars($equipo['club_nombre']) ?>
                                                                </small>
                                                            </p>
                                                            <?php if ($ya_solicitado): ?>
                                                                <button class="btn btn-warning btn-sm w-100" disabled>
                                                                    <i class="fas fa-clock"></i> Solicitud Enviada
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button"
                                                                        class="btn btn-primary btn-sm w-100"
                                                                        onclick="abrirModalSolicitud(<?= $equipo['id_equipo'] ?>, '<?= htmlspecialchars($equipo['nombre'], ENT_QUOTES) ?>')">
                                                                    <i class="fas fa-paper-plane"></i> Solicitar Ingreso
                                                                </button>
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
                            <?php endif; ?>

                            <!-- TAB: Miembros (solo capitán) -->
                            <?php if ($es_capitan): ?>
                            <div class="tab-pane fade" id="tab-miembros">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-users"></i> Miembros de <?= htmlspecialchars($equipo_actual['equipo_nombre']) ?>
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($miembros_equipo)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Jugador</th>
                                                            <th>Usuario</th>
                                                            <th class="text-center">Rating</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($miembros_equipo as $i => $m): ?>
                                                        <tr>
                                                            <td class="text-muted ps-3"><?= $i + 1 ?></td>
                                                            <td><strong><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></strong></td>
                                                            <td><small class="text-muted">@<?= htmlspecialchars($m['usuario']) ?></small></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-primary"><?= $m['rating'] ?></span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                                El equipo no tiene miembros aún.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div><!-- /tab-content -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini modal para enviar solicitud con mensaje opcional -->
        <div class="modal fade" id="modal-solicitud-ingreso" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Solicitar ingreso a <span id="nombre-equipo-solicitud"></span></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="form-solicitud-equipo">
                            <input type="hidden" name="action" value="solicitar_equipo">
                            <input type="hidden" name="id_equipo" id="id-equipo-solicitud">
                            <div class="mb-3">
                                <label class="form-label">Mensaje al capitán (opcional)</label>
                                <textarea name="mensaje" class="form-control" rows="3"
                                          placeholder="Preséntate o explica por qué quieres unirte..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> Enviar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function abrirModalSolicitud(idEquipo, nombreEquipo) {
            document.getElementById('id-equipo-solicitud').value = idEquipo;
            document.getElementById('nombre-equipo-solicitud').textContent = nombreEquipo;
            var modal = new bootstrap.Modal(document.getElementById('modal-solicitud-ingreso'));
            modal.show();
        }
        </script>
        <?php endif; ?>